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
 * @param string $contesto Modulo o area applicativa da cui proviene il log
 * @param int|null $id_personaggio ID del personaggio associato all'evento, se presente
 * @return void
 */
function gdrcd_log($descrizione, $livello_log, $contesto, $id_personaggio)
{
     
    gdrcd_query("
        INSERT INTO `log` (`id`, `data`, `descrizione`, `livello_log`, `contesto`, `id_personaggio`)
        VALUES
            (UUID(),
            NOW(),
            '$descrizione',
            '$livello_log',
            '$contesto',
            $id_personaggio)
    ");
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
 * @param string $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_debug($messaggio, $contesto, $id_personaggio)
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
 * @param string $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_info($messaggio, $contesto, $id_personaggio)
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
 * @param string $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_notice($messaggio, $contesto, $id_personaggio)
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
 * @param string $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_warning($messaggio, $contesto, $id_personaggio)
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
 * @param string $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_error($messaggio, $contesto, $id_personaggio)
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
 * @param string $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_critical($messaggio, $contesto, $id_personaggio)
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
 * @param string $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_alert($messaggio, $contesto, $id_personaggio)
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
 * @param string $contesto Modulo o area applicativa di riferimento
 * @param int|null $id_personaggio ID del personaggio associato al log
 * @return void
 */
function gdrcd_log_emergency($messaggio, $contesto, $id_personaggio)
{
    gdrcd_log($messaggio, 'emergency', $contesto, $id_personaggio);
}