<?php require(__DIR__.'/../../../includes/required.php'); /*Header comune*/
$gathering = Gathering::getInstance();
$gathering_azioni= GatheringAzioni::getInstance();
//Ricevo il tempo di reload
$i_ref_time = Filters::get($_GET['ref']);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <!--meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="refresh" content="<?php echo $i_ref_time; ?>">
    <link rel="stylesheet" href="../../../themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/presenti.css" TYPE="text/css">
    <link rel="stylesheet" href="../../../themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/main.css" TYPE="text/css">
    <link rel="stylesheet" href="../../../themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/chat.css" TYPE="text/css">
    <title>Messaggi</title>
</head>
<body class="transparent_body">
<div class="box_messages">
<?php

 if((!$gathering->gatheringType()) && ($gathering->gatheringRandom())){


     echo $gathering_azioni->checkActionTime();






 }
?>


</div>

