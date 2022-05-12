<?php

header('Content-Type:text/html; charset=UTF-8');

//Includo i parametri, la configurazione, la lingua e le funzioni
require_once('includes/required.php');

//Eseguo la connessione al database
$handleDBConnection = gdrcd_connect();

/**
 * Nel caso stessi utilizzando un sistema di protezione per il sito, prevedo il caricamento della pagina
 * @author Breaker
 */
if ($PARAMETERS['settings']['protection'] == 'ON') {
    require 'protezione.php';
}


if (DbMigrationEngine::dbNeedsInstallation()) {
    /*
     * Fix per installare il database la prima volta.
     */
    gdrcd_redirect("installer.php");
}

/*
 * Definizione pagina da visualizzare
 */
$page = (!empty($_GET['page']) && $_GET['page'] != 'homepage') ? 'homepage__' . gdrcd_filter('include', $_GET['page']) : 'homepage';

/*
 * Definizione dell'eventuale contenuto interno
 * Utile se si vuol mantenere la struttura della homepage quando si aprono i link
 */
$content = (!empty($_GET['content'])) ? gdrcd_filter('include', $_GET['content']) : 'home';


/**
 * Avvio la costruzione dei contenuti della pagina
 * @author Kasa
 */
?>
    <!--Force IE6 into quirks mode with this comment tag-->
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <!-- IE9: mi stai ampiamente rompendo i maroni. -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <link rel="shortcut icon" href="imgs/favicon.ico" type="image/png"/>
        <link rel="stylesheet" href="themes/homepage/<?= $PARAMETERS['themes']['homepage']; ?>/homepage.css"
              type="text/css"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css"/>
        <title>
            <?php echo $PARAMETERS['info']['site_name']; ?>
        </title>
    </head>
    <body class="main_body">
<?php

// Includo la pagina
gdrcd_load_modules($page, ['content' => $content]);

require 'footer.inc.php';
