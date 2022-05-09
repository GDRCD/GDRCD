<?php

header('Content-Type:text/html; charset=UTF-8');

//Includo i parametri, la configurazione, la lingua e le funzioni
require_once('includes/required.php');

//Eseguo la connessione al database
$handleDBConnection = gdrcd_connect();

# Controllo del login
if(!empty($_SESSION['login'])){
    $me = gdrcd_filter('in',$_SESSION['login']);

    $table_check = gdrcd_query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '{$PARAMETERS['database']['database_name']}' AND TABLE_NAME = 'personaggio';");

    if(!empty($table_check)) {

        $check = gdrcd_query("SELECT count(nome) as TOT FROM personaggio WHERE ora_entrata > ora_uscita AND nome='{$me}' LIMIT 1");

        if ($check['TOT'] == 0) {
            session_destroy();
            die('Non sei collegato con nessun pg.');
        }
    }
    else{
        session_destroy();
    }

}

/** * CONTROLLO PER AGGIORNAMENTO DB
 * Il controllo viene lanciato solo in index e nelle pagine di installer/upgrade.
 * Dopo l'aggiornamento non dovrebbe dare noie.
 * Nel qual caso vogliate risparmiare risorse quando si visita la homepage però è possibile modificare la variabile $check_for_update in index.php e settarla a FALSE.
 * @author Blancks
 */
if(isset($check_for_update) && $check_for_update) {
    include('upgrade_details.php');
}
/** * Fine controllo di update */

/**    * Caricamento plugins.
 * I plugins non sono vitali all'esecuzione dell'engine, per cui si includono col comando include.
 * @author Blancks
 */

/* Caricamento bbdecoder */
if(($PARAMETERS['mode']['user_bbcode'] == 'ON' && $PARAMETERS['settings']['user_bbcode']['type'] == 'bbd') || $PARAMETERS['settings']['forum_bbcode']['type'] == 'bbd') {
    include('plugins/bbdecoder/bbdecoder.php');
}

?>
<!--Force IE6 into quirks mode with this comment tag-->
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- IE9: mi stai ampiamente rompendo i maroni. -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="imgs/favicon.ico" type="image/png" />
    <link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/main.css" type="text/css" />
    <link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/chat.css" type="text/css" />
    <link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/presenti.css" type="text/css" />
    <link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/scheda.css" type="text/css" />
    <link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/messaggi.css" type="text/css" />
    <link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/forum.css" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <?php
    /** * Il controllo individua se l'header non è impiegato per il main */
    if( ! isset($check_for_update)) {
        ?>
        <link rel="stylesheet" href="layouts/<?php echo $PARAMETERS['themes']['kind_of_layout'], '_frames.php?css=true'; ?>" type="text/css" />
        <?php
    }
    ?>
    <title>
        <?php echo $PARAMETERS['info']['site_name']; ?>
    </title>
    <?php
    /** * Refresh fix, crossbrowser
     * @author Blancks
     */
    if( ! empty($_GET['ref'])) {
        //
    }
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
if((($table == 0) && isset($dont_check) && ! $dont_check) && isset($check_for_update) && $check_for_update) {
    echo '<div class="error">', $MESSAGE['error']['db_empty'], '</div>', '<div class="link_back"><a href="installer.php">', gdrcd_filter_out($MESSAGE['installer']['instal']), '</a></div>', '</body></html>';
    exit();

} elseif((isset($updating_queryes[0]) && ! empty($updating_queryes[0]) && ! $dont_check) && isset($check_for_update) && $check_for_update) {
    echo '<div class="error">', $MESSAGE['error']['db_not_updated'], '</div>';

    if($updating_password) {
        echo '<div class="error">', $MESSAGE['warning']['pass_not_encripted'], '</div>';
    }

    echo '<div class="link_back"><a href="upgrade.php">', gdrcd_filter_out($MESSAGE['homepage']['updater']['update']), '</a></div>', '</body></html>';

    exit();
}
?>