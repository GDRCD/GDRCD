<?php

/**
 * Tutte le richieste per le funzionalità della chat passano da questo file.
 * La specifica "op" richiesta viene poi smistata al file responsabile del suo funzionamento.
 */

// Include il core di GDRCD
require_once dirname(__FILE__, 3) . '/includes/required.php';

// Controlla che l'utente sia connesso
gdrcd_controllo_sessione();

// Connette GDRCD al database
gdrcd_connect();

// Controlla che l'utente non sia esiliato
if(gdrcd_controllo_esilio($_SESSION['login'])) {
    session_destroy();
    die();
}

// Controlla che l'utente sia abilitato ad accedere alla chat
if ( !gdrcd_chat_is_accessible($_SESSION['luogo']) ) {
    http_response_code(403);
    die();
}

// "Sblocca" il funzionamento dei files collegati ai vari "op".
gdrcd_chat_op_set_enable();

// Recupera la "op" passata nella url
$operation = $_GET['op'];

// Smista l'operazione richiesta al file responsabile del suo funzionamento.
switch ($operation) {

    // La lista di seguito indica quali "op" sono disponibili.
    // Se ne servono di nuovi basterà creare il file nella
    // cartella /pages/chat/op/ e specificarlo qui di seguito.

    case 'chat_read':   // Legge i messaggi nuovi in chat
    case 'chat_write':  // Scrive un nuovo messaggio in chat
    case 'roll_dice':   // Tira un dado e scrive l'esito in chat
    case 'roll_skill':  // Tira un dado col valore dell'abilità e scrive l'esito in chat
    case 'roll_stats':  // Tira un dado col valore della caratteristica e scrive l'esito in chat
    case 'use_item':    // Utilizza un oggetto e scrive il responso in chat

        $operation_file = dirname(__FILE__) . '/op/'. $operation .'.inc.php';

        // Se il file di operazione non esiste ritorno un 501 al browser (Not Implemented)
        if (!file_exists($operation_file)) {
            http_response_code(501);
            die();
        }

        // Esegue il file di operazione
        require $operation_file;
        die();

}

// L'operazione non è stata definita nello switch di prima: ritorno un 404 (Not Found)
http_response_code(404);
