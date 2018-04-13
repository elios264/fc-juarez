<?php
//var_dump($_SERVER['REQUEST_METHOD'],$_SERVER['PATH_INFO']); die();

interface DatabaseInterface {
	public function getSql($name);
	public function connect($hostname,$username,$password,$database,$port,$socket,$charset);
	public function query($sql,$params=array());
	public function fetchAssoc($result);
	public function fetchRow($result);
	public function insertId($result);
	public function affectedRows($result);
	public function close($result);
	public function fetchFields($table);
	public function addLimitToSql($sql,$limit,$offset);
	public function likeEscape($string);
	public function isNumericType($field);
	public function isBinaryType($field);
	public function isGeometryType($field);
	public function isJsonType($field);
	public function getDefaultCharset();
	public function beginTransaction();
	public function commitTransaction();
	public function rollbackTransaction();
	public function jsonEncode($object);
	public function jsonDecode($string);
}

class MySQL implements DatabaseInterface {

	protected $db;
	protected $queries;

	public function __construct() {
		$this->queries = array(
			'reflect_table'=>'SELECT
					"TABLE_NAME"
				FROM
					"INFORMATION_SCHEMA"."TABLES"
				WHERE
					"TABLE_NAME" COLLATE \'utf8_bin\' = ? AND
					"TABLE_SCHEMA" = ?',
			'reflect_pk'=>'SELECT
					"COLUMN_NAME"
				FROM
					"INFORMATION_SCHEMA"."COLUMNS"
				WHERE
					"COLUMN_KEY" = \'PRI\' AND
					"TABLE_NAME" = ? AND
					"TABLE_SCHEMA" = ?'
		);
  }

  function __destruct() {
    $this->db->close();
  }

	public function getSql($name) {
		return isset($this->queries[$name])?$this->queries[$name]:false;
	}

	public function connect($hostname,$username,$password,$database,$port,$socket,$charset) {
		$db = mysqli_init();
		if (defined('MYSQLI_OPT_INT_AND_FLOAT_NATIVE')) {
			mysqli_options($db,MYSQLI_OPT_INT_AND_FLOAT_NATIVE,true);
		}
		$success = mysqli_real_connect($db,$hostname,$username,$password,$database,$port,$socket,MYSQLI_CLIENT_FOUND_ROWS);
		if (!$success) {
			throw new \Exception('Connect failed. '.mysqli_connect_error());
		}
		if (!mysqli_set_charset($db,$charset)) {
			throw new \Exception('Error setting charset. '.mysqli_error($db));
		}
		if (!mysqli_query($db,'SET SESSION sql_mode = \'ANSI_QUOTES\';')) {
			throw new \Exception('Error setting ANSI quotes. '.mysqli_error($db));
		}
		$this->db = $db;
	}

	public function query($sql,$params=array()) {
		$db = $this->db;
		$sql = preg_replace_callback('/\!|\?/', function ($matches) use (&$db,&$params) {
			$param = array_shift($params);
			if ($matches[0]=='!') {
				$key = preg_replace('/[^a-zA-Z0-9\-_=<> ]/','',is_object($param)?$param->key:$param);
				if (is_object($param) && $param->type=='hex') {
					return "HEX(\"$key\") as \"$key\"";
				}
				if (is_object($param) && $param->type=='wkt') {
					return "ST_AsText(\"$key\") as \"$key\"";
				}
				return '"'.$key.'"';
			} else {
				if (is_array($param)) return '('.implode(',',array_map(function($v) use (&$db) {
					return "'".mysqli_real_escape_string($db,$v)."'";
				},$param)).')';
				if (is_object($param) && $param->type=='hex') {
					return "x'".$param->value."'";
				}
				if (is_object($param) && $param->type=='wkt') {
					return "ST_GeomFromText('".mysqli_real_escape_string($db,$param->value)."')";
				}
				if ($param===null) return 'NULL';
				return "'".mysqli_real_escape_string($db,$param)."'";
			}
		}, $sql);
		//if (!strpos($sql,'INFORMATION_SCHEMA')) echo "\n$sql\n";
		//if (!strpos($sql,'INFORMATION_SCHEMA')) file_put_contents('log.txt',"\n$sql\n",FILE_APPEND);
		return mysqli_query($db,$sql);
	}

