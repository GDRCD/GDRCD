<?php

/**
 * Questo file legge dal database i messaggi nuovi in chat rispetto al
 * precedente refresh dell'utente connesso al sito e li formatta in html
 * tramite la funzione gdrcd_chat_read_message() prima di ritornarli.
 */

// Garantisce che che questo file sia utilizzato unicamente da /pages/chat/ajax.php
gdrcd_chat_op_require_enabled();

// Variabili di input
$map_id = $_SESSION['luogo'];
$chat_last_id = gdrcd_chat_get_lastmessage_id();

// Query per recuperare i messaggi non ancora mostrati in chat per l'utente connesso
$query_azioni = gdrcd_stmt(
    "SELECT
        chat.id,
        chat.imgs,
        chat.mittente,
        chat.destinatario,
        chat.tipo,
        chat.ora,
        chat.testo,
        personaggio.url_img_chat

    FROM chat
        INNER JOIN mappa ON mappa.id = chat.stanza
        LEFT JOIN personaggio ON personaggio.nome = chat.mittente

    WHERE stanza = ?
        AND chat.id > ?
        AND chat.ora > IFNULL(mappa.ora_prenotazione, '0000-00-00 00:00:00')
        AND DATE_SUB(NOW(), INTERVAL 4 HOUR) < ora

    ORDER BY id ASC",
    [
        'ii',
        $map_id,
        $chat_last_id
    ]
);

// Contenitore per tutte le nuove azioni formattate in html
$azioni = [];

if (gdrcd_query($query_azioni, 'num_rows') > 0) {

    while ($riga_azione = gdrcd_query($query_azioni, 'fetch')) {

        // aggiorna $chat_last_id con l'ultimo id letto
        $chat_last_id = $riga_azione['id'];

        // formatta l'azione da inviare in chat
        $azione = gdrcd_chat_read_message($riga_azione);

        // controlla se $azione non sia vuoto (o null) prima di aggiungerla alle azioni da mostrare in chat
        if (!empty($azione)) {
            $azioni[] = $azione;
        }

    }

    gdrcd_query($query_azioni, 'free');

    // Scrive in sessione l'ultimo id letto dal database, in questo modo al
    // prossimo refresh verranno caricati soltanto i messaggi con un id maggiore.
    gdrcd_chat_set_lastmessage_id($chat_last_id);

}

// Output delle azioni trovate
header('Content-type: application/json;charset=utf-8');
echo json_encode($azioni);
