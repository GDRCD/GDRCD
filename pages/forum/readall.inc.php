<?php
$result = gdrcd_query("SELECT * FROM messaggioaraldo WHERE id_messaggio_padre = -1", 'result');

while($row = gdrcd_query($result, 'fetch')) {
    $esiste = gdrcd_query("SELECT id FROM araldo_letto WHERE thread_id = ".$row['id_messaggio']." AND id_personaggio = '".gdrcd_filter('in', $_SESSION['id_personaggio'])."'");

    if($esiste['id'] <= 0) {
        gdrcd_query("INSERT INTO araldo_letto (id_personaggio, araldo_id, thread_id) VALUES ('".gdrcd_filter('in', $_SESSION['id_personaggio'])."', ".$row['id_araldo'].", ".$row['id_messaggio'].")"
        );
    }
}