	public function fetchAssoc($result) {
		return mysqli_fetch_assoc($result);
	}

	public function fetchRow($result) {
		return mysqli_fetch_row($result);
	}

	public function insertId($result) {
		return mysqli_insert_id($this->db);
	}

	public function affectedRows($result) {
		return mysqli_affected_rows($this->db);
	}

	public function close($result) {
		return mysqli_free_result($result);
	}

	public function fetchFields($table) {
		$result = $this->query('SELECT * FROM ! WHERE 1=2;',array($table));
		return mysqli_fetch_fields($result);
	}

	public function addLimitToSql($sql,$limit,$offset) {
		return "$sql LIMIT $limit OFFSET $offset";
	}

	public function likeEscape($string) {
		return addcslashes($string,'%_');
	}

	public function convertFilter($field, $comparator, $value) {
		return false;
	}

	public function isNumericType($field) {
		return in_array($field->type,array(1,2,3,4,5,6,8,9));
	}

	public function isBinaryType($field) {
		//echo "$field->name: $field->type ($field->flags)\n";
		return (($field->flags & 128) && (($field->type>=249 && $field->type<=252) || ($field->type>=253 && $field->type<=254 && $field->charsetnr==63)));
	}

	public function isGeometryType($field) {
		return ($field->type==255);
	}

	public function isJsonType($field) {
		return ($field->type==245);
	}

	public function getDefaultCharset() {
		return 'utf8';
	}

	public function beginTransaction() {
		mysqli_query($this->db,'BEGIN');
		//return mysqli_begin_transaction($this->db);
	}

	public function commitTransaction() {
		mysqli_query($this->db,'COMMIT');
		//return mysqli_commit($this->db);
	}

	public function rollbackTransaction() {
		mysqli_query($this->db,'ROLLBACK');
		//return mysqli_rollback($this->db);
	}

	public function jsonEncode($object) {
		return json_encode($object);
	}

	public function jsonDecode($string) {
		return json_decode($string);
	}
}

class PHP_CRUD_API {

	protected $db;
	protected $settings;

	protected function mapMethodToAction($method,$key) {
		switch ($method) {
			case 'OPTIONS': return 'headers';
			case 'GET': return ($key===false)?'list':'read';
			default: $this->exitWith404('method');
		}
		return false;
	}

	protected function parseRequestParameter(&$request,$characters) {
		if ($request==='') return false;
		$pos = strpos($request,'/');
		$value = $pos?substr($request,0,$pos):$request;
		$request = $pos?substr($request,$pos+1):'';
		if (!$characters) return $value;
		return preg_replace("/[^$characters]/",'',$value);
	}

	protected function parseGetParameter($get,$name,$characters) {
		$value = isset($get[$name])?$get[$name]:false;
		return $characters?preg_replace("/[^$characters]/",'',$value):$value;
	}

	protected function parseGetParameterArray($get,$name,$characters) {
		$values = isset($get[$name])?$get[$name]:false;
		if (!is_array($values)) $values = array($values);
		if ($characters) {
			foreach ($values as &$value) {
				$value = preg_replace("/[^$characters]/",'',$value);
			}
		}
		return $values;
	}


	protected function applyTableAuthorizer($callback,$action,$database,&$tables) {
		if (is_callable($callback,true)) foreach ($tables as $i=>$table) {
			if (!$callback($action,$database,$table)) {
				unset($tables[$i]);
			}
		}
	}
	protected function processTableAndIncludeParameters($database,$table,$action) {
		$blacklist = array('information_schema','mysql','sys','pg_catalog');
		if (in_array(strtolower($database), $blacklist)) return array();
		$table_list = array();
		if ($result = $this->db->query($this->db->getSql('reflect_table'),array($table,$database))) {
			while ($row = $this->db->fetchRow($result)) $table_list[] = $row[0];
			$this->db->close($result);
		}
		if (empty($table_list)) $this->exitWith404('entity');
		return $table_list;
	}

