<?php

/**
 * Questo file contiene le funzioni di logging.
 *
 * Il sistema di logging permette di registrare nel database eventi utili per:
 * - debug tecnico
 * - tracciamento delle azioni utente
 * - monitoraggio di anomalie o errori gestiti
 * - segnalazione di problemi critici
 *
 * Tutte le funzioni wrapper presenti nel file delegano a gdrcd_log(),
 * che rappresenta il punto centrale di scrittura dei log nella tabella `log`.
 *
 * NOTA:
 * Questo file si occupa esclusivamente della scrittura dei log e non della
 * loro visualizzazione o consultazione.
 * ============================================================================
 */

/**
 * Registra un evento nella tabella di log.
 *
 * Questa è la funzione base del sistema di logging: salva nel database
 * la descrizione dell'evento, il livello di gravità, il contesto applicativo
 * in cui si è verificato e l'eventuale personaggio collegato all'azione.
 *
 * Viene richiamata indirettamente dalle funzioni wrapper dedicate ai singoli
 * livelli di log (debug, info, warning, error, ecc.), così da centralizzare
 * in un solo punto la scrittura dei record.
 *
 * @param string $descrizione Descrizione testuale dell'evento da registrare
 * @param string $livello_log Livello del log (es. debug, info, warning, error)
 * @param array $contesto Modulo o area applicativa da cui proviene il log
 * @param int|null $id_personaggio ID del personaggio associato all'evento, se presente
 * @return void
 */
/**
 * Registra un evento nella tabella di log.
 *
 * @param string $descrizione
 * @param string $livello_log
 * @param array $contesto
 * @param int|null $id_personaggio
 * @return void
 */
