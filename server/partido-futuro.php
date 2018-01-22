<?php

    # Session Setup
    ini_set("session.gc_maxlifetime", "18000");
    session_start();
    $plenary = &$_SESSION['plenary'];
    #-------------------------------------------------------------------------------------------------

    # Includes
    include('loginverify.php');
    include('lib/ifndatabase.php');
    include('../../lib/fngeneral.php');
    #-------------------------------------------------------------------------------------------------

    # QueryString Variables
    $queryString = $_SERVER['QUERY_STRING'];

    $queryParams = array();
    $queryString = base64_decode($queryString);

    if ( strlen($queryString) ) $queryParams =  getQueryStringParameters($queryString);

    if (isset($queryParams[key])) $arrData[Key] = $queryParams[key];
    if (isset($queryParams[page])) $arrData[Page] = $queryParams[page];
    #-------------------------------------------------------------------------------------------------

    #Delete the following, testing only
    $plenary[User][UserId] = 0;
    $plenary[User][Administrator] = 1;
    $plenary[User][TableSelectRecordsPerPage] = 5;

    # Form Variables
    $module = 'Partidos Futuros';
    $phpSelf = $_SERVER['PHP_SELF'];

    $actionFlag = isset( $_REQUEST[actionFlag] ) ? $_REQUEST[actionFlag] : 1;
    $formAccess = ($plenary[User][Administrator] ? 'IUD' : $plenary[User][FormAccess][User]);
    $recordsPerPage = $plenary[User][TableSelectRecordsPerPage];
    #-------------------------------------------------------------------------------------------------

    # Delete selected file
    if (isset($queryParams[df])){
        unlink($queryParams[df]);
        header("location:$phpSelf");
    }
    #-------------------------------------------------------------------------------------------------


    function displayValue($value){
        global $dbResult;
        return (!$dbResult)? fstrhsec($value): '';
    }

    function writeOptionListSeason( $selected ){

        $arrData[OrderBy] = 1;
        $result = selectSeason( $arrData, $totalTableRows, $recordset, $returnMessage);

        if ( $result ){
            foreach ( $recordset as $row ){
                echo '<option value="' . $row[SeasonId] . '"'  ;
                if( $selected == $row[SeasonId] ) echo ' SELECTED ';
                echo '>'.  $row[Title] .'</option>';
            }
        }

    }

    function writeOptionListTournament( $selected ){

        $arrData[OrderBy] = 1;
        $result = selectTournament( $arrData, $totalTableRows, $recordset, $returnMessage);

        if ( $result ){
            foreach ( $recordset as $row ){
                echo '<option value="' . $row[TournamentId] . '"'  ;
                if( $selected == $row[TournamentId] ) echo ' SELECTED ';
                echo '>'.  $row[Title] .'</option>';
            }
        }

    }

    if ( isset( $_REQUEST['actionFlag'] )  ){

        $actionFlag = $_REQUEST['actionFlag'];

        foreach( $_REQUEST['form'] as $key=>$value)
            $arrData[$key] = $value;

        if ( !strlen($arrData[SeasonId]) )
            $inputWarning .= '* Temporada<br/>';

        if ( !strlen($arrData[TournamentId]) )
            $inputWarning .= '* Torneo<br/>';

        if ( !strlen($arrData[Week]) )
            $inputWarning .= '* Jornada<br/>';

        if ( !strlen($arrData[Date]) )
            $inputWarning .= '* Fecha del partido<br/>';

        if ( !strlen($arrData[Title]) )
            $inputWarning .= '* Titulo<br/>';

        if ( !strlen($arrData[VersusTeam]) )
            $inputWarning .= '* Nombre del rival<br/>';

        if ( !strlen($arrData[VersusTeamAtHome]) )
            $inputWarning .= '* Rival juega en casa o visitante?<br/>';

        if ( !strlen($arrData[Stadium]) )
            $inputWarning .= '* Estadio<br/>';

        if ( !strlen($arrData[City]) )
            $inputWarning .= '* Ciudad<br/>';

        if ($actionFlag ==1 && !strlen($_FILES[fileUpload0]['tmp_name']))
            $inputWarning .= '* Logo equipo<br/>';

        if ($actionFlag == 1 && !strlen($_FILES[fileUpload1]['tmp_name']))
            $inputWarning .= '* Banner Publicitario<br/>';


        if (!$inputWarning){

            $id = $arrData[GameFutureId];

            #print_r($arrData); exit;

            #if (strlen($arrData[Date])) $arrData[DBDate] = date('Y-m-d', strtotime($arrData[Date]));
            $arrData[Hour] = date("H:i", strtotime("$arrData[GameHour]:$arrData[GameMinute] $arrData[GameAMPM]"));
            $arrData[Description] = freplacechr13(fstrhsec($arrData[Description]));

            $arrData[InitialSeasonId] = $arrData['InitialSeasonId' . $id];
            $arrData[InitialTournamentId] = $arrData['InitialTournamentId' . $id];
            $arrData[InitialWeek] = $arrData['InitialWeek' . $id];
            $arrData[InitialDate] = $arrData['InitialDate' . $id];
            $arrData[InitialHour] = $arrData['InitialHour' . $id];
            $arrData[InitialTitle] = $arrData['InitialTitle' . $id];
            $arrData[InitialSubtitle] = $arrData['InitialSubtitle' . $id];
            $arrData[InitialDescription] = freplacechr13(fstrhsec($arrData[InitialDescription]));
            $arrData[InitialVersusTeam] = $arrData['InitialVersusTeam' . $id];
            $arrData[InitialVersusTeamAtHome] = $arrData['InitialVersusTeamAtHome' . $id];
            $arrData[InitialStadium] = $arrData['InitialStadium' . $id];
            $arrData[InitialCity] = $arrData['InitialCity' . $id];
            $arrData[InitialAddress] = $arrData['InitialAddress' . $id];
            $arrData[InitialLinkAddress1] = $arrData['InitialLinkAddress1' . $id];
            $arrData[InitialLnikAddress2] = $arrData['InitialLinkAddress2' . $id];
            $arrData[InitialActive] = $arrData['InitialActive' . $id];

            $arrData[LastUpdateBy] = $plenary[User][UserId];

            #print_r($arrData); exit;

            for ($i = 0; $i <= 6; $i++)
                $arrData['FileUpload' . $i] = $_FILES['fileUpload' . $i]['tmp_name'];


            if ($actionFlag == 1)
                $dbResult = insertGameFuture( $arrData, $arrData[GameFutureId], $returnMessage );
            else
                $dbResult = updateGameFuture( $arrData, $returnMessage );

            if ($dbResult) {

                if ($arrData[FileUpload0])
                    copy($arrData[FileUpload0], '../binder/gamefuture/' . $arrData[GameFutureId] . '-0.png');

                $imageId = 1;
                for ($i = 1; $i <= 6; $i++){
                    if ($arrData['FileUpload' . $i]){

                        $uploadedFile = $arrData['FileUpload' . $i];
                        $file1 = '../binder/gamefuture/' . $arrData[GameFutureId] . '-' . $imageId . '.jpg';
                        copy($uploadedFile, $file1);

                        unlink($uploadedFile);
                        unset($uploadedFile, $file1);
                        $imageId ++;

                    }
                }

                if ($actionFlag == 2) $phpSelf .= '?' . encodeString ('key=' . $arrData[Key] . '&page=' . $arrData[Page]);
                header('location:' . $phpSelf);
            }
        }
    }


    if ( isset($queryParams[p1]) ){
        $result = deleteGameFuture($queryParams[p1], $returnMessage);
        if ($result) header('Location:' . $phpSelf);
    }

    $buttonCaption = ($actionFlag == 1) ? 'Agregar' : 'Modificar';
    if ($actionFlag == 1 && !isset($arrData[GameFutureId])) {
        $arrData[Active] = 1;
        $arrData[VesusTeamAtHome] = -1;
    }

