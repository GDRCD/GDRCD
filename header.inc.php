<?php

header('Content-Type:text/html; charset=UTF-8');

//Includo i parametri, la configurazione, la lingua e le funzioni
require(__DIR__ . '/core/required.php');

# Controllo del login
if ( !empty($_SESSION['login']) ) {
    $me = gdrcd_filter('in', $_SESSION['login']);
    $check = gdrcd_query("SELECT count(nome) as TOT FROM personaggio WHERE ora_entrata > ora_uscita AND nome='{$me}' LIMIT 1");

    if ( $check['TOT'] == 0 ) {
        session_destroy();
        die('Non sei collegato con nessun pg.');
    }

    // Refresh ultimo_refresh
    $me_id = Functions::getInstance()->getMyId();
    DB::query("UPDATE personaggio SET ultimo_refresh = NOW() WHERE  id='{$me_id}' LIMIT 1");
}

// Cronjob
if ( Cronjob::getInstance()->inlineCronjob() ) {
    Cronjob::getInstance()->startCron();
}

/** * CONTROLLO PER AGGIORNAMENTO DB
 * Il controllo viene lanciato solo in index e nelle pagine di installer/upgrade.
 * Dopo l'aggiornamento non dovrebbe dare noie.
 * Nel qual caso vogliate risparmiare risorse quando si visita la homepage però è possibile modificare la variabile $check_for_update in index.php e settarla a FALSE.
 * @author Blancks
 */
if ( isset($check_for_update) && $check_for_update ) {
    include('upgrade_details.php');
}
/** * Fine controllo di update */

/**    * Caricamento plugins.
 * I plugins non sono vitali all'esecuzione dell'engine, per cui si includono col comando include.
 * @author Blancks
 */

/* Caricamento bbdecoder */
if ( ($PARAMETERS['mode']['user_bbcode'] == 'ON' && $PARAMETERS['settings']['user_bbcode']['type'] == 'bbd') || $PARAMETERS['settings']['forum_bbcode']['type'] == 'bbd' ) {
    include('plugins/bbdecoder/bbdecoder.php');
}

?>
    <!--Force IE6 into quirks mode with this comment tag-->
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
    <head>
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
              integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p"
              crossorigin="anonymous"/>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <!-- IE9: mi stai ampiamente rompendo i maroni. -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <link rel="shortcut icon" href="favicon.png" type="image/png"/>
        <link rel="stylesheet" href="<?= Router::getCssLink('main.css'); ?>"
              type="text/css"/>
        <link rel="stylesheet" href="<?= Router::getCssLink('chat.css'); ?>"
              type="text/css"/>
        <link rel="stylesheet" href="<?= Router::getCssLink('scheda.css'); ?>"
              type="text/css"/>
        <link rel="stylesheet" href="<?= Router::getCssLink('messaggi.css'); ?>"
              type="text/css"/>
        <link rel="stylesheet" href="<?= Router::getCssLink('forum.css'); ?>"
              type="text/css"/>
        <link rel="stylesheet" href="<?= Router::getCssLink('presenti.css'); ?>"
              type="text/css"/>
        <link rel="stylesheet" href="<?= Router::getCssLink('calendario.css'); ?>"
              type="text/css"/>

        <!-- JQUERY -->
        <link rel="stylesheet" href="plugins/Jquery/jquery.min.css"/>
        <script src="plugins/Jquery/jquery.min.js"></script>
        <script src="plugins/Jquery/jquery-ui.min.js"></script>

        <!-- Chosen 1.8.7 -->
        <script src="plugins/Chosen/chosen.jquery.min.js"></script>
        <link rel="stylesheet" href="plugins/Chosen/chosen.min.css"/>

        <!-- SweetAlert v2.11 -->
        <script src="plugins/Swal2/dist/sweetalert2.all.min.js"></script>
        <link rel="stylesheet" href="plugins/Swal2/dist/sweetalert2.min.css"/>

        <!-- FullCalendar v6.0.2 -->
        <script src="/plugins/moment/min/moment-with-locales.js"></script>
        <script src="plugins/FullCalendar/dist/index.global.min.js"></script>
        <script src="plugins/FullCalendar/packages/daygrid/index.global.min.js"></script>

        <!-- Popper v2 -->
        <script src="plugins/Tippy/popper.min.js"></script>

        <!-- Tippy v6.3.7 -->
        <script src="plugins/Tippy/tippy.min.js"></script>

        <!-- CUSTOM PLUGINS -->
        <script src="plugins/Ajax.js"></script>
        <script src="plugins/Form.js"></script>
        <script src="plugins/Menu.js"></script>
        <script src="plugins/FakeTable.js"></script>
        <script src="plugins/modal.js"></script>

        <!-- CUSTOM WRAPPERS -->
        <script src="plugins/Swal.js"></script>
        <script src="plugins/Chosen.js"></script>
        <script src="plugins/FullCalendar.js"></script>
        <script src="plugins/Tippy.js"></script>


        <?php

        ?>
    </head>
    <body class="main_body">
<?php
/** * CONTROLLO PER AGGIORNAMENTO DB
 * Il controllo viene lanciato solo in index e nelle pagine di installer/upgrade.
 * Dopo l'aggiornamento non dovrebbe dare noie.
 * Nel qual caso vogliate risparmiare risorse quando si visita la homepage però è possibile modificare la variabile $check_for_update in index.php e settarla a FALSE.
 * @author Blancks
 */
if ( (($table == 0) && isset($dont_check) && !$dont_check) && isset($check_for_update) && $check_for_update ) {
    echo '<div class="error">', $MESSAGE['error']['db_empty'], '</div>', '<div class="link_back"><a href="installer.php">', Filters::out($MESSAGE['installer']['instal']), '</a></div>', '</body></html>';
    exit();

} else if ( (isset($updating_queryes[0]) && !empty($updating_queryes[0]) && !$dont_check) && isset($check_for_update) && $check_for_update ) {
    echo '<div class="error">', $MESSAGE['error']['db_not_updated'], '</div>';

    if ( $updating_password ) {
        echo '<div class="error">', $MESSAGE['warning']['pass_not_encripted'], '</div>';
    }

    echo '<div class="link_back"><a href="upgrade.php">', Filters::out($MESSAGE['homepage']['updater']['update']), '</a></div>', '</body></html>';

    exit();
}

$online_status = OnlineStatus::getInstance();
if ( $online_status->isEnabled() ) {

    if ( $online_status->onlineStatusNeedRefresh() ) {
        Router::loadPages('online_status/choose_status.php');
    }
}

?>