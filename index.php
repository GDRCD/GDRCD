<?php
$dont_check = true;
$check_for_update = false;

require_once 'config.inc.php';

if ($PARAMETERS['settings']['protection'] == 'ON'){
    require 'protezione.php';
}

require('header.inc.php'); /*Header comune*/

/*
 * Fix per installare il database la prima volta.
 */
$record = gdrcd_query("SELECT COUNT(*) AS number FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '".$PARAMETERS['database']['database_name']."'");
if($record['number'] == 0 ) {
    gdrcd_redirect("installer.php");
}

/*
 * Definizione pagina da visualizzare
 */
$page = ( !empty($_GET['page']) && $_GET['page'] != 'homepage' ) ? 'homepage__' . gdrcd_filter('include', $_GET['page'])  : 'homepage';

/*
 * Definizione dell'eventuale contenuto interno
 * Utile se si vuol mantenere la struttura della homepage quando si aprono i link
 */
$content = ( ! empty($_GET['content'])) ? gdrcd_filter('include', $_GET['content']) : 'home';

// Includo la pagina
gdrcd_load_modules($page, ['content' => $content]);

require 'footer.inc.php';