	protected function exitWith404($type) {
		if (isset($_SERVER['REQUEST_METHOD'])) {
			header('Content-Type:',true,404);
			die("Not found ($type)");
		} else {
			throw new \Exception("Not found ($type)");
		}
	}


	protected function headersCommand($parameters) {
		$headers = array();
		$headers[]='Access-Control-Allow-Headers: Content-Type, X-XSRF-TOKEN';
		$headers[]='Access-Control-Allow-Methods: OPTIONS, GET';
		$headers[]='Access-Control-Allow-Credentials: true';
		$headers[]='Access-Control-Max-Age: 1728000';
		if (isset($_SERVER['REQUEST_METHOD'])) {
			foreach ($headers as $header) header($header);
		} else {
			echo json_encode($headers);
		}
		return false;
	}

	protected function startOutput() {
		if (isset($_SERVER['REQUEST_METHOD'])) {
			header('Content-Type: application/json; charset=utf-8');
		}
	}

	protected function findPrimaryKeys($table,$database) {
		$fields = array();
		if ($result = $this->db->query($this->db->getSql('reflect_pk'),array($table,$database))) {
			while ($row = $this->db->fetchRow($result)) {
				$fields[] = $row[0];
			}
			$this->db->close($result);
		}
		return $fields;
	}

	protected function processKeyParameter($key,$tables,$database) {
		if ($key===false) return false;
		$fields = $this->findPrimaryKeys($tables[0],$database);
		if (count($fields)!=1) $this->exitWith404('1pk');
		return array(explode(',',$key),$fields[0]);
	}

	protected function processOrderingsParameter($orderings) {
		if (!$orderings) return false;
		foreach ($orderings as &$order) {
			$order = explode(',',$order,2);
			if (count($order)<2) $order[1]='ASC';
			if (!strlen($order[0])) return false;
			$direction = strtoupper($order[1]);
			if (in_array($direction,array('ASC','DESC'))) {
				$order[1] = $direction;
			}
		}
		return $orderings;
	}

