<?php
//Includo i parametri, la configurazione, la lingua e le funzioni
require ('../../includes/required.php');

//Eseguo la connessione al database
$handleDBConnection = gdrcd_connect();$sqlQuery = "SELECT eventi.id,  start, end,eventi_tipo.title, eventi_colori.backgroundColor, eventi_colori.borderColor, eventi_colori.textColor  FROM eventi 
LEFT JOIN eventi_tipo ON eventi.title = eventi_tipo.id
LEFT  JOIN eventi_colori ON eventi.colore = eventi_colori.id ORDER BY id";
$result = gdrcd_query($sqlQuery, 'result');
$eventArray = array();
foreach ($result as $array){
    array_push($eventArray, $array);
}
echo json_encode($eventArray);
?>