?>
<!doctype html>
<html class="no-js" lang="es">
<head>
<meta charset="utf-8">
<title><?= $module ?> ::: FC Juárez Bravos ::: Manager Mode</title>

<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>

<!--FAVICON-->
<link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">

<!--FONTS-->
<link href="https://fonts.googleapis.com/css?family=Exo:100,100i,300,300i,400,400i,700,700i,800,800i,900,900i" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
<script src="https://use.fontawesome.com/d6f7ba785b.js"></script>
<!--
font-family: 'Exo', sans-serif;
-->

<!--CSS-->
<link rel="stylesheet" type="text/css" href="assets/css/vendor.min.css">
<link rel="stylesheet" type="text/css" href="assets/css/plugins.min.css">
<link rel="stylesheet" type="text/css" href="assets/css/manager-mode.css">
<link href="assets/plugin/full-calendar/css/fullcalendar.min.css" rel="stylesheet"/>

<script type="text/javascript" src="assets/scripts/scripts.js"></script>
<script type="text/javascript">

    function updateData(fieldId){

        //alert(document.getElementById('uxTitle' + fieldId).value);

        document.getElementById('actionFlag').value = 2;
        document.getElementById('button').value = 'Modificar';
        document.getElementById('form[Id]').value = fieldId;

        $('#form\\[SeasonId\\]').val( $('#form\\[SeasonId' + fieldId + '\\]').val() );
        $('#form\\[SeasonId\\]').change();

        $('#form\\[TournamentId\\]').val( $('#form\\[TournamentId' + fieldId + '\\]').val() );
        $('#form\\[TournamentId\\]').change();

        $('#form\\[Week\\]').val( $('#form\\[Week' + fieldId + '\\]').val() );
        $('#form\\[Week\\]').change();

        document.getElementById('form[Date]').value = document.getElementById('form[Date' + fieldId + ']').value;

        $('#form\\[GameHour\\]').val( $('#form\\[GameHour' + fieldId + '\\]').val() );
        $('#form\\[GameHour\\]').change();

        $('#form\\[GameMinute\\]').val( $('#form\\[GameMinute' + fieldId + '\\]').val() );
        $('#form\\[GameMinute\\]').change();

        $('#form\\[GameAMPM\\]').val( $('#form\\[GameAMPM' + fieldId + '\\]').val() );
        $('#form\\[GameAMPM\\]').change();

        document.getElementById('form[Title]').value = document.getElementById('form[Title' + fieldId + ']').value;
        document.getElementById('form[Subtitle]').value = document.getElementById('form[Subtitle' + fieldId + ']').value;
        document.getElementById('form[Description]').value = document.getElementById('form[Description' + fieldId + ']').value;
        document.getElementById('form[VersusTeam]').value = document.getElementById('form[VersusTeam' + fieldId + ']').value;

        $('#form\\[VersusTeamAtHome\\]').val( $('#form\\[VersusTeamAtHome' + fieldId + '\\]').val() );
        $('#form\\[VersusTeamAtHome\\]').change();

        document.getElementById('form[Stadium]').value = document.getElementById('form[Stadium' + fieldId + ']').value;
        document.getElementById('form[City]').value = document.getElementById('form[City' + fieldId + ']').value;
        document.getElementById('form[Address]').value = document.getElementById('form[Address' + fieldId + ']').value;
        document.getElementById('form[LinkAddress1]').value = document.getElementById('form[LinkAddress1' + fieldId + ']').value;

        if (document.getElementById('form[Active' + fieldId + ']').value == 1){
            document.getElementById('form[Active]').checked = true;
        }else{
            document.getElementById('form[Active]').checked = false;
        }

        document.getElementById('form[SeasonId]').focus();

    }

    function insertData(){

        document.getElementById('actionFlag').value = 1;
        document.getElementById('button').value = 'Agregar';

        document.getElementById('form[Id]').value = "";

        document.getElementById('form[SeasonId]').options[0].selected = true;
        $('#form\\[SeasonId\\]').change();

        document.getElementById('form[TournamentId]').options[0].selected = true;
        $('#form\\[TournamentId\\]').change();

        document.getElementById('form[Week]').options[0].selected = true;
        $('#form\\[Week\\]').change();

        document.getElementById('form[Date]').value = "";

         document.getElementById('form[GameHour]').options[0].selected = true;
        $('#form\\[GameHour\\]').change();

         document.getElementById('form[GameMinute]').options[0].selected = true;
        $('#form\\[GameMinute\\]').change();

         document.getElementById('form[GameAMPM]').options[0].selected = true;
        $('#form\\[GameAMPM\\]').change();

        document.getElementById('form[Title]').value = "";
        document.getElementById('form[Subtitle]').value = "";
        document.getElementById('form[Description]').value = "";
        document.getElementById('form[VersusTeam]').value = "";

        document.getElementById('form[VersusTeamAtHome]').options[0].selected = true;
        $('#form\\[VersusTeamAtHome\\]').change();

        document.getElementById('form[Stadium]').value = "";
        document.getElementById('form[City]').value = "";
        document.getElementById('form[Address]').value = "";
        document.getElementById('form[LinkAddress1]').value = "";

        document.getElementById('form[Active]').checked = true;

        document.getElementById('form[SeasonId]').focus();

    }

    function searchByKey(key){

        getDataReturnText('partido-futuronav.php?<?= encodeString('p1=' . $formAccess . '&p2=' . $recordsPerPage) . '&' . encodeParameter(10) ?>' + key, 'divRecords');

    }

    </script>


