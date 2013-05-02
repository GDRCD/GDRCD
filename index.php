<?php 
$dont_check = FALSE;
$check_for_update = TRUE;

require 'header.inc.php';
require 'includes/credits.inc.php';



/** * Definizione pagina da visualizzare */
if (!empty($_GET['page']))
		$page = gdrcd_filter('include',$_GET['page']);
else
		$page = 'index';
		
		
/** * Definizione dell'eventuale contenuto interno
	* Utile se si vuol mantenere la struttura della homepage quando si aprono i link
*/
if (!empty($_GET['content']))
		$content = gdrcd_filter('include',$_GET['content']);
else
		$content = 'home';


/** * Algoritmi di base della homepage
*/

	/** * Conteggio utenti online
	*/
	$users = gdrcd_query("SELECT COUNT(nome) AS online FROM personaggio WHERE ora_entrata > ora_uscita AND DATE_ADD(ultimo_refresh, INTERVAL 4 MINUTE) > NOW()");
	
	
	/** * Procedura di recupero Password
	*/
	$RP_response = '';

	if(!empty($_POST['email']))
	{ 

		$newpass = gdrcd_query("SELECT email FROM personaggio WHERE email = '".gdrcd_filter('in',$_POST['email'])."' LIMIT 1", 'result');

		if (gdrcd_query($newpass, 'num_rows') > 0)
		{
			gdrcd_query($newpass, 'free');
	
			$pass = gdrcd_genera_pass();
			gdrcd_query("UPDATE personaggio SET pass = '".gdrcd_encript($pass)."' WHERE email = '".gdrcd_filter('in',$_POST['email'])."' LIMIT 1");
	   
			$subject = gdrcd_filter('out',$MESSAGE['register']['forms']['mail']['sub'].' '.$PARAMETERS['info']['site_name']);
			$text	= gdrcd_filter('out',$MESSAGE['register']['forms']['mail']['text'].': '.$pass);
	   
			mail($_POST['email'], $subject, $text, 'From: '.$PARAMETERS['info']['webmaster_email']);
		
		
			$RP_response = gdrcd_filter('out',$MESSAGE['warning']['modified']);
		
		}else
		{
			$RP_response = gdrcd_filter('out',$MESSAGE['warning']['cant_do']);
		}		
	
	}
	/** * Fine Recupero Password */



include 'themes/'. $PARAMETERS['themes']['current_theme'] .'/home/' . $page . '.php';



require 'footer.inc.php'; 
?>