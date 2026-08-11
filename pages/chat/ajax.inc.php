<?php

/**
 * Tutte le richieste per le funzionalità della chat passano da questo file.
 * La specifica "op" richiesta viene poi smistata al file responsabile del suo funzionamento.
 */

// Controlla che l'utente sia abilitato ad accedere alla chat
if ( !gdrcd_chat_room_is_login_allowed($_SESSION['luogo']) ) {
    gdrcd_api_output(
        gdrcd_api_status_forbidden($MESSAGE['chat']['whisper']['privat'])
    );
    die();
}

// Recupera la "op" passata nella url
$operation = $_GET['op'] ?? '';

// Smista l'operazione richiesta al file responsabile del suo funzionamento.
switch ($operation) {

    // La lista di seguito indica quali "op" sono disponibili.
    // Se ne servono di nuovi basterà creare il file nella
    // cartella /pages/chat/op/ e specificarlo qui di seguito.

    case 'read':           // Legge i messaggi nuovi in chat
    case 'write':          // Scrive un nuovo messaggio in chat
    case 'skillsystem':    // Esegue le operazioni relative allo skillsystem (dadi, abilità, caratteristiche e uso oggetti)

        $operation_file = dirname(__FILE__) . '/op/'. $operation .'.inc.php';

        // Se il file di operazione non esiste ritorno un 501 al browser (Not Implemented)
        if (!file_exists($operation_file)) {
            gdrcd_api_output(gdrcd_api_status_notimplemented());
            die();
        }

        // "Sblocca" il funzionamento dei files collegati a "chat"
        gdrcd_module_enable('chat');

        // Esegue il file di operazione
        require $operation_file;
        die();

}

// L'operazione non è stata definita nello switch di prima: ritorno un 404 (Not Found)
gdrcd_api_output(gdrcd_api_status_notfound());