	protected function convertFilter($field, $comparator, $value) {
		$result = $this->db->convertFilter($field,$comparator,$value);
		if ($result) return $result;
		// default behavior
		$comparator = strtolower($comparator);
		if ($comparator[0]!='n') {
			if (strlen($comparator)==2) {
				switch ($comparator) {
					case 'cs': return array('! LIKE ?',$field,'%'.$this->db->likeEscape($value).'%');
					case 'sw': return array('! LIKE ?',$field,$this->db->likeEscape($value).'%');
					case 'ew': return array('! LIKE ?',$field,'%'.$this->db->likeEscape($value));
					case 'eq': return array('! = ?',$field,$value);
					case 'lt': return array('! < ?',$field,$value);
					case 'le': return array('! <= ?',$field,$value);
					case 'ge': return array('! >= ?',$field,$value);
					case 'gt': return array('! > ?',$field,$value);
					case 'bt':
						$v = explode(',',$value);
						if (count($v)<2) return false;
						return array('! BETWEEN ? AND ?',$field,$v[0],$v[1]);
					case 'in': return array('! IN ?',$field,explode(',',$value));
					case 'is': return array('! IS NULL',$field);
				}
			} else {
				switch ($comparator) {
					case 'sco': return array('ST_Contains(!,ST_GeomFromText(?))=TRUE',$field,$value);
					case 'scr': return array('ST_Crosses(!,ST_GeomFromText(?))=TRUE',$field,$value);
					case 'sdi': return array('ST_Disjoint(!,ST_GeomFromText(?))=TRUE',$field,$value);
					case 'seq': return array('ST_Equals(!,ST_GeomFromText(?))=TRUE',$field,$value);
					case 'sin': return array('ST_Intersects(!,ST_GeomFromText(?))=TRUE',$field,$value);
					case 'sov': return array('ST_Overlaps(!,ST_GeomFromText(?))=TRUE',$field,$value);
					case 'sto': return array('ST_Touches(!,ST_GeomFromText(?))=TRUE',$field,$value);
					case 'swi': return array('ST_Within(!,ST_GeomFromText(?))=TRUE',$field,$value);
					case 'sic': return array('ST_IsClosed(!)=TRUE',$field);
					case 'sis': return array('ST_IsSimple(!)=TRUE',$field);
					case 'siv': return array('ST_IsValid(!)=TRUE',$field);
				}
			}
		} else {
			if (strlen($comparator)==2) {
				switch ($comparator) {
					case 'ne': return $this->convertFilter($field, 'neq', $value); // deprecated
					case 'ni': return $this->convertFilter($field, 'nin', $value); // deprecated
					case 'no': return $this->convertFilter($field, 'nis', $value); // deprecated
				}
			} elseif (strlen($comparator)==3) {
				switch ($comparator) {
					case 'ncs': return array('! NOT LIKE ?',$field,'%'.$this->db->likeEscape($value).'%');
					case 'nsw': return array('! NOT LIKE ?',$field,$this->db->likeEscape($value).'%');
					case 'new': return array('! NOT LIKE ?',$field,'%'.$this->db->likeEscape($value));
					case 'neq': return array('! <> ?',$field,$value);
					case 'nlt': return array('! >= ?',$field,$value);
					case 'nle': return array('! > ?',$field,$value);
					case 'nge': return array('! < ?',$field,$value);
					case 'ngt': return array('! <= ?',$field,$value);
					case 'nbt':
						$v = explode(',',$value);
						if (count($v)<2) return false;
						return array('! NOT BETWEEN ? AND ?',$field,$v[0],$v[1]);
					case 'nin': return array('! NOT IN ?',$field,explode(',',$value));
					case 'nis': return array('! IS NOT NULL',$field);
				}
			} else {
				switch ($comparator) {
					case 'nsco': return array('ST_Contains(!,ST_GeomFromText(?))=FALSE',$field,$value);
					case 'nscr': return array('ST_Crosses(!,ST_GeomFromText(?))=FALSE',$field,$value);
					case 'nsdi': return array('ST_Disjoint(!,ST_GeomFromText(?))=FALSE',$field,$value);
					case 'nseq': return array('ST_Equals(!,ST_GeomFromText(?))=FALSE',$field,$value);
					case 'nsin': return array('ST_Intersects(!,ST_GeomFromText(?))=FALSE',$field,$value);
					case 'nsov': return array('ST_Overlaps(!,ST_GeomFromText(?))=FALSE',$field,$value);
					case 'nsto': return array('ST_Touches(!,ST_GeomFromText(?))=FALSE',$field,$value);
					case 'nswi': return array('ST_Within(!,ST_GeomFromText(?))=FALSE',$field,$value);
					case 'nsic': return array('ST_IsClosed(!)=FALSE',$field);
					case 'nsis': return array('ST_IsSimple(!)=FALSE',$field);
					case 'nsiv': return array('ST_IsValid(!)=FALSE',$field);
				}
			}
		}
		return false;
	}

	public function addFilter(&$filters,$table,$and,$field,$comparator,$value) {
		if (!isset($filters[$table])) $filters[$table] = array();
		if (!isset($filters[$table][$and])) $filters[$table][$and] = array();
		$filter = $this->convertFilter($field,$comparator,$value);
		if ($filter) $filters[$table][$and][] = $filter;
	}

