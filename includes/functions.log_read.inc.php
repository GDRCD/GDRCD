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
            return ['banca.invio_bonifico', 'banca.ricezione_bonifico', 'personaggio.cedi_oggetto','personaggio.ricevi_oggetto'];

        case NUOVOLAVORO:
            return ['personaggio.nuovo_lavoro', 'personaggio.assegna_lavoro'];

        case DIMISSIONE:
            return ['personaggio.dimissione_lavoro'];

        case CHANGEDROLE:
            return ['personaggio.permessi.cambio'];

        case CHANGEDPASS:
            return ['personaggio.cambio_password'];

        case PX:
            return ['personaggio.assegna_px','personaggio.riceve_px'];

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
function gdrcd_extract_logs($eventi = null, $idPersonaggio = null, $limit = 100, $offset = 0)
{
    $sql = "SELECT `id_personaggio`, `data`, `descrizione`, `contesto`
            FROM `logs`
            WHERE 1=1";

    $params = [];

    // Filtro per tipo evento (opzionale)
    if ($eventi !== null) {
        if (!is_array($eventi)) {
            $eventi = [$eventi];
        }

        $eventi = array_values(array_filter($eventi, static function ($evento) {
            return $evento !== null && $evento !== '';
        }));

        if (!empty($eventi)) {
            $placeholders = implode(',', array_fill(0, count($eventi), '?'));
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(`contesto`, '$.evento')) IN ($placeholders)";
            $params = array_merge($params, $eventi);
        }
    }

    // Filtro per personaggio (opzionale)
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
function gdrcd_count_logs($eventi = null, $idPersonaggio = null): int
{
    $sql = "SELECT COUNT(*) AS totale FROM `logs` WHERE 1=1";
    $params = [];

    if ($eventi !== null) {
        if (!is_array($eventi)) {
            $eventi = [$eventi];
        }

        $eventi = array_values(array_filter($eventi, static function ($evento) {
            return $evento !== null && $evento !== '';
        }));

        if (!empty($eventi)) {
            $placeholders = implode(',', array_fill(0, count($eventi), '?'));
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(`contesto`, '$.evento')) IN ($placeholders)";
            $params = array_merge($params, $eventi);
        }
    }

    // Filtro per personaggio (opzionale)
    if ($idPersonaggio !== null) {
        $sql .= " AND `id_personaggio` = ?";
        $params[] = (int)$idPersonaggio;
    }
    

    $row = gdrcd_stmt_one($sql, $params);

    return (int)($row['totale'] ?? 0);
}
/**
 * Estrapola il contesto JSON da una riga di log JSON.
 * 
 * @param array $row Riga log dal database
 * @return array Contesto JSON decodificato
 */
