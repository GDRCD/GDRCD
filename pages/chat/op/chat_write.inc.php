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

// Converte lo stato ritornato da gdrcd_chat_write_message
// in un codice di risposta http appropriato da inviare al browser
$http_status = gdrcd_chat_status_to_http_code($chat_skillsystem_status);

// Invia in uscita il codice di stato http
http_response_code($http_status);