	public function addFilters(&$filters,$table,$satisfy,$filterStrings) {
		if ($filterStrings) {
			for ($i=0;$i<count($filterStrings);$i++) {
				$parts = explode(',',$filterStrings[$i],3);
				if (count($parts)>=2) {
          list($t,$f) = array($table,$parts[0]);
					$comparator = $parts[1];
					$value = isset($parts[2])?$parts[2]:null;
					$and = isset($satisfy[$t])?$satisfy[$t]:'and';
					$this->addFilter($filters,$t,$and,$f,$comparator,$value);
				}
			}
		}
	}

	protected function processSatisfyParameter($tables,$satisfyString) {
		$satisfy = array();
		foreach (explode(',',$satisfyString) as $str) {
			list($t,$s) = array($tables[0],$str);
			$and = ($s && strtolower($s)=='any')?'or':'and';
			$satisfy[$t] = $and;
		}
		return $satisfy;
	}

	protected function processFiltersParameter($tables,$satisfy,$filterStrings) {
		$filters = array();
		$this->addFilters($filters,$tables[0],$satisfy,$filterStrings);
		return $filters;
	}

	protected function processPageParameter($page) {
		if (!$page) return false;
		$page = explode(',',$page,2);
		if (count($page)<2) $page[1]=20;
		$page[0] = ($page[0]-1)*$page[1];
		return $page;
	}

	protected function retrieveObject($key,$fields,$filters,$tables) {
		if (!$key) return false;
		$table = $tables[0];
		$params = array();
		$sql = 'SELECT ';
		$this->convertOutputs($sql,$params,$fields[$table]);
		$sql .= ' FROM !';
		$params[] = $table;
		$this->addFilter($filters,$table,'and',$key[1],'eq',$key[0][0]);
		$this->addWhereFromFilters($filters[$table],$sql,$params);
		$object = null;
		if ($result = $this->db->query($sql,$params)) {
			$object = $this->fetchAssoc($result,$fields[$table]);
			$this->db->close($result);
		}
		return $object;
	}

	protected function retrieveObjects($key,$fields,$filters,$tables) {
		$keyField = $key[1];
		$keys = $key[0];
		$rows = array();
		foreach ($keys as $key) {
			$result = $this->retrieveObject(array(array($key),$keyField),$fields,$filters,$tables);
			if ($result===null) {
				return null;
			}
			$rows[] = $result;
		}
		return $rows;
	}

	protected function findFields($tables,$columns,$exclude,$select,$database) {
		$fields = array();
    $keep = false;

		foreach ($tables as $i=>$table) {
			$fields[$table] = $this->findTableFields($table,$database);
			$fields[$table] = $this->filterFieldsByColumns($fields[$table],$columns,$keep,$i==0,$table);
			$fields[$table] = $this->filterFieldsByExclude($fields[$table],$exclude,$keep,$i==0,$table);
		}
		return $fields;
	}

	protected function filterFieldsByColumns($fields,$columns,$keep,$first,$table) {
		if ($columns) {
			$columns = explode(',',$columns);
			foreach (array_keys($fields) as $key) {
				$delete = true;
				foreach ($columns as $column) {
					if ($first) {
						if ($column==$key || $column=="*") {
							$delete = false;
						}
					}
				}
				if ($delete && !isset($keep[$table][$key])) {
					unset($fields[$key]);
				}
			}
		}
		return $fields;
	}

	protected function filterFieldsByExclude($fields,$exclude,$keep,$first,$table) {
		if ($exclude) {
			$columns = explode(',',$exclude);
			foreach (array_keys($fields) as $key) {
				$delete = false;
				foreach ($columns as $column) {
					if ($first) {
						if ($column==$key || $column=="*") {
							$delete = true;
						}
					}
				}
				if ($delete && !isset($keep[$table][$key])) {
					unset($fields[$key]);
				}
			}
		}
		return $fields;
	}

	protected function findTableFields($table,$database) {
		$fields = array();
		foreach ($this->db->fetchFields($table) as $field) {
			$fields[$field->name] = $field;
		}
		return $fields;
	}

