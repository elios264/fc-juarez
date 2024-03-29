<?php

    include('../../lib/idbmanager.php');


// <editor-fold defaultstate="collapsed" desc=" Mobile APP Notification Functions ">

    function sendNotification($data){
      $title = $data['title'];
      $message = $data['message'];
      $segment = $data['segment'];


      $fields = array(
        'app_id' => "651a6964-47af-42f8-b87b-33d261a21887",
        'included_segments' => array($segment),
        'contents' => array( "en" => $message ),
        'headings' => array( "en" => $title ),
      );

      if (array_key_exists('send_after', $data)) {
        $fields['send_after'] = $data['send_after'];
      }
      if (array_key_exists('data', $data)) {
        $fields['data'] = $data['data'];
      }

      $fields = json_encode($fields);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                             'Authorization: Basic NzQ2NzUzZDEtNGMyZi00Yjg5LTgyZjAtMjdhOGEwZjA4NGJh'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);

      $response = curl_exec($ch);
      curl_close($ch);

      $response = json_decode ($response, true);

      if (!$response) {
        echo 'PUSH NOTIFICATION SCHEDULING FAILED';
        return 'NULL';
      }

      if (array_key_exists('error', $response)) {
        var_dump($response['error']);
        return 'NULL';
      }
      else {
       return $response['id'];
      }
    }
    function cancelNotification($notifId) {
      if (!$notifId) return false;

      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications/".$notifId."?app_id=651a6964-47af-42f8-b87b-33d261a21887");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic NzQ2NzUzZDEtNGMyZi00Yjg5LTgyZjAtMjdhOGEwZjA4NGJh'));


      $response = curl_exec($ch);
      curl_close($ch);

      $response = json_decode ($response, true);

      if (!$response) {
        echo 'PUSH NOTIFICATION cancelling FAILED';
        return false;
      }

      return true;
    }
    function getNotificationsFromMatch($match) {
      $versusTeam = $match['VersusTeam'];
      $atHome = $match['VersusTeamAtHome'];
      $date = $match['Date'];
      $hour = $match['GameHour'];
      $minute =  $match['GameMinute'];
      $ampm = $match['GameAMPM'];


      $notifications = [];
      $notifFormat = 'Y-m-d H:i:s TO';
      $title = $atHome === '0' ? ($versusTeam.' vs FC Juarez') : ('FC Juarez vs '.$versusTeam);
      $current = new DateTime('now', new DateTimeZone('America/Chihuahua'));
      $matchDate = DateTime::createFromFormat('Y-m-d h:i a T', $date. ' '.$hour.':'.$minute. ' '.strtolower($ampm). ' America/Chihuahua');
      $notifDate = DateTime::createFromFormat('Y-m-d h:i a T', $date. ' '.$hour.':'.$minute. ' '.strtolower($ampm). ' America/Chihuahua')->sub(new DateInterval('PT1H'));
      $preNotifDate = $matchDate->sub(new DateInterval('P3D'));

      if ($preNotifDate > $current) { //if we can still send the preMatch notification.
        $preNotif = array(
          'title' => $title,
          'message' => 'En 3 dias vive la emoción de ser Bravo',
          'segment' => 'MATCH_NEW_ALERTS',
          'send_after' => $preNotifDate->format($notifFormat),
          'data' => array("page" => "matchCalendar" ),
        );
        array_push($notifications, $preNotif);
      }
      if ($notifDate > $current) { //if we can still send the match notification.
        $notif = array(
          'title' => $title,
          'message' => '¡En una hora vive la emoción de ser Bravo!',
          'segment' => 'MATCH_NEW_ALERTS',
          'send_after' => $notifDate->format($notifFormat),
        );
        array_push($notifications, $notif);  }

      return $notifications;
    }
    function scheduleMatchNotifications($match) {
      $ids = [];

      if (!$match['Active']) {
        return ['NULL', 'NULL'];
      }

      $notifs = getNotificationsFromMatch($match);
      foreach($notifs as $item) {
        array_push($ids, sendNotification($item));
      }

      $size = sizeof($ids);
      if ($size === 0) $ids = ['NULL', 'NULL'];
      if ($size === 1) $ids[1] = 'NULL';

      return $ids;
    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" Data Format Functions ">

    function fstrreq($strValue){
        #fstrreq = format string required
        $strValue = str_replace("'", "''", $strValue);
        return "'". trim($strValue) . "'";
    }

    function fstrnotreq($strValue){
        #fstrnotreq = format string not required
        $strValue = str_replace("'", "''", $strValue);
        return strlen(trim($strValue)) > 0 ? "'". trim($strValue) . "'" : 'NULL';
    }

    function fnumreq($strValue){
        #fstrreq = format string required
        $strValue = trim(str_replace(",", "", $strValue));
        return strlen($strValue) > 0 ? $strValue : 0;
    }

    function fnumnotreq($strValue){
        #fstrnotreq = format string not required
        return strlen(trim($strValue)) > 0 ? $strValue  : 'NULL';
    }

    function fbolreq($strValue){
        return strlen($strValue) > 0 ? $strValue : 0;
    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" Season Functions ">

    function selectSeason( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        #$query = "CALL SelectSeason( '$arrData[Key]', $arrData[Offset], $arrData[RecordsPerPage] )";
        $query = 'CALL SelectSeason(' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[OrderBy]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertSeason( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertSeason( ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateSeason( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateSeason( ' .
        $arrData[SeasonId] . ", " .

        fstrreq($arrData[InitialTitle]) . ', ' .
        fstrnotreq($arrData[InitialDescription]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteSeason( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteSeason($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" Banner Functions ">

    function selectBanner( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        #$query = "CALL SelectSeason( '$arrData[Key]', $arrData[Offset], $arrData[RecordsPerPage] )";
        $query = 'CALL SelectBanner(' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[OrderBy]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertBanner( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertBanner( ' .
        fstrreq($arrData[Title]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateBanner( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateBanner( ' .
        $arrData[BannerId] . ", " .

        fstrreq($arrData[InitialTitle]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        fstrreq($arrData[Title]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteBanner( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteBanner($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" Advertisement Functions ">

    function selectAdvertisement( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        #$query = "CALL SelectSeason( '$arrData[Key]', $arrData[Offset], $arrData[RecordsPerPage] )";
        $query = 'CALL SelectAdvertisement(' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[OrderBy]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertAdvertisement( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertAdvertisement( ' .
        $arrData[AdvertisementId] . ', ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[LinkAddress]) . ', ' .
        fbolreq($arrData[ApplyExpiration]) . ', ' .
        fstrnotreq($arrData[StartUpDate]) . ', ' .
        fstrnotreq($arrData[ExpirationDate]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateAdvertisement( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateAdvertisement( ' .
        $arrData[AdvertisementId] . ", " .

        fstrreq($arrData[InitialTitle]) . ', ' .
        fstrnotreq($arrData[InitialLinkAddress]) . ', ' .
        fbolreq($arrData[InitialApplyExpiration]) . ', ' .
        fstrnotreq($arrData[InitialStartUpDate]) . ', ' .
        fstrnotreq($arrData[InitialExpirationDate]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[LinkAddress]) . ', ' .
        fbolreq($arrData[ApplyExpiration]) . ', ' .
        fstrnotreq($arrData[StartUpDate]) . ', ' .
        fstrnotreq($arrData[ExpirationDate]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteAdvertisement( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteAdvertisement($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" Tournament Functions ">

    function selectTournament( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        #$query = "CALL SelectSeason( '$arrData[Key]', $arrData[Offset], $arrData[RecordsPerPage] )";
        $query = 'CALL SelectTournament(' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[OrderBy]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertTournament( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertTournament( ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateTournament( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateTournament( ' .
        $arrData[TournamentId] . ", " .

        fstrreq($arrData[InitialTitle]) . ', ' .
        fstrnotreq($arrData[InitialDescription]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteTournament( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteTournament($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" GameEvent Functions ">

    function selectGameEvent( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL SelectGameEvent(' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[OrderBy]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertGameEVent( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertGameEvent( ' .
        fstrreq($arrData[Title]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateGameEvent( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateGameEvent( ' .
        $arrData[SeasonId] . ", " .

        fstrreq($arrData[InitialTitle]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        fstrreq($arrData[Title]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteGameEvent( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteGameEvent($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" GameFuture Functions ">

    function selectGameFuture( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL SelectGameFuture( '$arrData[Key]', $arrData[Offset], $arrData[RecordsPerPage] )";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function selectGameFutureSeason( $id, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL SelectGameFutureSeason($id)";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertGameFuture( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $pnIds = scheduleMatchNotifications($arrData);

        $query = 'CALL InsertGameFuture( ' .
        $arrData[SeasonId] . ', ' .
        $arrData[TournamentId] . ', ' .
        $arrData[Week] . ', ' .
        fstrreq($arrData[Date]) . ', ' .
        fstrreq($arrData[Hour]) . ', ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Subtitle]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fstrreq($arrData[VersusTeam]) . ', ' .
        fbolreq($arrData[VersusTeamAtHome]) . ', ' .
        fstrreq($arrData[Stadium]) . ', ' .
        fstrreq($arrData[City]) . ', ' .
        fstrnotreq($arrData[Address]) . ', ' .
        fstrnotreq($arrData[LinkAddress1]) . ', ' .
        fstrnotreq($arrData[LinkAddress2]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .
        fstrnotreq($pnIds[0]) . ', ' .
        fstrnotreq($pnIds[1]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateGameFuture( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
          $returnMessage = $db->getErrorMessage();
          return false;
        }

        cancelNotification($arrData['pnId1']);
        cancelNotification($arrData['pnId2']);
        $pnIds = scheduleMatchNotifications($arrData);


        $query = 'CALL UpdateGameFuture( ' .
        $arrData[GameFutureId] . ", " .

        $arrData[InitialSeasonId] . ', ' .
        $arrData[InitialTournamentId] . ', ' .
        $arrData[InitialWeek] . ', ' .
        fstrreq($arrData[InitialDate]) . ', ' .
        fstrreq($arrData[InitialHour]) . ', ' .
        fstrreq($arrData[InitialTitle]) . ', ' .
        fstrnotreq($arrData[InitialSubtitle]) . ', ' .
        fstrnotreq($arrData[InitialDescription]) . ', ' .
        fstrreq($arrData[InitialVersusTeam]) . ', ' .
        fbolreq($arrData[InitialVersusTeamAtHome]) . ', ' .
        fstrreq($arrData[InitialStadium]) . ', ' .
        fstrreq($arrData[InitialCity]) . ', ' .
        fstrnotreq($arrData[InitialAddress]) . ', ' .
        fstrnotreq($arrData[InitialLinkAddress1]) . ', ' .
        fstrnotreq($arrData[InitialLinkAddress2]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        $arrData[SeasonId] . ', ' .
        $arrData[TournamentId] . ', ' .
        $arrData[Week] . ', ' .
        fstrreq($arrData[Date]) . ', ' .
        fstrreq($arrData[Hour]) . ', ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Subtitle]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fstrreq($arrData[VersusTeam]) . ', ' .
        fbolreq($arrData[VersusTeamAtHome]) . ', ' .
        fstrreq($arrData[Stadium]) . ', ' .
        fstrreq($arrData[City]) . ', ' .
        fstrnotreq($arrData[Address]) . ', ' .
        fstrnotreq($arrData[LinkAddress1]) . ', ' .
        fstrnotreq($arrData[LinkAddress2]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .
        fstrnotreq($pnIds[0]) . ', ' .
        fstrnotreq($pnIds[1]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteGameFuture( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $id = $arrData['p1'];
        cancelNotification($arrData['pnId1']);
        cancelNotification($arrData['pnId2']);

        $query = "CALL DeleteGameFuture($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" GamePresent Functions ">

    function selectGamePresent( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL SelectGamePresent( '$arrData[Key]', $arrData[Offset], $arrData[RecordsPerPage] )";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function selectGamePresentData( $id, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL SelectGamePresentData($id)";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $recordset = $recordset[0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function selectGamePresentSeason( $id, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL SelectGamePresentSeason($id)";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertGamePresent( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertGamePresent( ' .
        $arrData[GameFutureId] . ', ' .
        fstrnotreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fnumreq($arrData[ScoreHome]) . ', ' .
        fnumreq($arrData[ScoreAway]) . ', ' .
        fbolreq($arrData[Finalized]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateGamePresent( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateGamePresent( ' .
        $arrData[GamePresentId] . ", " .

        $arrData[InitialGameFutureId] . ', ' .
        fstrreq($arrData[InitialTitle]) . ', ' .
        fstrnotreq($arrData[InitialDescription]) . ', ' .
        fnumreq($arrData[InitialScoreHome]) . ', ' .
        fnumreq($arrData[InitialScoreAway]) . ', ' .
        fbolreq($arrData[InitialFinalized]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        $arrData[GameFutureId] . ', ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fnumreq($arrData[ScoreHome]) . ', ' .
        fnumreq($arrData[ScoreAway]) . ', ' .
        fbolreq($arrData[Finalized]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteGamePresent( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteGamePresent($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" GamePresentMinute Functions ">

    function selectGamePresentMinute( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL SelectGamePresentMinute( $arrData[GamePresentId], '$arrData[Key]', $arrData[Offset], $arrData[RecordsPerPage] )";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertGamePresentMinute( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertGamePresentMinute( ' .
        $arrData[GamePresentId] . ', ' .

        $arrData[GameEventId] . ', ' .
        $arrData[Minute] . ', ' .
        fstrnotreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .

        fnumreq($arrData[ScoreHome]) . ', ' .
        fnumreq($arrData[ScoreAway]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();

        if ($dbResult) {
          $response = sendNotification(array(
            'title' => $arrData[GameEventAction],
            'message' => $arrData[Description],
            'segment' => $arrData[GameEventId] === '14' ? 'MATCH_GOAL_ALERTS' : 'GENERAL_ALERTS',
            'data' =>  array("page" => "minute"),
          ));
        }

        return $dbResult;

    }

    function updateGamePresentMinute( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateGamePresentMinute( ' .
        $arrData[GamePresentId] . ", " .
        $arrData[DetailId] . ", " .

        $arrData[InitialGameEventId] . ', ' .
        $arrData[InitialMinute]  . ', ' .
        fstrreq($arrData[InitialTitle]) . ', ' .
        fstrnotreq($arrData[InitialDescription]) . ', ' .

        $arrData[GameEventId] . ', ' .
        $arrData[Minute] . ', ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .

        fnumreq($arrData[ScoreHome]) . ', ' .
        fnumreq($arrData[ScoreAway]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();

        if ($dbResult) {
          $response = sendNotification(array(
            'title' => $arrData[GameEventAction],
            'message' => $arrData[Description],
            'segment' => $arrData[GameEventId] === '14' ? 'MATCH_GOAL_ALERTS' : 'GENERAL_ALERTS',
            'data' =>  array("page" => "minute"),
          ));
        }

        return $dbResult;

    }

    function deleteGamePresentMinute( $gamePresentId, $detailId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteGamePresentMinute($gamePresentId, $detailId, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" GamePast Functions ">

    function selectGamePast( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL SelectGamePast( '$arrData[Key]', $arrData[Offset], $arrData[RecordsPerPage] )";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertGamePast( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertGamePast( ' .
        $arrData[GamePresentId] . ', ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Subtitle]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fstrnotreq($arrData[LinkAddress1]) . ', ' .
        fstrnotreq($arrData[LinkAddress2]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateGamePast( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateGamePast( ' .
        $arrData[GamePastId] . ", " .

        $arrData[InitialGamePresentId] . ', ' .
        fstrreq($arrData[InitialTitle]) . ', ' .
        fstrnotreq($arrData[InitialSubtitle]) . ', ' .
        fstrnotreq($arrData[InitialDescription]) . ', ' .
        fstrnotreq($arrData[InitialLinkAddress1]) . ', ' .
        fstrnotreq($arrData[InitialLinkAddress2]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        $arrData[GamePresentId] . ', ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Subtitle]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fstrnotreq($arrData[LinkAddress1]) . ', ' .
        fstrnotreq($arrData[LinkAddress2]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteGamePast( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteGamePast($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" SocialNetwork Functions ">

    function selectSocialNetwork( &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        #$query = "CALL SelectSeason( '$arrData[Key]', $arrData[Offset], $arrData[RecordsPerPage] )";
        $query = 'CALL SelectSocialNetwork()';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $recordset = $recordset[0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateSocialNetworkTwitter( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL UpdateSocialNetworkTwitter(
        '$arrData[TwitterUserName]',
        '$arrData[TwitterAccessToken]',
        '$arrData[TwitterAccessTokenSecret]',
        $arrData[LastUpdateBy],
        @ReturnValue, @ReturnMessage ); ";
        $query .= "SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateSocialNetworkFacebook( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL UpdateSocialNetworkFacebook(
        '$arrData[FacebookProfileId]',
        '$arrData[FacebookProfileName]',
        '$arrData[FacebookProfileAccessToken]',
        $arrData[LastUpdateBy],
        @ReturnValue, @ReturnMessage ); ";
        $query .= "SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" HybridCategory Functions ">

    function selectHybridCategory( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL SelectHybridCategory(' .
        fstrreq($arrData[Entity]) . ', ' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[OrderBy]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';

        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertHybridCategory( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL InsertHybridCategory( " .
        fstrreq($arrData[Entity]) . ", " .
        fstrreq($arrData[Title]) . ", " .
        fstrnotreq($arrData[Description]) . ", " .
        $arrData[LastUpdateBy] . ",
        @NextId, @ReturnValue, @ReturnMessage ); ";
        $query .= "SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateHybridCategory( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        /*
        echo $arrData[ClientAdditionalInfoId];
        echo '<br/>'. $arrData[ClientId];
        echo '<br/>'. $arrData[InitialTitle];
        echo '<br/>'. $arrData[InitialDescription];
        echo '<br/>'. $arrData[InitialDisplayOrder];
        echo '<br/>'. $arrData[Title];
        echo '<br/>'. $arrData[Description];
        echo '<br/>'. $arrData[DisplayOrder];
        */

        $query = "CALL UpdateHybridCategory( " .
        $arrData[CategoryId] . ", " .
        fstrreq($arrData[InitialTitle]) . ", " .
        fstrnotreq($arrData[InitialDescription]) . ", " .
        fstrreq($arrData[Title]) . ", " .
        fstrnotreq($arrData[Description]) . ", " .
        $arrData[LastUpdateBy] . ",
        @ReturnValue, @ReturnMessage ); ";
        $query .= "SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteHybridCategory( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteHybridCategory($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" HybridSubcategory Functions ">

    function selectHybridSubcategory( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL SelectHybridSubcategory(' .
        fstrreq($arrData[Entity]) . ', ' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[OrderBy]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';

        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertHybridSubcategory( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL InsertHybridSubcategory( " .
        fstrreq($arrData[Entity]) . ", " .
        fstrreq($arrData[Title]) . ", " .
        fstrnotreq($arrData[Description]) . ", " .
        $arrData[LastUpdateBy] . ",
        @NextId, @ReturnValue, @ReturnMessage ); ";
        $query .= "SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateHybridSubcategory( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        /*
        echo $arrData[ClientAdditionalInfoId];
        echo '<br/>'. $arrData[ClientId];
        echo '<br/>'. $arrData[InitialTitle];
        echo '<br/>'. $arrData[InitialDescription];
        echo '<br/>'. $arrData[InitialDisplayOrder];
        echo '<br/>'. $arrData[Title];
        echo '<br/>'. $arrData[Description];
        echo '<br/>'. $arrData[DisplayOrder];
        */

        $query = "CALL UpdateHybridSubcategory( " .
        $arrData[SubcategoryId] . ", " .
        fstrreq($arrData[InitialTitle]) . ", " .
        fstrnotreq($arrData[InitialDescription]) . ", " .
        fstrreq($arrData[Title]) . ", " .
        fstrnotreq($arrData[Description]) . ", " .
        $arrData[LastUpdateBy] . ",
        @ReturnValue, @ReturnMessage ); ";
        $query .= "SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteHybridSubcategory( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteHybridSubcategory($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" Article Functions ">

    function selectArticle( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL SelectArticle(' .
        fnumreq($arrData[CategoryId]) . ', ' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertArticle( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL InsertArticle( " .
        $arrData[CategoryId] . ', ' .
        fstrreq($arrData[Date]) . ", " .
        fstrreq($arrData[Title]) . ", " .
        fstrreq($arrData[Description]) . ", " .
        fstrnotreq($arrData[Author]) . ", " .
        fstrnotreq($arrData[LinkAddress]) . ", " .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ",
        @NextId, @ReturnValue, @ReturnMessage ); ";
        $query .= "SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateArticle( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL UpdateArticle( " .
        $arrData[ArticleId] . ", " .

        $arrData[InitialCategoryId] . ', ' .
        fstrreq($arrData[InitialDate]) . ", " .
        fstrreq($arrData[InitialTitle]) . ", " .
        fstrreq($arrData[InitialDescription]) . ", " .
        fstrnotreq($arrData[InitialAuthor]) . ", " .
        fstrnotreq($arrData[InitialLinkAddress]) . ", " .
        fbolreq($arrData[InitialActive]) . ', ' .

        $arrData[CategoryId] . ', ' .
        fstrreq($arrData[Date]) . ", " .
        fstrreq($arrData[Title]) . ", " .
        fstrreq($arrData[Description]) . ", " .
        fstrnotreq($arrData[Author]) . ", " .
        fstrnotreq($arrData[LinkAddress]) . ", " .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ",
        @ReturnValue, @ReturnMessage ); ";
        $query .= "SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteArticle( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteArticle($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" Gallery Functions ">

    function selectGallery( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL SelectGallery(' .
        fnumreq($arrData[CategoryId]) . ', ' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function selectGalleryData( $id, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL SelectGalleryData($id)";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $recordset = $recordset[0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertGallery( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL InsertGallery( " .
        $arrData[CategoryId] . ', ' .
        fstrreq($arrData[Date]) . ", " .
        fstrreq($arrData[Title]) . ", " .
        fbolreq($arrData[Active]) . ', ' .
        $arrData[LastUpdateBy] . ",
        @NextId, @ReturnValue, @ReturnMessage ); ";
        $query .= "SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateGallery( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL UpdateGallery( " .
        $arrData[GalleryId] . ", " .

        $arrData[InitialCategoryId] . ', ' .
        fstrreq($arrData[InitialDate]) . ", " .
        fstrreq($arrData[InitialTitle]) . ", " .
        fbolreq($arrData[InitialActive]) . ', ' .

        $arrData[CategoryId] . ', ' .
        fstrreq($arrData[Date]) . ", " .
        fstrreq($arrData[Title]) . ", " .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ",
        @ReturnValue, @ReturnMessage ); ";
        $query .= "SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteGallery( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteGallery($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" BravosTerritory Functions ">

    function selectBravosTerritory( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL SelectBravosTerritory(' .
        fnumreq($arrData[CategoryId]) . ', ' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertBravosTerritory( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertBravosTerritory( ' .
        $arrData[CategoryId] . ', ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fstrnotreq($arrData[Address]) . ', ' .
        fstrnotreq($arrData[GoogleMapsLatitude]) . ', ' .
        fstrnotreq($arrData[GoogleMapsLongitude]) . ', ' .
        fstrnotreq($arrData[LinkAddress1]) . ', ' .
        fstrnotreq($arrData[LinkAddress2]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateBravosTerritory( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateBravosTerritory( ' .
        $arrData[BravosTerritoryId] . ", " .

        $arrData[InitialCategoryId] . ', ' .
        fstrreq($arrData[InitialTitle]) . ', ' .
        fstrnotreq($arrData[InitialDescription]) . ', ' .
        fstrnotreq($arrData[InitialAddress]) . ', ' .
        fstrnotreq($arrData[InitialGoogleMapsLatitude]) . ', ' .
        fstrnotreq($arrData[InitialGoogleMapsLongitude]) . ', ' .
        fstrnotreq($arrData[InitialLinkAddress1]) . ', ' .
        fstrnotreq($arrData[InitialLinkAddress2]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        $arrData[CategoryId] . ', ' .
        fstrnotreq($arrData[Title]) . ', ' .
        fstrnotreq($arrData[Description]) . ', ' .
        fstrnotreq($arrData[Address]) . ', ' .
        fstrnotreq($arrData[GoogleMapsLatitude]) . ', ' .
        fstrnotreq($arrData[GoogleMapsLongitude]) . ', ' .
        fstrnotreq($arrData[LinkAddress1]) . ', ' .
        fstrnotreq($arrData[LinkAddress2]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteBravosTerritory( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteBravosTerritory($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" Roster Functions ">

    function selectRoster( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        #$query = "CALL SelectSeason( '$arrData[Key]', $arrData[Offset], $arrData[RecordsPerPage] )";
        $query = 'CALL SelectRoster(' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[OrderBy]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertRoster( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertRoster( ' .
        fstrreq($arrData[Name]) . ', ' .
        fstrreq($arrData[Number]) . ', ' .
        fstrreq($arrData[Position]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateRoster( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateRoster( ' .
        $arrData[RosterId] . ", " .

        fstrreq($arrData[InitialName]) . ', ' .
        fstrreq($arrData[InitialNumber]) . ', ' .
        fstrreq($arrData[InitialPosition]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        fstrreq($arrData[Name]) . ', ' .
        fstrreq($arrData[Number]) . ', ' .
        fstrreq($arrData[Position]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteRoster( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteRoster($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" Product Functions ">

    function selectProduct( $arrData, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL SelectProduct(' .
        fnumreq($arrData[CategoryId]) . ', ' .
        fnumreq($arrData[SubcategoryId]) . ', ' .
        fstrreq($arrData[Key]) . ', ' .
        fnumreq($arrData[OrderBy]) . ', ' .
        fnumreq($arrData[Offset]) . ', ' .
        fnumreq($arrData[RecordsPerPage]) . ')';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertProduct( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertProduct( ' .
        $arrData[CategoryId] . ', ' .
        fnumreq($arrData[SubcategoryId]) . ', ' .
        fstrreq($arrData[IssueDate]) . ', ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrreq($arrData[Description]) . ', ' .
        fstrreq($arrData[ProductNumber]) . ', ' .
        fnumreq($arrData[OnHand]) . ', ' .
        fnumreq($arrData[ActualPrice]) . ', ' .
        fnumreq($arrData[ListPrice]) . ', ' .
        fnumreq($arrData[ShippingPrice]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .
        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateProduct( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        /*
        echo $arrData[ClientAdditionalInfoId];
        echo '<br/>'. $arrData[ClientId];
        echo '<br/>'. $arrData[InitialTitle];
        echo '<br/>'. $arrData[InitialDescription];
        echo '<br/>'. $arrData[InitialDisplayOrder];
        echo '<br/>'. $arrData[Title];
        echo '<br/>'. $arrData[Description];
        echo '<br/>'. $arrData[DisplayOrder];
        */

        $query = 'CALL UpdateProduct( ' .
        $arrData[ProductId] . ', ' .

        $arrData[InitialCategoryId] . ', ' .
        fnumreq($arrData[InitialSubcategoryId]) . ', ' .
        fstrreq($arrData[InitialIssueDate]) . ', ' .
        fstrreq($arrData[InitialTitle]) . ', ' .
        fstrreq($arrData[InitialDescription]) . ', ' .
        fstrreq($arrData[InitialProductNumber]) . ', ' .
        fnumnotreq($arrData[InitialOnHand]) . ', ' .
        fnumreq($arrData[InitialActualPrice]) . ', ' .
        fnumreq($arrData[InitialListPrice]) . ', ' .
        fnumreq($arrData[InitialShippingPrice]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        $arrData[CategoryId] . ', ' .
        fnumreq($arrData[SubcategoryId]) . ', ' .
        fstrreq($arrData[IssueDate]) . ', ' .
        fstrreq($arrData[Title]) . ', ' .
        fstrreq($arrData[Description]) . ', ' .
        fstrreq($arrData[ProductNumber]) . ', ' .
        fnumnotreq($arrData[OnHand]) . ', ' .
        fnumreq($arrData[ActualPrice]) . ', ' .
        fnumreq($arrData[ListPrice]) . ', ' .
        fnumreq($arrData[ShippingPrice]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteProduct( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteProduct($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" User Functions ">

    function selectUser( $key, $offset, $recordsPerPage, &$totalTableRows, &$recordset, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL SelectUser( '$key', $offset, $recordsPerPage )";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset = $db->getRecordset(1);
            $totalTableRows = $db->getRecordset(2);
            $totalTableRows = $totalTableRows[0][0];

        }

        $db->closeConnection();
        return $dbResult;

    }

    function insertUser( $arrData, &$nextId, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL InsertUser( ' .
        $arrData[UserType] . ', ' .
        fstrreq($arrData[Name]) . ', ' .
        fstrnotreq($arrData[FatherName]) . ', ' .
        fstrnotreq($arrData[MotherName]) . ', ' .
        fstrnotreq($arrData[Email]) . ', ' .
        fstrreq($arrData[UserName]) . ', ' .
        fstrreq($arrData[Password]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @NextId, @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @NextId AS NextId, @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $nextId = $outputParameters[NextId];
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateUser( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateUser( ' .
        $arrData[UserId] . ', ' .

        $arrData[InitialUserType] . ', ' .
        fstrreq($arrData[InitialName]) . ', ' .
        fstrnotreq($arrData[InitialFatherName]) . ', ' .
        fstrnotreq($arrData[InitialMotherName]) . ', ' .
        fstrnotreq($arrData[InitialEmail]) . ', ' .
        fstrreq($arrData[InitialUserName]) . ', ' .
        fstrreq($arrData[InitialPassword]) . ', ' .
        fbolreq($arrData[InitialActive]) . ', ' .

        $arrData[UserType] . ', ' .
        fstrreq($arrData[Name]) . ', ' .
        fstrnotreq($arrData[FatherName]) . ', ' .
        fstrnotreq($arrData[MotherName]) . ', ' .
        fstrnotreq($arrData[Email]) . ', ' .
        fstrreq($arrData[UserName]) . ', ' .
        fstrreq($arrData[Password]) . ', ' .
        fbolreq($arrData[Active]) . ', ' .

        $arrData[LastUpdateBy] . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function deleteUser( $id, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL DeleteUser($id, @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function validateLogin( $arrData, &$recordset1, &$returnMessage ) {

        $db = new iDBManager();

        if ( ! $db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = "CALL ValidateLogin('$arrData[UserName]', '$arrData[Password]', @ReturnValue, @ReturnMessage);";
        $query .= "SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage;";

        $dbResult = true;
        $db->setSqlQuery($query);

        if (!$db->selectDataSet()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            $recordset1 = $db->getRecordset(1);
            $recordset1 = $recordset1[0];

            $outputParameters = $db->getOutputParameterValues();

            if ( isset($outputParameters[ReturnValue]) ){
                if ($outputParameters[ReturnValue] != 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                    $dbResult = false;
                }
                else
                    $returnMessage = $outputParameters[ReturnMessage];
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

    function updateUserPassword( $arrData, &$returnMessage ) {

        $db = new iDBManager();

        if ( !$db->openConnection() ){
            $returnMessage = $db->getErrorMessage();
            return false;
        }

        $query = 'CALL UpdateUserPassword( ' .
        $arrData[UserId] . ', ' .
        fstrreq($arrData[InitialPassword]) . ', ' .
        fstrreq($arrData[Password]) . ',
        @ReturnValue, @ReturnMessage ); ';
        $query .= 'SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage';
        //$query = "CALL SelectGenerationRecent( @ReturnValue, @ReturnMessage ); SELECT @ReturnValue AS ReturnValue, @ReturnMessage AS ReturnMessage";
        $db->setSqlQuery($query);

        $dbResult = true;
        if (!$db->executeNonQuery()){
            $returnMessage = $db->getErrorMessage();
            $dbResult = false;
        } else {

            //debug print output values
            $outputParameters = $db->getOutputParameterValues();
            if ( !empty($outputParameters) ){
                #foreach( $outputParameters as $key=>$value)
                #    echo $key. '=>'. $value. '<br>';

                if ($outputParameters[ReturnValue] == 0){
                    $returnMessage = $outputParameters[ReturnMessage];
                }
                else{
                    $returnMessage = 'Error('. $outputParameters[ReturnValue]. ') '. $outputParameters[ReturnMessage];
                    $dbResult = false;
                }

                #echo "DB->returnvalues ReturnValue = ". $outputParameters["ReturnValue"]. "<br>";
                #echo "DB->returnvalues Insertid = ". $outputParameters["NextId"]. "<br>";
            }

        }

        $db->closeConnection();
        return $dbResult;

    }

// </editor-fold>


?>