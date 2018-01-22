<?php

    include('lib/ifndatabase.php');
    include('../../lib/fngeneral.php');

    $queryParams = array();
    $strpos = strpos($_SERVER['QUERY_STRING'],'&'); // For Javascript parameter

    if (!$strpos)
        $queryString = $_SERVER['QUERY_STRING'];
    else{
        $queryString = substr($_SERVER['QUERY_STRING'], 0, $strpos);
        $javaString = substr($_SERVER['QUERY_STRING'], $strpos+1);
    }

    $queryString = base64_decode($queryString);
    $javaString = htmlspecialchars(urldecode($javaString));

    if ( strlen($queryString) )$queryParams =  getQueryStringParameters($queryString);
    if ( strlen($javaString) )$javaParams =  getQueryStringParameters($javaString);

    $fa   = (isset($queryParams[p1]) && strlen($queryParams[p1]) ) ? $queryParams[p1] : '';
    $rpp  = (isset($queryParams[p2]) && strlen($queryParams[p2]) ) ? $queryParams[p2] : 5;
    $page = (isset($queryParams[p3]) && strlen($queryParams[p3]) ) ? $queryParams[p3] : 1;
    if (!$strpos)
        $key  = (isset($queryParams[p4]) && strlen($queryParams[p4]) ) ? $queryParams[p4] : '';
    else
        $key  = (isset($javaParams[0]) && strlen($javaParams[0]) ) ? $javaParams[0] : '';

    #echo $key . '|' . $page . '|' . $fa;

    $recordsPerPage = $rpp;
    $phpSelf = $_SERVER['PHP_SELF'] . '?';

    $offset = ($page - 1) * $recordsPerPage;

    $arrData[Key] = $key;
    $arrData[Offset] = $offset;
    $arrData[RecordsPerPage] = $recordsPerPage;

    $result = selectGameFuture($arrData, $totalTableRows, $recordset, $returnMessage );

    echo '
    <table class="table table-striped table-hover table-bordered">
    <thead>
        <tr>
            <th>Nombre del rival</th>
            <th>Casa/Visita</th>
            <th>Fecha</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>';

    if (count($recordset)){
        foreach ( $recordset as $row ){

            $versusTeamAt = $row[VersusTeamAtHome] ? 'Casa' : 'Visita';

            echo '
        <tr>
            <td><strong>' . $row[VersusTeam] . '</strong><br/>';

            $images = '';
            for ($i=0; $i<=6; $i++){

                $fileExt = $i == 0 ? 'png' : 'jpg';
                if ($i == 0) $label = 'Logo Equipo';
                if ($i == 1) $label = 'Banner';
                if ($i >= 2) $label = 'Imagen / ' . ($i-1);

                $filename = "../binder/gamefuture/$row[GameFutureId]-$i.$fileExt";
                if (file_exists($filename)){
                    if (strlen($images)) $images .= '<br/>';
                    $images .= '<a href="' . $filename . '?' . rand(1,32000) . '" target="_blank">' . $label . ' Ver</a>';
                    $images .= ' | <a href="partido-futuro.php?' . encodeString("df=$filename") . '" style="color:red"
                    onClick="return window.confirm('. "'Eliminar " . $label . "?'" . ')">Eliminar</a>';
                }
            }

            $images .= '<br/><a href="../perfil-partidos-por-jugar.php?' . encodeString("gf=$row[GameFutureId]") . '" target="_blank">Ver Datos Juego</a>';

            echo $images . '
            </td>
            <td><div>' . $versusTeamAt . '</div></td>
            <td><div>' . $row[Date] . '</div></td>
            <td>';

            if ( strpos($fa, 'U') )
                echo '
                <a href="javascript:updateData(' . $row[GameFutureId] . ')"><i class="fa fa-cog fa-lg base-dark" aria-hidden="true"></i></a>';

            if ( strpos($fa, 'D') )
                echo '
                <a href="partido-futuro.php?' . encodeString('p1=' . $row[GameFutureId].'&pnId1='.$row['pnId1'].'&pnId2='.$row['pnId2'] ) . '"
                onClick="return window.confirm('. "'Eliminar registro seleccionado?'". ')" class="mrgn-l-sm"><i class="fa fa-times fa-lg text-danger" aria-hidden="true"></i></a>';

            echo '
            </td>
        </tr>';
        }
    }

    echo '
    </tbody>
    </table>';


    echo '
    <div style="display:none;">';

    if (count($recordset))
        foreach ( $recordset as $row )
            echo '
        <input type="hidden" id="form[pnId1' . $row[GameFutureId] . ']" name="form[pnId1' . $row[GameFutureId] . ']"  value="'. $row[pnId1] . '"  />
        <input type="hidden" id="form[pnId2' . $row[GameFutureId] . ']" name="form[pnId2' . $row[GameFutureId] . ']"  value="'. $row[pnId2] . '"  />
        <input type="text" id="form[SeasonId' . $row[GameFutureId] . ']" name="form[InitialSeasonId' . $row[GameFutureId] . ']"  value="'. $row[SeasonId] . '"  />
        <input type="text" id="form[TournamentId' . $row[GameFutureId] . ']" name="form[InitialTournamentId' . $row[GameFutureId] . ']"  value="'. $row[TournamentId] . '"  />
        <input type="text" id="form[Week' . $row[GameFutureId] . ']" name="form[InitialWeek' . $row[GameFutureId] . ']"  value="'. $row[Week] . '"  />
        <input type="text" id="form[Date' . $row[GameFutureId] . ']" name="form[InitialDate' . $row[GameFutureId] . ']"  value="'. $row[Date] . '"  />
        <input type="text" id="form[Hour' . $row[GameFutureId] . ']" name="form[InitialHour' . $row[GameFutureId] . ']"  value="'. $row[Hour] . '"  />
        <input type="text" id="form[GameHour' . $row[GameFutureId] . ']" name="form[InitialGameHour' . $row[GameFutureId] . ']"  value="'. date("h", strtotime($row[Hour])) . '"  />
        <input type="text" id="form[GameMinute' . $row[GameFutureId] . ']" name="form[InitialGameMinute' . $row[GameFutureId] . ']"  value="'. date("i", strtotime($row[Hour])) . '"  />
        <input type="text" id="form[GameAMPM' . $row[GameFutureId] . ']" name="form[InitialGameAMPM' . $row[GameFutureId] . ']"  value="'. date("A", strtotime($row[Hour])) . '"  />
        <input type="text" id="form[Title' . $row[GameFutureId] . ']" name="form[InitialTitle' . $row[GameFutureId] . ']"  value="'. $row[Title] . '"  />
        <input type="text" id="form[Subtitle' . $row[GameFutureId] . ']" name="form[InitialSubtitle' . $row[GameFutureId] . ']"  value="'. $row[Subtitle] . '"  />
        <textarea id="form[Description' . $row[GameFutureId] . ']" name="form[InitialDescription' . $row[GameFutureId] . ']" >'. fstrhsec($row[Description]) . '</textarea>
        <input type="text" id="form[VersusTeam' . $row[GameFutureId] . ']" name="form[InitialVersusTeam' . $row[GameFutureId] . ']"  value="'. $row[VersusTeam] . '"  />
        <input type="text" id="form[VersusTeamAtHome' . $row[GameFutureId] . ']" name="form[InitialVersusTeamAtHome' . $row[GameFutureId] . ']"  value="'. $row[VersusTeamAtHome] . '"  />
        <input type="text" id="form[Stadium' . $row[GameFutureId] . ']" name="form[InitialStadium' . $row[GameFutureId] . ']"  value="'. $row[Stadium] . '"  />
        <input type="text" id="form[City' . $row[GameFutureId] . ']" name="form[InitialCity' . $row[GameFutureId] . ']"  value="'. $row[City] . '"  />
        <input type="text" id="form[Address' . $row[GameFutureId] . ']" name="form[InitialAddress' . $row[GameFutureId] . ']"  value="'. $row[Address] . '"  />
        <input type="text" id="form[LinkAddress1' . $row[GameFutureId] . ']" name="form[InitialLinkAddress1' . $row[GameFutureId] . ']"  value="'. $row[LinkAddress1] . '"  />
        <input type="text" id="form[LinkAddress2' . $row[GameFutureId] . ']" name="form[InitialLinkAddress2' . $row[GameFutureId] . ']"  value="'. $row[LinkAddress2] . '"  />
        <input type="text" id="form[Active' . $row[GameFutureId] . ']" name="form[InitialActive' . $row[GameFutureId] . ']"  value="'. $row[Active] . '"  />';

    echo '
        <input type="text" name="form[Key]" value="' . $key . '" />
        <input type="text" name="form[Page]" value="' . $page . '" />
    </div>';


    if ($totalTableRows > $recordsPerPage){

        echo '
    <div class="col-sm-12">

        <div class="col-sm-6 pull-right">
        <div class="row">

            <ul class="pagination pull-right">';

        if ($page > 1 )
            echo '
                <li><a onclick="javascript:getDataReturnText(' . "'" . $phpSelf . encodeString(
                'p1=' . $fa .
                '&p2=' . $rpp .
                '&p3=' . ($page-1) .
                '&p4=' . $key) . "', 'divRecords'" . ');insertData();' . '"><i class="fa fa-angle-left"></i>&nbsp;ANTERIOR</a></li>';

        if ($page < ceil($totalTableRows / $recordsPerPage ) )
            echo '
                <li><a onclick="javascript:getDataReturnText(' . "'" . $phpSelf . encodeString(
                'p1=' . $fa .
                '&p2=' . $rpp .
                '&p3=' . ($page+1) .
                '&p4=' . $key) . "', 'divRecords'" . ');insertData();' . '">SIGUIENTE&nbsp;<i class="fa fa-angle-right"></i></a></li>';

        echo '
            </ul>

        </div>
        </div>

    </div>';

    }

?>