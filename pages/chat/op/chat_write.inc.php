<?php

/**
 * Questo file gestisce il salvataggio nel database di tutte le tipologie
 * di azione che l'utente può inviare scrivendo nell'input di testo in chat.
 */

// Garantisce che che questo file sia utilizzato unicamente da /pages/chat/ajax.php
gdrcd_chat_op_require_enabled();

// Dati in input inviati dal giocatore
$message = trim($_POST['message']);
$tag_o_destinatario = $_POST['tag'] ?? '';
$type = $_POST['type'] ?? null;

// Prova a salvare l'azione nel database
$chat_insert_status = gdrcd_chat_write_message(
    $message,
    $tag_o_destinatario,
    $type
);

// Ritorna una risposta al browser formattata in base all'esito dell'operazione precedente
gdrcd_chat_status_output($chat_insert_status);