</head>

<body>
<div class="bst-wrapper">

    <!--HEADER-->

    <?php include("header.php"); ?>

    <!--HEADER-->

    <!--MAIN-->

    <div class="bst-main">
    <div class="fyt-main-top"></div>
    <div class="bst-main-wrapper pad-all-md">

    <!--NAVIGATION SIDEBAR-->
    <?php include("navigation.php"); ?>
    <!--NAVIGATION SIDEBAR-->

    <!--CONTENT-->
    <div class="bst-content-wrapper">
    <div class="bst-content">

    <div class="bst-page-bar mrgn-b-md breadcrumb-double-arrow">
    <ul class="breadcrumb">
    <li class="breadcrumb-item text-capitalize">
    <h3>WORKPANEL</h3> </li>
    <li class="breadcrumb-item"><a href="#/"><?= $module ?></a></li>
    </ul>
    </div>

    <!--MAIN CONTENT-->
    <form class="form-elments1" action="" method="post" enctype="multipart/form-data">
    <div class="form-style">
    <div class="row">

      	<!--INFO PARTIDO-->
        <div class="col-md-12 col-lg-6">
        <div class="bst-block">

        <div class="bst-block-title mrgn-b-lg">
            <h3 class="text-capitalize"><?= $module ?></h3>
        </div>

        <input type="hidden" id="actionFlag" name="actionFlag" value="<?= $actionFlag ?>" />
        <input type="hidden" id="form[Id]" name="form[GameFutureId]" value="<?= $arrData[GameFutureId] ?>" />

        <div class="select-box">
        <div class="selectbox-wrap mrgn-b-sm">
        <div class="selectbox">
            <select id="form[SeasonId]" name="form[SeasonId]" class="form-control" tabindex="1">
                <option value="">Seleccionar temporada</option>
                <?= writeOptionListSeason($arrData[SeasonId]); ?>
            </select>
        </div>
        </div>
        </div>

        <div class="select-box">
        <div class="selectbox-wrap mrgn-b-sm">
        <div class="selectbox">
            <select id="form[TournamentId]" name="form[TournamentId]" class="form-control" tabindex="2">
                <option value="">Seleccionar Torneo</option>
                <?= writeOptionListTournament($arrData[TournamentId]); ?>
            </select>
        </div>
        </div>
        </div>

        <div class="select-box">
        <div class="selectbox-wrap mrgn-b-sm">
        <div class="selectbox">
            <select id="form[Week]" name="form[Week]" class="form-control" tabindex="3">
                <option value="">Seleccionar Jornada</option>
                <?php
                for($i=1; $i<=22; $i++){
                    echo '<option value="' . $i . '"'  ;
                    if( $i == $arrData[Week] ) echo ' SELECTED ';
                    echo '>Jornada '.  $i .'</option>';
                }
                ?>
                <option value="51">Cuartos de Final Ida</option>
                <option value="52">Cuartos de Final Vuelta</option>
                <option value="53">Semi Final Ida</option>
                <option value="54">Semi Final Vuelta</option>
                <option value="55">Final Ida</option>
                <option value="56">Final Vuelta</option>
            </select>
        </div>
        </div>
        </div>



        <div class="form-group">
        <label for="inputDate2">Fecha del partido</label>
        <input type="date" id="form[Date]" name="form[Date]" class="form-control" tabindex="4" placeholder="--/--/----" value="<?= $arrData[Date] ?>" >
        </div>

        <div class="form-group">
        <label for="">Horario</label>
        <div class="selectbox-wrap mrgn-b-sm">

        <div>
        <div class="selectbox col-sm-4" style="padding-left: 0px; margin-bottom: 15px;">
            <select id="form[GameHour]" name="form[GameHour]" class="form-control" tabindex="5">
                <?php
                for ($i=1;$i<=12;$i++)
                    echo '
                <option value="' . substr("0$i", -2) . '" ' . ($i == $arrData[GameHour] ? 'SELECTED' : '') . '>' . substr("0$i", -2) . '</option>';
                ?>
            </select>
        </div>
        <div class="selectbox col-sm-4" style="margin-bottom: 15px;">
            <select id="form[GameMinute]" name="form[GameMinute]" class="form-control" tabindex="6">
                <?php
                for ($i=0;$i<=59;$i++)
                    echo '
                <option value="' . substr("0$i", -2) . '" ' . ($i == $arrData[GameMinute] ? 'SELECTED' : '') . '>' . substr("0$i", -2) . '</option>';
                ?>
            </select>
        </div>
        <div class="selectbox col-sm-4" style="margin-bottom: 15px;">
            <select id="form[GameAMPM]" name="form[GameAMPM]" class="form-control" tabindex="7">
                <option value="AM" <?= $arrData[GameAMPM] == 'AM' ? 'SELECTED' : '' ?> >AM</option>
                <option value="PM" <?= $arrData[GameAMPM] == 'PM' ? 'SELECTED' : '' ?> >PM</option>
            </select>
        </div>
        </div>

        </div>
		</div>




        <div class="form-group" style="margin-top:15px;">
        <label for="form[Title]">Titulo</label>
        <input type="text" id="form[Title]" name="form[Title]"  class="form-control" maxlength="300" tabindex="8" placeholder="Titulo" value="<?= $arrData[Title] ?>">
        </div>

        <div class="form-group">
        <label for="form[Title]">Subtitulo</label>
        <input type="text" id="form[Subtitle]" name="form[Subtitle]"  class="form-control"  maxlength="300" tabindex="9" placeholder="Subtitulo" value="<?= $arrData[Subtitle] ?>">
        </div>

        <div class="form-group">
        <label for="form[Description]">Descripción</label>
        <textarea id="form[Description]" name="form[Description]" class="form-control" rows="3" tabindex="10"><?= fstrhsec($arrData[Description]) ?></textarea>
        </div>

        <div class="form-group">
        <label for="form[VersusTeam]">Nombre del rival</label>
        <input type="text" id="form[VersusTeam]" name="form[VersusTeam]"  class="form-control" maxlength="100" tabindex="11" placeholder="" value="<?= $arrData[VersusTeam] ?>">
        </div>

        <div class="select-box">
        <label for="">¿El rival se encuentra en casa o visitante?</label>
        <div class="selectbox-wrap mrgn-b-sm">
        <div class="selectbox">
            <select id="form[VersusTeamAtHome]" name="form[VersusTeamAtHome]" class="form-control" tabindex="12">
                <option value="">Seleccionar...</option>
                <option value="0" <?= intval($arrData[VersusTeamAtHome]) == 0 ? 'SELECTED' : '' ?> >Visita</option>
                <option value="1" <?= intval($arrData[VersusTeamAtHome]) == 1 ? 'SELECTED' : '' ?> >Casa</option>
            </select>
        </div>
        </div>
        </div>

        <div class="form-group">
        <label for="form[Stadium]">Estadio</label>
        <input type="text" id="form[Stadium]" name="form[Stadium]"  class="form-control" maxlength="50" tabindex="13" placeholder="" value="<?= $arrData[Stadium] ?>">
        </div>

        <div class="form-group">
        <label for="form[City]">Ciudad</label>
        <input type="text" id="form[City]" name="form[City]"  class="form-control" maxlength="50" tabindex="14" placeholder="" value="<?= $arrData[City] ?>">
        </div>

        <div class="form-group">
        <label for="form[Address]">Direccion</label>
        <input type="text" id="form[Address]" name="form[Address]"  class="form-control" maxlength="100" tabindex="15" placeholder="" value="<?= $arrData[Address] ?>">
        </div>

        <div class="form-group">
        <label for="form[LinkAddress1]">Liga del Video</label>
        <input type="text" id="form[LinkAddress1]" name="form[LinkAddress1]"  class="form-control" maxlength="300" tabindex="16" placeholder="http..." value="<?= $arrData[LinkAddress1] ?>">
        </div>

        <?php
        $tabindex = 17;
        for($i=0; $i<=6; $i++){

            if ($i == 0) $label = 'Logo Equipo (png)';
            if ($i == 1) $label = 'Banner Publicitario (jpg)';
            if ($i >= 2) $label = 'Imagen para galería / ' . ($i-1) . ' (jpg)';

            echo '
        <div class="bst-block-content mrgn-b-sm">
        <label for="">' . $label . '</label>
        <div class="fileinput fileinput-new input-group mrgn-b-sm" data-provides="fileinput">
        <div class="form-control" data-trigger="fileinput"> <i class="glyphicon glyphicon-open-file"></i> <span class="fileinput-filename"></span> </div>
        <div class="input-group-addon btn-file">
            <span class="fileinput-new">Seleccionar archivo</span>
            <span class="fileinput-exists">Cambiar</span>
            <input type="file" name="fileUpload' . $i . '" tabindex="' . $tabindex . '">
        </div>
        <a href="#" class="input-group-addon fileinput-exists" data-dismiss="fileinput">Quitar</a>
        </div>
        </div>';
            $tabindex++;
        }
        ?>


        <div class="form-group">
        <label for="form[Active]">Activo</label>
        <input type="checkbox" id="form[Active]" name="form[Active]" style="padding-top:5px;" tabindex="23" value="1" <?= $arrData[Active] == 1 ? "CHECKED" : '' ?> />
        </div>

        <button type="button" class="btn btn-lg btn-gray" onclick="javascript:insertData()" tabindex="25">Nuevo</button>
        <input id="button" type="submit" class="btn btn-lg btn-success" tabindex="24" value="<?= $buttonCaption ?>" />

        <div class="form-group">
        <?php
            if ($inputWarning)
                echo '<br/>Favor de capturar los siguientes campos:<br><strong>' . $inputWarning . '</strong>';

            if ($returnMessage)
                echo "<br/>$returnMessage";
            ?>
        </div>

        </div>
        </div>
        <!--INFO PARTIDO-->


        <!--HISTORIAL-->
        <div class="col-md-12 col-lg-6">
        <div class="bst-full-block">
        <div class="pad-all-lg">
        <div class="bst-block-title">
        <div class="caption">
        <h3 class="text-capitalize">Historial</h3> </div>
        </div>
        </div>
        <div class="bst-block-content">

            <div class="form-group">
                <div class="input-group">
                <input type="text" id="form[SearchKey]" class="form-control" onkeypress="javascript:if(event.keyCode == 13){searchByKey(this.value);return false;}" value="<?= $arrData[Key] ?>" />
                <span class="input-group-btn">
                <button type="button" onclick="javascript:searchByKey(document.getElementById('form[SearchKey]').value);" class="btn btn-lg btn-success">Buscar</button>
                </span>
                </div>
            </div>

            <div id="divRecords" class="table-responsive">
                <script>getDataReturnText('partido-futuronav.php?<?= encodeString('p1=' . $formAccess . '&p2=' .  $recordsPerPage . '&p3=' . $arrData[Page] . '&p4=' . $arrData[Key] )  ?>', 'divRecords'); </script>
            </div>

        </div>
        </div>
        </div>
        <!--HISTORIAL-->

    </div>
    </div>
    </form>
    <!--MAIN CONTENT-->

    </div>
    </div>

    <!--CONTENT-->

    </div>
    </div>

    <!--MAIN-->

    <!--FOOTER-->
    <?php include("footer.php"); ?>
    <!--FOOTER-->


