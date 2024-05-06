<?php

//Includio i parametri, la configurazione, la lingua e le funzioni
require ('includes/required.php');

$last_message = isset($_SESSION['last_message']) ? $_SESSION['last_message'] : 0;

if(!empty($_SESSION['theme']) and array_key_exists($_SESSION['theme'], $PARAMETERS['themes']['available'])){
    $PARAMETERS['themes']['current_theme'] = $_SESSION['theme'];
}

//Eseguo la connessione al database
$handleDBConnection = gdrcd_connect();
//Ricevo il tempo di reload
$i_ref_time = gdrcd_filter_get($_GET['ref']);

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <!--meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="refresh" content="<?php echo $i_ref_time; ?>">
    <link rel="stylesheet" href="../themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/presenti.css" TYPE="text/css">
    <link rel="stylesheet" href="../themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/main.css" TYPE="text/css">
    <link rel="stylesheet" href="../themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/chat.css" TYPE="text/css">
    <title>Chat</title>
</head>
<body class="transparent_body"  >