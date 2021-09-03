<?php

/**
 * Recupero tutti i topic del Forum non letti dall'utente
 */
$result = gdrcd_query(
    "SELECT messaggioaraldo.id_araldo, messaggioaraldo.id_messaggio
          FROM messaggioaraldo
          LEFT JOIN araldo_letto ON ( messaggioaraldo.id_messaggio = araldo_letto.thread_id AND nome = 'Kasui')
          WHERE messaggioaraldo.id_messaggio_padre = -1
            AND araldo_letto.id IS NULL;",
    'result');

// Segno come letti i topic individuati
while($row = gdrcd_query($result, 'fetch')) {
    gdrcd_query("INSERT INTO araldo_letto (nome, araldo_id, thread_id) VALUES ('".$_SESSION['login']."', ".$row['id_araldo'].", ".$row['id_messaggio'].")");
}