	protected function filterInputByFields($input,$fields) {
		if ($fields) foreach (array_keys((array)$input) as $key) {
			if (!isset($fields[$key])) {
				unset($input->$key);
			}
		}
		return $input;
	}

	protected function convertInputs(&$input,$fields) {
		foreach ($fields as $key=>$field) {
			if (isset($input->$key) && $input->$key && $this->db->isBinaryType($field)) {
				$value = $input->$key;
				$value = str_pad(strtr($value, '-_', '+/'), ceil(strlen($value) / 4) * 4, '=', STR_PAD_RIGHT);
				$input->$key = (object)array('type'=>'hex','value'=>bin2hex(base64_decode($value)));
			}
			if (isset($input->$key) && $input->$key && $this->db->isGeometryType($field)) {
				$input->$key = (object)array('type'=>'wkt','value'=>$input->$key);
			}
			if (isset($input->$key) && $input->$key && $this->db->isJsonType($field)) {
				$input->$key = $this->db->jsonEncode($input->$key);
			}
		}
	}

	protected function convertOutputs(&$sql, &$params, $fields) {
		$sql .= implode(',',str_split(str_repeat('!',count($fields))));
		foreach ($fields as $key=>$field) {
			if ($this->db->isBinaryType($field)) {
				$params[] = (object)array('type'=>'hex','key'=>$key);
			}
			else if ($this->db->isGeometryType($field)) {
				$params[] = (object)array('type'=>'wkt','key'=>$key);
			}
			else {
				$params[] = $key;
			}
		}
	}

	protected function convertTypes($result,&$values,&$fields) {
		foreach ($values as $i=>$v) {
			if (is_string($v)) {
				if ($this->db->isNumericType($fields[$i])) {
					$values[$i] = $v + 0;
				}
				else if ($this->db->isBinaryType($fields[$i])) {
					$values[$i] = base64_encode(pack("H*",$v));
				}
				else if ($this->db->isJsonType($fields[$i])) {
					$values[$i] = $this->db->jsonDecode($v);
				}
			}
		}
	}

	protected function fetchAssoc($result,$fields=false) {
		$values = $this->db->fetchAssoc($result);
		if ($values && $fields) {
			$this->convertTypes($result,$values,$fields);
		}
		return $values;
	}

	protected function fetchRow($result,$fields=false) {
		$values = $this->db->fetchRow($result,$fields);
		if ($values && $fields) {
			$fields = array_values($fields);
			$this->convertTypes($result,$values,$fields);
		}
		return $values;
	}

	protected function getParameters($settings) {
		extract($settings);

		$table     = $this->parseRequestParameter($request, 'a-zA-Z0-9\-_');
		$key       = $this->parseRequestParameter($request, 'a-zA-Z0-9\-_,'); // auto-increment or uuid
		$action    = $this->mapMethodToAction($method,$key);
		$page      = $this->parseGetParameter($get, 'page', '0-9,');
		$filters   = $this->parseGetParameterArray($get, 'filter', false);
		$satisfy   = $this->parseGetParameter($get, 'satisfy', 'a-zA-Z0-9\-_,.');
		$columns   = $this->parseGetParameter($get, 'columns', 'a-zA-Z0-9\-_,.*');
		$exclude   = $this->parseGetParameter($get, 'exclude', 'a-zA-Z0-9\-_,.*');
		$orderings = $this->parseGetParameterArray($get, 'order', 'a-zA-Z0-9\-_,');

		$tables    = $this->processTableAndIncludeParameters($database,$table,$action);
		$key       = $this->processKeyParameter($key,$tables,$database);
		$satisfy   = $this->processSatisfyParameter($tables,$satisfy);
		$filters   = $this->processFiltersParameter($tables,$satisfy,$filters);
		$page      = $this->processPageParameter($page);
		$orderings = $this->processOrderingsParameter($orderings);

    // reflection
    $collect = array();
    $select = array();
		$fields = $this->findFields($tables,$columns,$exclude,$select,$database);

		// permissions
		if ($table_authorizer) $this->applyTableAuthorizer($table_authorizer,$action,$database,$tables);
		if (!isset($tables[0])) $this->exitWith404('entity');

		// input
		$inputs = array(false);
		foreach ($inputs as $k=>$context) {
			$input = $this->filterInputByFields($context,$fields[$tables[0]]);
			$this->convertInputs($input,$fields[$tables[0]]);
			$inputs[$k] = $input;
    }

		return compact('action','database','tables','key','page','filters','fields','orderings','inputs','collect','select');
	}