</div>
<!--SCRIPTS-->
<script type="text/javascript" src="assets/js/loader.js"></script>
<script src="assets/js/vendor.js" type="text/javascript"></script>
<script src="assets/js/plugins.js" type="text/javascript"></script>
<script src="assets/plugin/jvmap/js/jquery-jvectormap-continents-mill.js" type="text/javascript"></script>
<script src="assets/plugin/full-calendar/js/fullcalendar.min.js" type="text/javascript"></script>
<script src="assets/js/manager.js" type="text/javascript"></script>
<script src="assets/js/lang-all.js" type="text/javascript"></script>

<!--CALENDAR-->
<script>

    $(document).ready(function() {
        if ($('.dashboard-calendar').length > 0) {
            var todayDate = moment().startOf('day');
            var YM = todayDate.format('YYYY-MM');
            var YESTERDAY = todayDate.clone().subtract(1, 'day').format('YYYY-MM-DD');
            var TODAY = todayDate.format('YYYY-MM-DD');
            var TOMORROW = todayDate.clone().add(1, 'day').format('YYYY-MM-DD');
            var initialLocaleCode = 'en';
            var events = [{
                title: 'Vs Dor',
                start: YM + '-01',
                backgroundColor: '#12ec60'
            }, {
                title: 'Vs Lob',
                start: YM + '-08',
                backgroundColor: '#f42829'
            }, {
                title: 'Vs Atl',
                start: YM + '-24',
                backgroundColor: '#12ec60'
            }, {
                title: 'Vs Chi',
                start: YM + '-26',
                backgroundColor: '#f42829'
            }];
            $('.dashboard-calendar').fullCalendar({
                locale: 'es',
                editable: false,
                eventLimit: false,
                navLinks: false,
                events: events,
                header: {
                    left: 'prev,next today',
                    center: '',
                    right: 'title'
                }
            });
        }
    });
</script>



</body>
</html>