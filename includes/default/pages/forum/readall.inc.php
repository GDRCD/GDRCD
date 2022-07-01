<?php
$result = gdrcd_query("SELECT * FROM messaggioaraldo WHERE id_messaggio_padre = -1", 'result');

while($row = gdrcd_query($result, 'fetch')) {
    $esiste = gdrcd_query("SELECT id FROM araldo_letto WHERE thread_id = ".$row['id_messaggio']." AND nome = '".$_SESSION['login']."'");

    if($esiste['id'] <= 0) {
        gdrcd_query("INSERT INTO araldo_letto (nome, araldo_id, thread_id) VALUES ('".$_SESSION['login']."', ".$row['id_araldo'].", ".$row['id_messaggio'].")"
        );
    }
}