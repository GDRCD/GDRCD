<?php

/**
 * Tutte le richieste ajax/xhr passano da questo file.
 * 
 * Questo file accetta un parametro "page" analogamente al main,
 * ma la differenza è che accetta unicamente valori che risolvono 
 * ad una cartella, all'interno della quale ci si aspetta l'esistenza
 * di un file "ajax.php" che si occupi di gestire la richiesta.
 */

// Include il core di GDRCD
require_once dirname(__FILE__) . '/includes/required.php';

// Controlla che l'utente sia connesso
gdrcd_controllo_sessione();

// Connette GDRCD al database
gdrcd_connect();

// Controlla che l'utente non sia esiliato
if( ($ban_message = gdrcd_controllo_esilio($_SESSION['login'], true)) ) {
    session_destroy();
    gdrcd_api_output(
        gdrcd_api_status_forbidden($ban_message)
    );
    die();
}

// Recupera la "page" passata nella url
$page = $_GET['page'] ?? '';

if ( $page ) {
    try {

        // Se il modulo viene caricato con successo lo script termina subito dopo
        gdrcd_load_modules($page .'/ajax', [], true);
        die();

    } catch (Throwable $e) {

        // In caso di errore nel caricare il modulo basta non fare nulla

    }
}

// Se si arriva fin qui: 404 Not Found
gdrcd_api_output(gdrcd_api_status_notfound());
