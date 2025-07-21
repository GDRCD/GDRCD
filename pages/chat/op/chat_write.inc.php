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

// Converte il codice di esito della funzione gdrcd_chat_write_message()
// in un codice di stato http idoneo da comunicare in risposta
$http_status = match ($chat_insert_status) {
    1 => 201,   // Created: messaggio salvato correttamente
    0 => 400,   // Bad Request: i dati forniti non sono corretti e non è possibile salvare il messaggio nel db
    -1 => 403,  // Forbidden: azione non consentita perché non si dispone dei permessi necessari
};

// Invia in uscita il codice di stato http
http_response_code($http_status);
