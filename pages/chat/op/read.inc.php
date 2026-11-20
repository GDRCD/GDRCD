<?php

/**
 * Questo file legge dal database i messaggi nuovi in chat rispetto al
 * precedente refresh dell'utente connesso al sito e li formatta in html
 * tramite la funzione gdrcd_chat_read_message() prima di ritornarli.
 */

// Garantisce che che questo file sia utilizzato unicamente da /pages/chat/ajax.php
gdrcd_module_allowed('chat');

// Variabili di input
$map_id = $_SESSION['luogo'];
$chat_last_id = gdrcd_chat_get_lastmessage_id();

header('X-Last-Id: '. $chat_last_id);

// Meteo corrente della chat, per aggiornare il box senza ricaricare la pagina.
// Il testo è già filtrato per l'html e viene codificato per stare in un header.
$meteo = gdrcd_meteo_corrente((int)$map_id, (int)($_SESSION['mappa'] ?? 0));
header('X-Meteo: '. rawurlencode($meteo['testo']));

// Legge le nuove azioni dal database
$output = gdrcd_chat_read_messages($map_id, $chat_last_id);

if (count($output['message']) > 0) {
    // Scrive in sessione l'ultimo id letto dal database, in questo modo al
    // prossimo refresh verranno caricati soltanto i messaggi con un id maggiore.
    gdrcd_chat_set_lastmessage_id(end($output['message'])['id']);
}

// Output delle azioni trovate
gdrcd_api_output($output);