	protected function addWhereFromFilters($filters,&$sql,&$params) {
		$first = true;
		if (isset($filters['or'])) {
			$first = false;
			$sql .= ' WHERE (';
			foreach ($filters['or'] as $i=>$filter) {
				$sql .= $i==0?'':' OR ';
				$sql .= $filter[0];
				for ($i=1;$i<count($filter);$i++) {
					$params[] = $filter[$i];
				}
			}
			$sql .= ')';
		}
		if (isset($filters['and'])) {
			foreach ($filters['and'] as $i=>$filter) {
				$sql .= $first?' WHERE ':' AND ';
				$sql .= $filter[0];
				for ($i=1;$i<count($filter);$i++) {
					$params[] = $filter[$i];
				}
				$first = false;
			}
		}
	}

	protected function addOrderByFromOrderings($orderings,&$sql,&$params) {
		foreach ($orderings as $i=>$ordering) {
			$sql .= $i==0?' ORDER BY ':', ';
			$sql .= '! '.$ordering[1];
			$params[] = $ordering[0];
		}
	}

	protected function listCommandInternal($parameters) {
		extract($parameters);
		echo '{';
		$table = array_shift($tables);
		// first table
		$count = false;
		echo '"'.$table.'":{';
		if (is_array($orderings) && is_array($page)) {
			$params = array();
			$sql = 'SELECT COUNT(*) FROM !';
			$params[] = $table;
			if (isset($filters[$table])) {
					$this->addWhereFromFilters($filters[$table],$sql,$params);
			}
			if ($result = $this->db->query($sql,$params)) {
				while ($pages = $this->db->fetchRow($result)) {
					$count = (int)$pages[0];
				}
			}
		}
		$params = array();
		$sql = 'SELECT ';
		$this->convertOutputs($sql,$params,$fields[$table]);
		$sql .= ' FROM !';
		$params[] = $table;
		if (isset($filters[$table])) {
			$this->addWhereFromFilters($filters[$table],$sql,$params);
		}
		if (is_array($orderings)) {
			$this->addOrderByFromOrderings($orderings,$sql,$params);
		}
		if (is_array($orderings) && is_array($page)) {
			$sql = $this->db->addLimitToSql($sql,$page[1],$page[0]);
		}
		if ($result = $this->db->query($sql,$params)) {
			echo '"columns":';
			$keys = array_keys($fields[$table]);
			echo json_encode($keys);
			$keys = array_flip($keys);
			echo ',"records":[';
			$first_row = true;
			while ($row = $this->fetchRow($result,$fields[$table])) {
				if ($first_row) $first_row = false;
				else echo ',';
				if (isset($collect[$table])) {
					foreach (array_keys($collect[$table]) as $field) {
						$collect[$table][$field][] = $row[$keys[$field]];
					}
				}
				echo json_encode($row);
			}
			$this->db->close($result);
			echo ']';
			if ($count) echo ',';
		}
		if ($count) echo '"results":'.$count;
		echo '}';
		echo '}';
	}

	protected function readCommand($parameters) {
		extract($parameters);
		if (count($key[0])>1) $object = $this->retrieveObjects($key,$fields,$filters,$tables);
		else $object = $this->retrieveObject($key,$fields,$filters,$tables);
		if (!$object) $this->exitWith404('object');
		$this->startOutput();
		echo json_encode($object);
		return false;
	}

