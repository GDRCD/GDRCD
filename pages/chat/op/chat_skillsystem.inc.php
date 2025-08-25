<?php

/**
 * Questo file gestisce l'esecuzione di tutta la parte statistica
 * riguardante lo skillsystem di GDRCD, in particolare:
 *  - Lancio dado
 *  - Lancio su Caratteristica
 *  - Lancio su Abilità
 *  - Utilizzo Oggetti
 */

// Garantisce che che questo file sia utilizzato unicamente da /pages/chat/ajax.php
gdrcd_chat_module_allowed('chat');

// Valore tendina "Selezione Tiro"
$selezione_tiro = $_POST['id_selection'] ?? '';

switch ($selezione_tiro) {

    // Tiro Abilità
    case 'skills':
        // Dati in input dal form correlato
        $id_ab = $_POST['id_ab'] ?? '';

        // Chiama la funzione responsabile per effettuare la tipologia di tiro
        $output = gdrcd_chat_use_skill($id_ab);
        break;

    // Tiro Caratteristica
    case 'stats':
        // Dati in input dal form correlato
        $id_stats = $_POST['id_stats'] ?? '';

        // Chiama la funzione responsabile per effettuare la tipologia di tiro
        $output = gdrcd_chat_use_stats($id_stats);
        break;

    // Tiro Dado
    case 'dice':
        // Dati in input dal form correlato
        $dice = $_POST['dice'] ?? '';
        $dice_number = $_POST['dice_number'] ?? '';
        $dice_modifier = $_POST['dice_modifier'] ?? '';
        $dice_threshold = $_POST['dice_threshold'] ?? '';

        // Chiama la funzione responsabile per effettuare la tipologia di tiro
        $output = gdrcd_chat_use_dice(
            $dice,
            $dice_number,
            $dice_modifier,
            $dice_threshold
        );
        break;

    // Uso oggetti in chat
    case 'items':
        // Dati in input dal form correlato
        $id_item = $_POST['id_item'] ?? '';

        // Chiama la funzione responsabile per effettuare la tipologia di tiro
        $output = gdrcd_chat_use_item($id_item);
        break;

    // Tipologia sconosciuta o non selezionata
    default:
        $output = gdrcd_chat_status_invalid($MESSAGE['chat']['error']['invalid_skillsystem_type']);
        break;
}

// Ritorna una risposta al browser formattata in base all'esito dell'operazione eseguita
gdrcd_chat_output($output);
