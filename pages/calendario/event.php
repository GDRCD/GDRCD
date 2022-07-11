<?php
//Includo i parametri, la configurazione, la lingua e le funzioni
require ('../../includes/required.php');

//Eseguo la connessione al database
$handleDBConnection = gdrcd_connect();$sqlQuery = "SELECT id,  start, end,title, backgroundColor, borderColor, textColor  FROM eventi ORDER BY id";
$result = gdrcd_query($sqlQuery, 'result');
$eventArray = array();
foreach ($result as $array){
    array_push($eventArray, $array);
}
echo json_encode($eventArray);
?>
