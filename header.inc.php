<?php
session_start();
header('Content-Type:text/html; charset=UTF-8');


	/** * Se il personaggio è connesso avvio la gestione dei suoi spostamenti nella land
		* Il controllo va messo qui e non in main poichè in main risulterebbe trovarsi dopo l'inclusione del config
		* dando vita ad un bug sul tastino di aggiornamento della pagina corrente.
		
		* @author Blancks
	*/
	if (!empty($_SESSION['login']))
	{

		/** * Aggiornamento della posizione nella mappa del pg
			* @author Blancks
		*/
		if (isset($_REQUEST['map_id']) && is_numeric($_REQUEST['map_id']))
		{
			$_SESSION['luogo'] = -1;
			$_SESSION['mappa'] = $_REQUEST['map_id'];
		}
 
		if (isset($_REQUEST['dir']) && is_numeric($_REQUEST['dir']))
		{
			$_SESSION['luogo']=$_REQUEST['dir'];
		}

	}
	
	//Includo i parametri, la configurazione, la lingua e le funzioni 
	require 'includes/required.php';
   	
	//Eseguo la connessione al database
	$handleDBConnection = gdrcd_connect();

	/** * CONTROLLO PER AGGIORNAMENTO DB
		* Il controllo viene lanciato solo in index e nelle pagine di installer/upgrade.
		* Dopo l'aggiornamento non dovrebbe dare noie.
		* Nel qual caso vogliate risparmiare risorse quando si visita la homepage però è possibile modificare la variabile $check_for_update in index.php e settarla a FALSE.
		
		* @author Blancks
	*/
	if (isset($check_for_update) && $check_for_update)
	{
		include 'upgrade_details.php';
	}
    /** * Fine controllo di update */
    
    
    
    /**	* Caricamento plugins.
		* I plugins non sono vitali all'esecuzione dell'engine, per cui si includono col comando include.
		* @author Blancks
	*/
	
	/* Caricamento bbdecoder */
	if (($PARAMETERS['mode']['user_bbcode']=='ON' && $PARAMETERS['settings']['user_bbcode']['type']=='bbd') || $PARAMETERS['settings']['forum_bbcode']['type'] == 'bbd')
	{
		include 'plugins/bbdecoder/bbdecoder.php';
	}
    
?>
<!--Force IE6 into quirks mode with this comment tag-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- IE9: mi stai ampiamente rompendo i maroni. -->
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="shortcut icon" href="favicon.png" type="image/png" />
<link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/homepage.css" type="text/css" />
<link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/main.css" type="text/css" />
<link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/chat.css" type="text/css" />
<link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/presenti.css" type="text/css" />
<link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/scheda.css" type="text/css" />
<link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/messaggi.css" type="text/css" />
<link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/forum.css" type="text/css" />
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
<?php
	/** * Il controllo individua se l'header non è impiegato per il main */
	if (!isset($check_for_update))
	{
?>
<link rel="stylesheet" href="layouts/<?php echo $PARAMETERS['themes']['kind_of_layout'], '_frames.php?css=true';?>" type="text/css" />
<?php

	}
?>
<title><?php echo $PARAMETERS['info']['site_name']; ?></title>
<?php 
		/** * Refresh fix, crossbrowser
			* @author Blancks
		*/
		if (!empty($_GET['ref']))
		{
?>
<script type="text/javascript">setTimeout("self.location.href.reload();", <?php echo (int)$_GET['ref'] * 1000; ?>);</script>
<?php 	} 	?>
</head>
<body class="main_body">

<?php 

	 /** * CONTROLLO PER AGGIORNAMENTO DB
		* Il controllo viene lanciato solo in index e nelle pagine di installer/upgrade.
		* Dopo l'aggiornamento non dovrebbe dare noie.
		* Nel qual caso vogliate risparmiare risorse quando si visita la homepage però è possibile modificare la variabile $check_for_update in index.php e settarla a FALSE.
		
		* @author Blancks
	*/
	if ((($table == 0) && isset($dont_check) && !$dont_check) && isset($check_for_update) && $check_for_update)
	{
		echo 	'<div class="error">', $MESSAGE['error']['db_empty'], '</div>',
				'<div class="link_back"><a href="installer.php">', gdrcd_filter_out($MESSAGE['installer']['instal']), '</a></div>',
				'</body></html>';
				
		exit();	
	
	}elseif ((isset($updating_queryes[0]) && !empty($updating_queryes[0]) && !$dont_check) && isset($check_for_update) && $check_for_update)
	{
		echo '<div class="error">', $MESSAGE['error']['db_not_updated'], '</div>';
		
		if ($updating_password)
				echo '<div class="error">', $MESSAGE['warning']['pass_not_encripted'], '</div>';
		
		echo 	'<div class="link_back"><a href="upgrade.php">', gdrcd_filter_out($MESSAGE['homepage']['updater']['update']), '</a></div>',
				'</body></html>';
		
		exit();
	}


?>
