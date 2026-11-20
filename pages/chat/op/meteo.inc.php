<?php

/**
 * Questo file gestisce il meteo manuale impostato da un master dall'interno
 * della chat: può impostare o ripristinare il meteo della sola chat corrente
 * (override) oppure della zona a cui la chat appartiene.
 *
 * Dati in input (POST):
 *  - azione: 'imposta' oppure 'ripristina'
 *  - bersaglio: 'luogo' (solo questa chat) oppure 'zona' (tutta la zona)
 *  - temperatura, condizione, durata: solo per 'imposta'
 *
 * Non usare mai campi chiamati map_id o dir: config.core.inc.php sposta il
 * personaggio quando li riceve, anche nelle richieste ajax.
 */

// Garantisce che che questo file sia utilizzato unicamente da /pages/chat/ajax.php
gdrcd_module_allowed('chat');

// Solo i master possono modificare il meteo
if ((int)($_SESSION['permessi'] ?? 0) < METEO_PERM) {
    gdrcd_api_output(gdrcd_api_status_forbidden($MESSAGE['chat']['error']['permissions']));
    die();
}

// In modalità automatica il meteo manuale verrebbe ignorato
if (($PARAMETERS['mode']['auto_meteo'] ?? 'ON') == 'ON') {
    gdrcd_api_output(gdrcd_api_status_invalid($MESSAGE['interface']['meteo']['error']['auto_mode']));
    die();
}

// Dati in input inviati dal master: tutto ciò che non è una stringa viene scartato
$azione = $_POST['azione'] ?? null;
$bersaglio = $_POST['bersaglio'] ?? null;

if (!in_array($azione, ['imposta', 'ripristina'], true) || !in_array($bersaglio, ['luogo', 'zona'], true)) {
    gdrcd_api_output(gdrcd_api_status_invalid($MESSAGE['interface']['meteo']['error']['invalid_target']));
    die();
}

// La chat è quella in cui si trova il master, la zona viene letta dal database:
// non ci si fida né del client né di $_SESSION['mappa'], che può essere vecchia
$id_luogo = (int)($_SESSION['luogo'] ?? 0);
$luogo = $id_luogo > 0
    ? gdrcd_stmt_one('SELECT id_mappa FROM mappa WHERE id = ? LIMIT 1', [$id_luogo])
    : null;

if (empty($luogo)) {
    gdrcd_api_output(gdrcd_api_status_invalid($MESSAGE['error']['location_doesnt_exist']));
    die();
}

if ($bersaglio == 'zona') {
    $id_bersaglio = (int)$luogo['id_mappa'];
    $zona = $id_bersaglio > 0
        ? gdrcd_stmt_one('SELECT id_click FROM mappa_click WHERE id_click = ? LIMIT 1', [$id_bersaglio])
        : null;

    if (empty($zona)) {
        gdrcd_api_output(gdrcd_api_status_invalid($MESSAGE['interface']['meteo']['error']['no_map']));
        die();
    }
} else {
    $id_bersaglio = $id_luogo;
}

if ($azione == 'imposta') {
    // Stesse regole del pannello di gestione: temperatura, condizione e durata
    $dati = gdrcd_meteo_valida_richiesta(
        $_POST,
        gdrcd_meteo_limiti(),
        gdrcd_meteo_condizioni(),
        gdrcd_meteo_durate(),
        gdrcd_meteo_now()
    );

    if (is_string($dati)) {
        gdrcd_api_output(gdrcd_api_status_invalid($MESSAGE['interface']['meteo']['error'][$dati]));
        die();
    }

    $esito = gdrcd_meteo_imposta($bersaglio, $id_bersaglio, $dati['temperatura'], $dati['condizione'], $dati['scadenza']);
} else {
    $esito = gdrcd_meteo_azzera($bersaglio, $id_bersaglio);
}

if (!$esito) {
    gdrcd_api_output(gdrcd_api_status_invalid($MESSAGE['interface']['meteo']['error']['invalid_target']));
    die();
}

// Ritorna il meteo risultante, così il form può aggiornare subito il box
gdrcd_api_output(gdrcd_api_status_ok(
    gdrcd_meteo_corrente($id_luogo, (int)($_SESSION['mappa'] ?? 0))
));
