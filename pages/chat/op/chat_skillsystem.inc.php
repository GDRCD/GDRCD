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

// Dati in input inviati dal giocatore
$id_ab = $_POST['id_ab'] ?? '';
$id_stats = $_POST['id_stats'] ?? '';
$dice = $_POST['dice'] ?? '';
$id_item = $_POST['id_item'] ?? '';

// Prova ad eseguire l'azione richiesta
$chat_skillsystem_status = gdrcd_chat_use_skillsystem(
    $id_ab,
    $id_stats,
    $dice,
    $id_item
);

// Ritorna una risposta al browser formattata in base all'esito dell'operazione precedente
gdrcd_chat_output($chat_skillsystem_status);
