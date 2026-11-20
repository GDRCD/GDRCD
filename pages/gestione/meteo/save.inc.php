<?php
/**
 * Imposta o ripristina il meteo manuale di una zona o di una chat dal
 * pannello di gestione. Incluso da index.inc.php per le richieste POST.
 *
 * Dati in input (POST):
 *  - op: 'save' (imposta) oppure 'reset' (ripristina)
 *  - bersaglio: 'zona' (mappa_click) oppure 'luogo' (chat in mappa)
 *  - id_click (zona) oppure id_luogo (chat): solo cifre
 *  - temperatura, condizione, durata: solo per 'save', con le stesse regole
 *    del form in chat (gdrcd_meteo_valida_richiesta())
 *
 * Non usare mai campi chiamati map_id o dir: config.core.inc.php sposta il
 * personaggio quando li riceve.
 */

/* Controllo permessi utente: il file non deve funzionare se incluso altrove */
if (!gdrcd_controllo_permessi($PARAMETERS['administration']['meteo']['access_level'])) {
    echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
    return;
}

$testi_meteo = $MESSAGE['interface']['meteo'];
$testi_pannello = $MESSAGE['interface']['administration']['meteo'];

$meteo_op = $_POST['op'] ?? null;
$meteo_bersaglio = $_POST['bersaglio'] ?? null;
$meteo_errore = null;
$meteo_esito = null;

if (($PARAMETERS['mode']['auto_meteo'] ?? 'ON') == 'ON') {
    // In modalità automatica il pannello è di sola lettura
    $meteo_errore = $testi_meteo['error']['auto_mode'];
} elseif (!in_array($meteo_op, ['save', 'reset'], true) || !in_array($meteo_bersaglio, ['zona', 'luogo'], true)) {
    $meteo_errore = $testi_meteo['error']['invalid_target'];
} else {
    // La zona o la chat deve esistere: una chat deve essere una vera chat (chat = 1)
    if ($meteo_bersaglio == 'zona') {
        $meteo_id = gdrcd_meteo_id_richiesta($_POST['id_click'] ?? null);
        $meteo_riga = $meteo_id !== null
            ? gdrcd_stmt_one('SELECT id_click FROM mappa_click WHERE id_click = ? LIMIT 1', [$meteo_id])
            : null;
    } else {
        $meteo_id = gdrcd_meteo_id_richiesta($_POST['id_luogo'] ?? null);
        $meteo_riga = $meteo_id !== null
            ? gdrcd_stmt_one('SELECT id FROM mappa WHERE id = ? AND chat = 1 LIMIT 1', [$meteo_id])
            : null;
    }

    if (empty($meteo_riga)) {
        $meteo_errore = $testi_pannello['error']['not_found'];
    } elseif ($meteo_op == 'save') {
        $meteo_dati = gdrcd_meteo_valida_richiesta(
            $_POST,
            gdrcd_meteo_limiti(),
            gdrcd_meteo_condizioni(),
            gdrcd_meteo_durate(),
            gdrcd_meteo_now()
        );

        if (is_string($meteo_dati)) {
            $meteo_errore = $testi_meteo['error'][$meteo_dati];
        } elseif (gdrcd_meteo_imposta($meteo_bersaglio, $meteo_id, $meteo_dati['temperatura'], $meteo_dati['condizione'], $meteo_dati['scadenza'])) {
            $meteo_esito = $testi_pannello['saved'];
        } else {
            $meteo_errore = $testi_meteo['error']['invalid_target'];
        }
    } elseif (gdrcd_meteo_azzera($meteo_bersaglio, $meteo_id)) {
        $meteo_esito = $testi_pannello['reset_done'];
    } else {
        $meteo_errore = $testi_meteo['error']['invalid_target'];
    }
}

if ($meteo_errore !== null) {
    echo '<div class="error">' . gdrcd_filter('out', $meteo_errore) . '</div>';
} else {
    echo '<div class="success">' . gdrcd_filter('out', $meteo_esito) . '</div>';
}