function gdrcd_extract_log_contesto(array $row)
{
       return json_decode($row['contesto'], true) ?: [];
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
 * @param int|null $whichLog Tipo di log (costante legacy), null = tutti i log
 * @param array $row      Riga log dal database
 *
 * @return array{autore: string, destinatario: string, descrizione: string}
 */
function gdrcd_present_log_row(?int $whichLog, array $row): array
{
    $contesto = gdrcd_extract_log_contesto($row);

    $evento = $contesto['evento'] ?? null;

    $autore = $contesto['autore'] ?? '-';
    $idAutore = $contesto['id_autore'] ?? null;

    $soggetto = $contesto['soggetto'] ?? '-';
    $idSoggetto = $contesto['id_soggetto'] ?? null;

    $destinatario = $contesto['destinatario'] ?? '-';
    $idDestinatario = $contesto['id_destinatario'] ?? null;

    $descrizione = $row['descrizione'] ?? '';
     // Vista "Tutti i log": usa il nuovo schema standard
    if ($whichLog === null) {
        
        

        return [
            'autore' => $autore,
            'id_autore' => $idAutore,
            'soggetto' => $soggetto,
            'id_soggetto' => $idSoggetto,
            'id_destinatario' => $idDestinatario,
            'destinatario' => $destinatario,
            'descrizione' => $descrizione,
        ];
    }

    switch (gdrcd_filter('num', $whichLog)) {
        case BLOCKED:
        case LOGGEDIN:
        case ERRORELOGIN:
            
            $descrizione = $row['descrizione'] ?? '';
            
            break;

        case ACCOUNTMULTIPLO:
           
            if (!empty($contesto['ip'])) {
                $descrizione .= ' (' . ($contesto['ip']) . ')';
            }
            break;

        case BONIFICO:
            
            $descrizione = $row['descrizione'] . ' (' . gdrcd_descrizione_transazione_pg($contesto) . ')';
             
            break;

        case NUOVOLAVORO:
        case DIMISSIONE:
             
            $descrizione = $row['descrizione'] ?? '';
            if (!empty($contesto['lavoro'])) {
                $descrizione .= ' (' . $contesto['lavoro'] . ')';
            }
            break;

        case CHANGEDROLE:
            
            $descrizione = $row['descrizione'] . ' (' . ($contesto['nuovo_ruolo'] ?? '-') . ')';
            break;

        case CHANGEDPASS:
            
            $descrizione = $row['descrizione'] ?? '';
           
            break;

        case PX:
             $descrizione = $row['descrizione'] . ' ' . '(' . (int)($contesto['px'] ?? 0) . ' px)';
            if (!empty($contesto['causale'])) {
                $descrizione .= ' Causale: ' . $contesto['causale'];
            }
            break;

        case DELETEPG:
             $descrizione = $row['descrizione'] ?? '';
            break;

        case CHANGEDNAME:
            $descrizione = $row['descrizione'] . ' ' . '(Da "' . ($contesto['nome_precedente'] ?? '-') . '" a "' . ($contesto['nome_nuovo'] ?? '-') . '")';
            break;

        default:
            
            $descrizione = $row['descrizione'] ?? '';
            break;
    }     
            
    return [
        'id_autore' =>  $contesto['id_autore'] ?? null,
        'autore' => $contesto['autore'] ?? '-',
        'id_soggetto' => $contesto['id_soggetto'] ?? null,
        'soggetto' => $contesto['soggetto'] ?? '-',
        'id_destinatario' => $contesto['id_destinatario'] ?? null,
        'destinatario' => $contesto['destinatario'] ?? '-',
        'descrizione' => $descrizione
    ];
}


function gdrcd_descrizione_transazione_pg(array $contesto): string
{
    $evento = $contesto['evento'] ?? '';

    if (in_array($evento, ['banca.invio_bonifico', 'banca.ricezione_bonifico'], true)) {
        $descrizione = (string)($contesto['ammontare'] ?? '-');

        if (!empty($contesto['valuta'])) {
            $descrizione .= ' ' . $contesto['valuta'];
        }

        if (!empty($contesto['causale'])) {
            $descrizione .= ' - ' . $contesto['causale'];
        }

        return $descrizione;
    }

    if (in_array($evento, ['personaggio.cedi_oggetto', 'personaggio.ricevi_oggetto'], true)) {
        $descrizione = (string)($contesto['oggetto'] ?? 'Oggetto');

        $quantita = (int)($contesto['quantita_rimossa'] ?? $contesto['quantita'] ?? 1);
        if ($quantita > 1) {
            $descrizione .= ' x' . $quantita;
        }

        $cariche = (int)($contesto['cariche'] ?? 0);
        if ($cariche > 0) {
            $descrizione .= ' (' . $cariche . ' cariche)';
        }

        return $descrizione;
    }

    return '-';
}
/**
 * Crea il contesto del log
 * @param array $contesto Il contesto del log
 * @param int $idSoggetto L'ID del soggetto
 * @param string $soggetto Il nome del soggetto
 * @param int $idAutore L'ID dell'autore
 * @param string $autore Il nome dell'autore
 * @return array Il contesto del log
 */

function gdrcd_log_context_make(
    array $contesto,
    int $idSoggetto = null,
    string $soggetto = null,
    int $idAutore = null,
    string $autore = null
    
    
) {
    $autore = [
        'id_autore' => $idAutore ?? $_SESSION['id_personaggio'],
        'autore' => $autore ?? $_SESSION['login']
    ];
     $soggetto = $idSoggetto && $soggetto ? [
        'id_soggetto' => $idSoggetto,
        'soggetto' => $soggetto,
    ] : [];

    return [...$autore, ...$soggetto, ...$contesto];
}