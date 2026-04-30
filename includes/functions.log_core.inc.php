<?php
/**
 * Questo file contiene le funzioni di log.
 *
 * Il sistema di log permette di registrare nel database eventi utili per:
 * - debug tecnico
 * - tracciamento delle azioni utente
 * - monitoraggio di anomalie o errori gestiti
 * - segnalazione di problemi critici
 *
 * Tutte le funzioni wrapper presenti nel file delegano a gdrcd_log(),
 * che rappresenta il punto centrale di scrittura dei log nella tabella `logs`.
 *
 * NOTA:
 * Questo file si occupa esclusivamente della scrittura dei log e non della
 * loro visualizzazione o consultazione.
 * ============================================================================
 */

/*
|--------------------------------------------------------------------------
| Significato dei livelli di log
|--------------------------------------------------------------------------
| debug     : Solo sviluppo/test. Dati tecnici molto dettagliati.
|             Utili solo durante sviluppo o test, non in produzione.
|             Utili per debug tecnico, analisi approfondite, ecc.
|             (es. query SQL, dati interni, flussi tecnici).    
|
| info      : Azioni ordinarie dell’utente o del sistema.
|               (es. Login riuscito)
|
| notice    : Eventi importanti ma non problematici 
|                (es. Bonifico tra PG, assegnazione PX, consegna oggetto, modifica scheda da staff.)
|
| warning   : Anomalia o comportamento sospetto (Login fallito, tentativo accesso pagina non autorizzata, dati mancanti ma recuperabili.)
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
 * Inizializza il buffer dei log e registra il flush automatico a fine script.
 *
 * La shutdown function svuota il buffer richiamando gdrcd_logs_buffer(true),
 * così i log accumulati vengono scritti nel database con un'unica INSERT multipla.
 *
 * @return void
 */
function gdrcd_logs_init()
{
    static $initialized = false;

    if ($initialized) {
        return;
    }

    $initialized = true;

    register_shutdown_function(function () {
        gdrcd_logs_buffer(true);
    });
}

/**
 * Accumula i log in memoria e, quando richiesto, li scrive nel database.
 *
 * Se descrizione, timestamp e livello sono valorizzati, il log viene aggiunto
 * al buffer statico. Se $flush vale true, il buffer viene svuotato con una
 * singola INSERT multipla.
 *
 * @param bool $flush Se true, forza la scrittura dei log accumulati
 * @param string|null $descrizione Descrizione testuale dell'evento da registrare
 * @param string|null $timestamp Timestamp del log in formato compatibile con MySQL
 * @param string|null $livello_log Livello del log (es. debug, info, warning, error)
 * @param string|null $contesto Contesto già codificato in JSON, se presente
 * @param int|null $id_personaggio ID del personaggio associato all'evento, se presente
 * @return void
 */
function gdrcd_logs_buffer($flush = false, $descrizione = null, $timestamp = null, $livello_log = null, $contesto = null, $id_personaggio = null)
{
    static $logs = [];

    if ($descrizione !== null && $timestamp !== null && $livello_log !== null) {
        $logs[] = [
            'data' => $timestamp,
            'descrizione' => $descrizione,
            'livello_log' => $livello_log,
            'contesto' => $contesto,
            'id_personaggio' => ($id_personaggio === null ? 0 : (int)$id_personaggio),
        ];
    }

    if ($flush === true && !empty($logs)) {
        
        $values_placeholders = [];
        $params = [];

        foreach ($logs as $log) {
            $values[] = '(UUID_TO_BIN(UUID()), ?, ?, ?, ?, ?)';

            $params[] = $log['data'];
            $params[] = $log['descrizione'];
            $params[] = $log['livello_log'];
            $params[] = $log['contesto'];
            $params[] = $log['id_personaggio'];
        }

        gdrcd_stmt(
            "INSERT INTO `logs` (`id`, `data`, `descrizione`, `livello_log`, `contesto`, `id_personaggio`) VALUES " . implode(', ', $values),
            $params
        );

        $logs = [];
    }
}

/**
 * Registra un evento nella tabella di log.
 *
 * Questa è la funzione base del sistema di logging: prepara la descrizione
 * dell'evento, il livello di gravità, il contesto applicativo e l'eventuale
 * personaggio collegato, poi delega la scrittura a gdrcd_logs_buffer().
 *
 * Viene richiamata indirettamente dalle funzioni wrapper dedicate ai singoli
 * livelli di log (debug, info, warning, error, ecc.), così da centralizzare
 * in un solo punto la preparazione dei record.
 *
 * @param string $descrizione Descrizione testuale dell'evento da registrare
 * @param string $livello_log Livello del log (es. debug, info, warning, error)
 * @param array|null $contesto Modulo o area applicativa da cui proviene il log
 * @param int|null $id_personaggio ID del personaggio associato all'evento, se presente
 * @return void
 * @throws Exception
 */
function gdrcd_log($descrizione, $livello_log, $contesto = null, $id_personaggio = null)
{
    global $PARAMETERS;

    if ($livello_log === 'debug') {
        if (empty($PARAMETERS['debug_mode'])) {
            return;
        }
    }

    if ($contesto !== null) {
        if (!is_array($contesto)) {
            throw new Exception('Parametro $contesto non valido. Sono ammessi solo array');
        }

        $contesto = json_encode($contesto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    gdrcd_logs_buffer(
        false,
        $descrizione,
        date('Y-m-d H:i:s'),
        $livello_log,
        $contesto,
        $id_personaggio
    );
}

/**
 * Registra un log di livello debug.
 *
 * Da utilizzare per informazioni tecniche dettagliate,
 * utili solo durante sviluppo o test 
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
 * Da utilizzare per tracciare eventi importanti ma non problematici,
 * come la ricezione di un bonifico o l'assegnazione di un oggetto.
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
 * Da utilizzare per anomalie, incoerenze o situazioni potenzialmente problematiche,
 * come il tentativo fallito di login
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
