<?php session_start();

header('Content-Type:text/html; charset=UTF-8');
$last_message = $_SESSION['last_message'];

//Includio i parametri, la configurazione, la lingua e le funzioni
require 'includes/constant_values.inc.php';
require 'config.inc.php';
require 'vocabulary/'.$PARAMETERS['languages']['set'].'.vocabulary.php';
require 'includes/functions.inc.php';
require 'includes/function_chat.inc.php';

//Eseguo la connessione al database
$handleDBConnection = gdrcd_connect();
//Ricevo il tempo di reload
$i_ref_time = gdrcd_filter_get($_GET['ref']);


?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">


<link rel="stylesheet" href="../themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/presenti.css" TYPE="text/css">
<link rel="stylesheet" href="../themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/main.css" TYPE="text/css">
<link rel="stylesheet" href="../themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/chat.css" TYPE="text/css">

</head>
<body class="transparent_body"  >