	protected function listCommand($parameters) {
		extract($parameters);
		$this->startOutput();
		$this->listCommandInternal($parameters);
		return false;
	}


	public function __construct($config) {
		extract($config);

		// initialize
		$dbengine = isset($dbengine)?$dbengine:null;
		$hostname = isset($hostname)?$hostname:null;
		$username = isset($username)?$username:null;
		$password = isset($password)?$password:null;
		$database = isset($database)?$database:null;
		$port = isset($port)?$port:null;
		$socket = isset($socket)?$socket:null;
		$charset = isset($charset)?$charset:null;

		$table_authorizer = isset($table_authorizer)?$table_authorizer:null;
		$allow_origin = isset($allow_origin)?$allow_origin:null;


		$db = isset($db)?$db:null;
		$method = isset($method)?$method:null;
		$request = isset($request)?$request:null;
		$get = isset($get)?$get:null;
		$origin = isset($origin)?$origin:null;

		// defaults
		if (!$dbengine) {
			$dbengine = 'MySQL';
		}
		if (!$method) {
			$method = $_SERVER['REQUEST_METHOD'];
		}
		if (!$request) {
			$request = isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'';
			if (!$request) {
				$request = isset($_SERVER['ORIG_PATH_INFO'])?$_SERVER['ORIG_PATH_INFO']:'';
				$request = $request!=$_SERVER['SCRIPT_NAME']?$request:'';
			}
		}
		if (!$get) {
			$get = $_GET;
		}

		if (!$origin) {
			$origin = isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:'';
		}

		// connect
		$request = trim($request,'/');
		if (!$database) {
			$database = $this->parseRequestParameter($request, 'a-zA-Z0-9\-_');
		}
		if (!$db) {
			$db = new $dbengine();
			if (!$charset) {
				$charset = $db->getDefaultCharset();
			}
			$db->connect($hostname,$username,$password,$database,$port,$socket,$charset);
		}

		if ($allow_origin===null) {
			$allow_origin = '*';
		}

		$this->db = $db;
		$this->settings = compact('method', 'request', 'get', 'post', 'origin', 'database', 'table_authorizer', 'allow_origin');
	}


	protected function allowOrigin($origin,$allowOrigins) {
		if (isset($_SERVER['REQUEST_METHOD'])) {
			header('Access-Control-Allow-Credentials: true');
			foreach (explode(',',$allowOrigins) as $o) {
				if (preg_match('/^'.str_replace('\*','.*',preg_quote(strtolower(trim($o)))).'$/',$origin)) {
					header('Access-Control-Allow-Origin: '.$origin);
					break;
				}
			}
		}
	}

	public function executeCommand() {
		if ($this->settings['origin']) {
			$this->allowOrigin($this->settings['origin'],$this->settings['allow_origin']);
		}
		if (!$this->settings['request']) {
      $this->startOutput();
      echo json_encode(array( "message" => "welcome to the coolest api on earth" ));
		} else {
			$parameters = $this->getParameters($this->settings);
			switch($parameters['action']){
				case 'list': $output = $this->listCommand($parameters); break;
        case 'read': $output = $this->readCommand($parameters); break;
        case 'headers': $output = $this->headersCommand($parameters); break;
				default: $output = false;
			}
			if ($output!==false) {
				$this->startOutput();
				echo json_encode($output);
			}
		}
	}
}

$api = new PHP_CRUD_API(array(
 	'dbengine'=>'MySQL',
	'hostname'=>'192.185.24.63',
	'username'=>'fcjuarez',
	'password'=>'M3rc@tik@',
	'database'=>'fcjuarez_emc2',
  'charset'=>'utf8',
  'table_authorizer'=>function($cmd,$db,$tab) {
    return in_array($tab, array('Season', 'Tournament', 'GameFuture', 'GamePresent', 'GamePast', 'GamePresentMinute', 'Banner', 'Advertisement'));
  },
));
$api->executeCommand();
