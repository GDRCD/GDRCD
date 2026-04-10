<?php

/**
 * Questo file contiene le funzioni di log.
 *
 * Il sistema di log permette di leggere i log registrati nel database.
 * Permette di filtrare i log per eventi specifici, per personaggi e per paginazione.
 */
/*
|--------------------------------------------------------------------------
| Compatibilità con codici log legacy
|--------------------------------------------------------------------------
*/

/**
 * Restituisce il gruppo di eventi JSON associati a una costante legacy.
 *
 * Permette di tradurre i vecchi codici di log (es. LOGGEDIN, BONIFICO)
 * nei corrispondenti eventi JSON utilizzati nel nuovo sistema.
 *
 * @param int $code Codice log legacy
 *
 * @return array Lista di eventi JSON associati
 */

function gdrcd_log_group_from_code($code)
{
    switch ((int)$code) {
        case BLOCKED:
            return ['auth.login.bloccato', 'auth.login.bloccato.blacklist'];

        case LOGGEDIN:
            return ['auth.login.successo'];

        case ACCOUNTMULTIPLO:
            return ['auth.multiaccount.cookie', 'auth.multiaccount.ip'];

        case ERRORELOGIN:
            return ['auth.login.fallito'];

        case BONIFICO:
            return ['banca.invio_bonifico', 'banca.ricezione_bonifico'];

        case NUOVOLAVORO:
            return ['personaggio.nuovo_lavoro', 'personaggio.assegna_lavoro'];

        case DIMISSIONE:
            return ['personaggio.dimissioni_lavoro'];

        case CHANGEDROLE:
            return ['personaggio.permessi.cambio'];

        case CHANGEDPASS:
            return ['personaggio.cambio_password'];

        case PX:
            return ['personaggio.assegna_px'];

        case DELETEPG:
            return [
                'personaggio.cancella_account',
                'personaggio.disabilita_account',
                'personaggio.ripristina_account'
            ];

        case CHANGEDNAME:
            return ['personaggio.cambio_nome'];

        default:
            return [];
    }
}

/**
 * Estrae una lista di log filtrati per uno o più eventi, con supporto
 * opzionale al filtro per personaggio e alla paginazione.
 *
 * I log vengono cercati nel campo JSON `contesto`, utilizzando la chiave
 * `evento`, ordinati per data decrescente e restituiti con il contesto
 * già decodificato in array.
 *
 * @param string|array $eventi        Evento singolo o lista di eventi da cercare
 *                                    (es. 'auth.login.successo' oppure
 *                                    ['auth.login.successo', 'auth.login.fallito'])
 * @param int          $limit         Numero massimo di risultati da estrarre
 * @param int          $offset        Offset iniziale per la paginazione
 * @param int|null     $idPersonaggio ID del personaggio da filtrare; se null,
 *                                    non viene applicato alcun filtro sul personaggio
 *
 * @return array Lista dei log trovati, ciascuno arricchito con il campo
 *               `contesto_decodificato`
 */
function gdrcd_extract_logs($eventi, $limit = 100, $offset = 0, $idPersonaggio = null)
{
    if (!is_array($eventi)) {
        $eventi = [$eventi];
    }

    $eventi = array_values(array_filter($eventi, static function ($evento) {
        return $evento !== null && $evento !== '';
    }));

    if (empty($eventi)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($eventi), '?'));
    $sql = "SELECT `id_personaggio`, `data`, `descrizione`, `contesto`
            FROM `log`
            WHERE JSON_UNQUOTE(JSON_EXTRACT(`contesto`, '$.evento')) IN ($placeholders)";


    $params = $eventi;

    if ($idPersonaggio !== null) {
        $sql .= " AND `id_personaggio` = ?";

        $params[] = (int)$idPersonaggio;
    }

    $sql .= " ORDER BY `data` DESC
              LIMIT ?, ?";

    $params[] = (int)$offset;
    $params[] = (int)$limit;

    $logs = [];
    foreach (gdrcd_stmt_all($sql, $params) as $log) {
        $log['contesto_decodificato'] = json_decode($log['contesto'], true) ?: [];
        $logs[] = $log;
    }

    return $logs;
}



/**
 * Conta il numero di log associati a una lista di eventi JSON.
 *
 * La funzione interroga il campo `contesto`, utilizzando la chiave `evento`,
 * e restituisce il numero totale di record corrispondenti.
 *
 * @param array $eventi Lista di eventi JSON da cercare
 *
 * @return int Numero totale di log trovati
 */
function gdrcd_count_logs(array $eventi)
{
    if (empty($eventi)) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($eventi), '?'));

    $stmt = gdrcd_stmt_one(
        "SELECT COUNT(*) AS totale
            FROM `log`
            WHERE JSON_UNQUOTE(JSON_EXTRACT(`contesto`, '$.evento')) IN ($placeholders)",
        $eventi
    );

    return (int)($stmt['totale'] ?? 0);
}