function gdrcd_log($descrizione, $livello_log, $contesto = null, $id_personaggio = null)
{
    
    if (is_array($contesto) || is_object($contesto)) {
        $contesto = json_encode($contesto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    if ($contesto === null) {
        $contesto = 'NULL';
    }

    $descrizione = gdrcd_filter('in', $descrizione);
    $livello_log = gdrcd_filter('in', $livello_log);
     
    gdrcd_stmt(
        "INSERT INTO `log` (`id`, `data`, `descrizione`, `livello_log`, `contesto`, `id_personaggio`)
         VALUES (UUID_TO_BIN(UUID()), NOW(), ?, ?, ?, ?)",
        [
            'sssi',
            $descrizione,
            $livello_log,
            $contesto,
            ($id_personaggio === null ? 0 : (int)$id_personaggio)
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Significato dei livelli di log
|--------------------------------------------------------------------------
| debug     : informazioni tecniche a basso livello, utili in fase di sviluppo
|             o analisi approfondita (es. query SQL, dati interni, flussi tecnici).
|
| info      : eventi informativi legati al normale funzionamento del sistema
|             o ad azioni compiute dagli utenti.
|
| notice    : eventi rilevanti ma non problematici, utili da tenere sotto controllo,
|             ad esempio attività di gestione o operazioni amministrative.
|
| warning   : situazioni anomale che non bloccano il sistema ma richiedono attenzione.
|
| error     : errori gestiti dall'applicazione, per cui il sistema continua a funzionare
|             ma l'operazione non è andata a buon fine.
|
| critical  : errori gravi o condizioni che compromettono seriamente il corretto
|             funzionamento dell'applicazione.
|
| alert     : condizioni eccezionali che richiedono intervento rapido; attualmente
|             previsto per usi futuri.
|
| emergency : livello massimo di gravità, pensato per situazioni critiche estreme;
|             attualmente previsto per usi futuri.
|--------------------------------------------------------------------------
*/

/**
 * Registra un log di livello debug.
 *
 * Da utilizzare per informazioni tecniche dettagliate, utili soprattutto
 * durante sviluppo, test o analisi di problemi complessi.
 *
 * @param string $messaggio Descrizione dell'evento
 * @param array $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_debug($messaggio, $contesto = null, $id_personaggio = null)
{
    gdrcd_log($messaggio, 'debug', $contesto, $id_personaggio);
}

/**
 * Registra un log di livello info.
 *
 * Da utilizzare per tracciare eventi applicativi ordinari, come azioni utente
 * o normali cambi di stato del sistema.
 *
 * @param string $messaggio Descrizione dell'evento
 * @param array $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_info($messaggio, $contesto = null, $id_personaggio = null)       
{
    gdrcd_log($messaggio, 'info', $contesto, $id_personaggio);
}

/**
 * Registra un log di livello notice.
 *
 * Da utilizzare per eventi degni di nota che non rappresentano errori,
 * ma che è utile conservare a fini di controllo o audit.
 *
 * @param string $messaggio Descrizione dell'evento
 * @param array $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_notice($messaggio, $contesto = null, $id_personaggio = null)
{
    gdrcd_log($messaggio, 'notice', $contesto, $id_personaggio);
}

/**
 * Registra un log di livello warning.
 *
 * Da utilizzare per anomalie, incoerenze o situazioni potenzialmente problematiche
 * che non impediscono l'esecuzione del sistema ma meritano attenzione.
 *
 * @param string $messaggio Descrizione dell'evento
 * @param array $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_warning($messaggio, $contesto = null, $id_personaggio = null)
{
    gdrcd_log($messaggio, 'warning', $contesto, $id_personaggio);
}

/**
 * Registra un log di livello error.
 *
 * Da utilizzare quando si verifica un errore gestito dall'applicazione:
 * l'operazione fallisce, ma il sistema continua a funzionare.
 *
 * @param string $messaggio Descrizione dell'errore
 * @param array $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_error($messaggio, $contesto = null, $id_personaggio = null)  
{
    gdrcd_log($messaggio, 'error', $contesto, $id_personaggio);
}

/**
 * Registra un log di livello critical.
 *
 * Da utilizzare per errori gravi che compromettono in modo importante
 * il corretto funzionamento del sistema o di una sua componente.
 *
 * @param string $messaggio Descrizione dell'errore critico
 * @param array $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_critical($messaggio, $contesto = null, $id_personaggio = null)   
{
    gdrcd_log($messaggio, 'critical', $contesto, $id_personaggio);
}

/**
 * Registra un log di livello alert.
 *
 * Previsto per situazioni eccezionali che richiedono attenzione immediata.
 * Al momento funge principalmente da livello disponibile per usi futuri.
 *
 * @param string $messaggio Descrizione dell'evento
 * @param array $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_alert($messaggio, $contesto = null, $id_personaggio = null)
{
    gdrcd_log($messaggio, 'alert', $contesto, $id_personaggio);
}

/**
 * Registra un log di livello emergency.
 *
 * È il livello massimo di gravità, pensato per condizioni critiche estreme
 * che rendono il sistema o una sua parte inutilizzabile.
 * Attualmente è mantenuto per completezza e per possibili sviluppi futuri.
 *
 * @param string $messaggio Descrizione dell'evento
 * @param array $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_emergency($messaggio, $contesto = null, $id_personaggio = null)
{
    gdrcd_log($messaggio, 'emergency', $contesto, $id_personaggio);
}



function estraiLog( $evento, $limit, $idPersonaggio = null)
{
    $stmt = gdrcd_stmt(
        "SELECT `data`, `descrizione`, `contesto`
         FROM `log`
         WHERE `id_personaggio` = ?
           AND JSON_UNQUOTE(JSON_EXTRACT(`contesto`, '$.evento')) = ?
         ORDER BY `data` DESC
         LIMIT ?",
        ['isi', (int)$idPersonaggio, $evento, (int)$limit]
    );
    $logs = [];

    while ($row = gdrcd_query($stmt, 'assoc')) {
        $row['contesto_decodificato'] = json_decode($row['contesto'], true) ?: [];
        $logs[] = $row;
    }

    gdrcd_query($stmt, 'free');

    return $logs;
}

function gdrcd_log_event_map()
{
    return [
        'login_bloccato' => 'auth.login.bloccato',
        'login_successo' => 'auth.login.successo',
        'login_fallito' => 'auth.login.fallito',
        'multiaccount_cookie' => 'auth.multiaccount.cookie',
        'multiaccount_ip' => 'auth.multiaccount.ip',

        'bonifico_inviato' => 'banca.invio_bonifico',
        'bonifico_ricevuto' => 'banca.ricezione_bonifico',

        'cambio_nome' => 'personaggio.cambio_nome',
        'cambio_password' => 'personaggio.cambio_password',
        'cancella_account' => 'personaggio.cancella_account',
        'disabilita_account' => 'personaggio.disabilita_account',
        'ripristina_account' => 'personaggio.ripristina_account',

        'cambio_permessi' => 'personaggio.permessi.cambio',
        'assegna_px' => 'personaggio.assegna_px',

        'nuovo_lavoro' => 'personaggio.nuovo_lavoro',
        'dimissioni_lavoro' => 'personaggio.dimissioni_lavoro',
        'assegna_lavoro' => 'personaggio.assegna_lavoro',

        'abbandona_oggetto' => 'personaggio.abbandona_oggetto',
        'cedi_oggetto' => 'personaggio.cedi_oggetto',
        'ricevi_oggetto' => 'personaggio.ricevi_oggetto',
    ];
}
         /**
         * Restituisce il gruppo di eventi JSON associati alla vecchia costante log.
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
         * Conta i log per una lista di eventi.
         */
        function gdrcd_count_logs_by_events(array $eventi)
        {
            if (empty($eventi)) {
                return 0;
            }

            $placeholders = implode(',', array_fill(0, count($eventi), '?'));
            $types = str_repeat('s', count($eventi));

            $stmt = gdrcd_stmt(
                "SELECT COUNT(*) AS totale
                 FROM `log`
                 WHERE JSON_UNQUOTE(JSON_EXTRACT(`contesto`, '$.evento')) IN ($placeholders)",
                array_merge([$types], $eventi)
            );

            $row = gdrcd_query($stmt, 'assoc');
            gdrcd_query($stmt, 'free');

            return (int)($row['totale'] ?? 0);
        }
        /**
         * Estrae i log per una lista di eventi.
         */
        function gdrcd_extract_logs_by_events(array $eventi, $limit, $offset = 0)
        {
            if (empty($eventi)) {
                return [];
            }

            $placeholders = implode(',', array_fill(0, count($eventi), '?'));
            $types = str_repeat('s', count($eventi)) . 'ii';

            $stmt = gdrcd_stmt(
                "SELECT `id_personaggio`, `data`, `descrizione`, `contesto`
                 FROM `log`
                 WHERE JSON_UNQUOTE(JSON_EXTRACT(`contesto`, '$.evento')) IN ($placeholders)
                 ORDER BY `data` DESC
                 LIMIT ?, ?",
                array_merge([$types], $eventi, [(int)$offset, (int)$limit])
            );

            $logs = [];

            while ($row = gdrcd_query($stmt, 'assoc')) {
                $row['contesto_decodificato'] = json_decode($row['contesto'], true) ?: [];
                $logs[] = $row;
            }

            gdrcd_query($stmt, 'free');

            return $logs;
        }
/**
         * Trasforma un log in righe stampabili per la tabella admin.
         */
        function gdrcd_present_log_row($whichLog, array $row)
        {
            $contesto = $row['contesto_decodificato'] ?? [];

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
                        $autore = $contesto['nome_interessato'] ?? '-';
                        $destinatario = $contesto['controparte_nome'] ?? '-';
                    } else {
                        $autore = $contesto['autore'] ?? '-';
                        $destinatario = $contesto['nome_interessato'] ?? '-';
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