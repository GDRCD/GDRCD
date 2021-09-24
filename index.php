<?php
$dont_check = true;
$check_for_update = false;

require_once 'config.inc.php';

if ($PARAMETERS['settings']['protection'] == 'ON'){
    require 'protezione.php';
}

require 'header.inc.php';
require 'includes/credits.inc.php';

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
$page = ( ! empty($_GET['page'])) ? gdrcd_filter('include', $_GET['page']) : 'index';

/*
 * Definizione dell'eventuale contenuto interno
 * Utile se si vuol mantenere la struttura della homepage quando si aprono i link
 */
$content = ( ! empty($_GET['content'])) ? gdrcd_filter('include', $_GET['content']) : 'home';

/*
 * Conteggio utenti online
 */
$users = gdrcd_query("SELECT COUNT(nome) AS online FROM personaggio WHERE ora_entrata > ora_uscita AND DATE_ADD(ultimo_refresh, INTERVAL 4 MINUTE) > NOW()");

/*
 * Procedura di recupero Password
 */
$RP_response = '';

if ( ! empty($_POST['email'])) {

    $result = gdrcd_query("SELECT nome, email FROM personaggio", 'result');
    $success = false;
    while($row = gdrcd_query($result, 'fetch')) {
        if (gdrcd_password_check($_POST['email'], $row['email'])) {
            gdrcd_query($result, 'free');
            $pass = gdrcd_genera_pass();
            gdrcd_query("UPDATE personaggio SET pass = '" . gdrcd_encript($pass) . "' WHERE nome = '" .$row['nome']. "' LIMIT 1");

            $subject = gdrcd_filter('out',$MESSAGE['register']['forms']['mail']['sub'] . ' ' . $PARAMETERS['info']['site_name']);
            $text = gdrcd_filter('out', $MESSAGE['register']['forms']['mail']['text'] . ': ' . $pass);

            mail($_POST['email'], $subject, $text, 'From: ' . $PARAMETERS['info']['webmaster_email']);

            $RP_response = gdrcd_filter('out', $MESSAGE['warning']['modified']);

            $success = true;
        }
    }
    if ($success === false) {
        $RP_response = gdrcd_filter('out', $MESSAGE['warning']['cant_do']);
    }

}
/*
 * Fine Recupero Password
 */

include 'themes/' . $PARAMETERS['themes']['current_theme'] . '/home/' . $page . '.php';

require 'footer.inc.php';
?>