/**
 * Estrapola il contesto JSON da una riga di log JSON.
 * 
 * @param array $row Riga log dal database
 * @return array Contesto JSON decodificato
 */
function gdrcd_extract_log_contesto(array $row)
{
    $contesto = $row['contesto_decodificato'] ?? [];
    return $contesto;
}

/**
 * Trasforma un log grezzo in una struttura leggibile per la UI admin.
 *
 * In base al tipo di log (costante legacy), interpreta il contenuto JSON
 * e costruisce una rappresentazione uniforme con:
 * - autore
 * - destinatario
 * - descrizione
 *
 * @param int   $whichLog Tipo di log (costante legacy)
 * @param array $row      Riga log dal database
 *
 * @return array{autore: string, destinatario: string, descrizione: string}
 */
function gdrcd_present_log_row($whichLog, array $row)
{
    $contesto = gdrcd_extract_log_contesto($row);

    $autore = '-';
    $destinatario = '-';
    $descrizione = $row['descrizione'] ?? '';

    switch ((int)$whichLog) {
        case BLOCKED:
        case LOGGEDIN:
        case ERRORELOGIN:
            $autore = $contesto['ip'] ?? ($contesto['host'] ?? '-');
            $destinatario = $contesto['utente'] ?? '-';
            $descrizione = $row['descrizione'];

            $autore = gdrcd_mask_ip($autore);
            break;

        case ACCOUNTMULTIPLO:
            $autore = $contesto['utente_corrente'] ?? '-';
            $destinatario = $contesto['altro_account'] ?? '-';
            $descrizione = $row['descrizione'];

            if (!empty($contesto['ip'])) {
                $descrizione .= ' (' . gdrcd_mask_ip($contesto['ip']) . ')';
            }
            break;

        case BONIFICO:
            if (($contesto['direzione'] ?? '') === 'uscita') {
                $autore = $contesto['nome_mittente'] ?? '-';
                $destinatario = $contesto['destinatario'] ?? '-';
            } else {
                $autore = $contesto['nome_mittente'] ?? '-';
                $destinatario = $contesto['destinatario'] ?? '-';
            }

            $descrizione = ($contesto['ammontare'] ?? '-') . ' ' .
                ($contesto['valuta'] ?? '') .
                (!empty($contesto['causale']) ? ' - ' . $contesto['causale'] : '');
            break;

        case NUOVOLAVORO:
        case DIMISSIONE:
            $autore = $contesto['autore'] ?? ($contesto['eseguito_da'] ?? '-');
            $destinatario = $contesto['nome_interessato'] ?? ($contesto['id_personaggio'] ?? '-');
            $descrizione = $contesto['lavoro'] ?? $row['descrizione'];
            break;

        case CHANGEDROLE:
            $autore = $contesto['nome_autore'] ?? '-';
            $destinatario = $contesto['nome_interessato'] ?? '-';
            $descrizione = $contesto['nuovo_ruolo'] ?? $row['descrizione'];
            break;

        case CHANGEDPASS:
            $autore = $contesto['nome'] ?? ($contesto['id_personaggio'] ?? '-');
            $destinatario = $row['id_personaggio'] ?? '-';
            $descrizione = $row['descrizione'];

            if (!empty($contesto['ip'])) {
                $descrizione .= ' (' . gdrcd_mask_ip($contesto['ip']) . ')';
            }
            break;

        case PX:
            $autore = $contesto['autore'] ?? '-';
            $destinatario = $contesto['nome_interessato'] ?? ($row['id_personaggio'] ?? '-');
            $descrizione = '(' . (int)($contesto['px'] ?? 0) . ' px) ' . ($contesto['causale'] ?? '');
            break;

        case DELETEPG:
            $autore = $contesto['eseguito_da'] ?? ($_SESSION['login'] ?? '-');
            $destinatario = $contesto['nome'] ?? ($contesto['id_personaggio'] ?? '-');
            $descrizione = $row['descrizione'];
            break;

        case CHANGEDNAME:
            $autore = $contesto['eseguito_da'] ?? '-';
            $destinatario = $contesto['nome_nuovo'] ?? ($row['id_personaggio'] ?? '-');
            $descrizione = 'Da "' . ($contesto['nome_precedente'] ?? '-') . '" a "' . ($contesto['nome_nuovo'] ?? '-') . '"';
            break;

        default:
            $autore = $contesto['autore'] ?? ($contesto['eseguito_da'] ?? '-');
            $destinatario = $contesto['nome_interessato'] ?? ($row['id_personaggio'] ?? '-');
            $descrizione = $row['descrizione'];
            break;
    }

    return [
        'autore' => $autore,
        'destinatario' => $destinatario,
        'descrizione' => $descrizione,
    ];
}
