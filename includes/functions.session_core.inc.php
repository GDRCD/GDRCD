<?php

/**
 * Funzioni di basso livello per la gestione delle sessioni PHP.
 *
 * Questo modulo fornisce le operazioni fondamentali per interagire con le sessioni PHP:
 * avvio, rigenerazione, lettura, scrittura, distruzione e calcolo del fingerprint.
 * Nessuna di queste funzioni esegue query al database.
 */

/**
 * Avvia o riprende una sessione PHP, acquisendo il lock.
 * Configura la sessione in modo sicuro impostando i parametri del cookie.
 *
 * @param string|null $id_sessione Se fornito, imposta l'id di sessione prima di avviarla.
 * @return void
 */
function gdrcd_session_init(?string $id_sessione = null): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => filter_var($_SERVER['HTTPS'] ?? '', FILTER_VALIDATE_BOOL),
        'httponly'  => true,
        'samesite'  => 'Strict',
    ]);

    if ($id_sessione !== null) {
        gdrcd_session_id($id_sessione);
    }

    session_start();
}

/**
 * Ritorna l'id della sessione corrente.
 *
 * @param string|null $id_sessione Se fornito, imposta l'id di sessione prima di avviarla.
 * @return string L'identificativo della sessione PHP attiva.
 */
function gdrcd_session_id(?string $id_sessione = null): string
{
    return session_id($id_sessione);
}

/**
 * Persiste le modifiche ai dati in sessione e rilascia il lock.
 * Dopo l'invocazione la sessione è in sola lettura.
 *
 * @return void
 */
function gdrcd_session_commit(): void
{
    session_write_close();
}

/**
 * Genera un nuovo id di sessione mantenendo i dati esistenti.
 *
 * @return string Il nuovo id di sessione generato.
 */
function gdrcd_session_regenerate(): string
{
    session_regenerate_id(false);

    return gdrcd_session_id();
}

/**
 * Elimina tutti i dati presenti nella sessione attiva.
 *
 * @return void
 */
function gdrcd_session_clear(): void
{
    session_unset();
}

/**
 * Distrugge completamente la sessione corrente e il cookie associato.
 *
 * @return void
 */
function gdrcd_session_destroy(): void
{
    // Elimina i dati in sessione
    gdrcd_session_clear();

    // Distrugge la sessione
    session_destroy();

    // Rimuove il cookie di sessione
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

/**
 * Scrive uno o più valori nella sessione attiva.
 *
 * Se $key è una stringa, scrive il singolo valore $value associato alla chiave.
 * Se $key è un array associativo, effettua la scrittura di tutti i valori in bulk.
 *
 * @param string|array $key La chiave oppure un array associativo chiave=>valore.
 * @param mixed $value Il valore da scrivere (usato solo se $key è una stringa).
 * @return void
 */
function gdrcd_session_write(string|array $key, mixed $value = null): void
{
    if (is_array($key)) {
        foreach ($key as $k => $v) {
            $_SESSION[$k] = $v;
        }
        return;
    }

    $_SESSION[$key] = $value;
}

/**
 * Elimina una o più chiavi dalla sessione attiva.
 *
 * Se $key è una stringa, elimina la singola chiave.
 * Se $key è un array, elimina tutte le chiavi specificate.
 *
 * @param string|array $key La chiave o un array di chiavi da eliminare.
 * @return void
 */
function gdrcd_session_delete(string|array $key): void
{
    if (is_array($key)) {
        foreach ($key as $k) {
            unset($_SESSION[$k]);
        }
        return;
    }

    unset($_SESSION[$key]);
}

/**
 * Legge un valore dalla sessione corrente.
 *
 * @param string $key La chiave del valore da leggere.
 * @param mixed $default Valore di fallback se la chiave non esiste.
 * @return mixed Il valore associato alla chiave, oppure $default.
 */
function gdrcd_session(string $key, mixed $default = null): mixed
{
    if (array_key_exists($key, $_SESSION)) {
        return $_SESSION[$key];
    }

    return $default;
}

/**
 * Calcola il livello di confidenza del fingerprint confrontando
 * i dati del client corrente con quelli salvati nei metadati della sessione.
 *
 * Ritorna una delle costanti GDRCD_FINGERPRINT_*.
 *
 * @param array $session_metadata I metadati della sessione dalla tabella sessions.
 * @return int Una delle costanti: GDRCD_FINGERPRINT_VERYCONFIDENT, GDRCD_FINGERPRINT_CONFIDENT,
 *             GDRCD_FINGERPRINT_UNSURE o GDRCD_FINGERPRINT_WRONG.
 */
function gdrcd_session_fingerprint(array $session_metadata): string
{
    $score = gdrcd_session_fingerprint_score($session_metadata);

    if ($score === 1.0) {
        return GDRCD_FINGERPRINT_VERYCONFIDENT;
    }

    if ($score >= 0.8) {
        return GDRCD_FINGERPRINT_CONFIDENT;
    }

    if ($score >= 0.5) {
        return GDRCD_FINGERPRINT_UNSURE;
    }

    return GDRCD_FINGERPRINT_WRONG;
}

/**
 * Calcola lo score numerico di affidabilità del fingerprint.
 *
 * Lo score è normalizzato tra 0.0 e 1.0, dove 1.0 indica
 * che tutti i parametri del client corrispondono.
 *
 * @param array $session_metadata I metadati della sessione dalla tabella sessions.
 * @return float Lo score di affidabilità (0.0 - 1.0).
 */
function gdrcd_session_fingerprint_score(array $session_metadata): float
{
    $signals = gdrcd_session_fingerprint_signals($session_metadata);
    $penalty = 0.0;

    foreach ($signals as $signal) {
        $penalty += GDRCD_FINGERPRINT_WEIGHTS[$signal] ?? 0.0;
    }

    $max_score = array_sum(GDRCD_FINGERPRINT_WEIGHTS);

    return 1.0 - ($penalty / $max_score);
}

/**
 * Determina quali parametri del client risultano diversi rispetto
 * ai valori salvati nei metadati della sessione.
 *
 * @param array $session_metadata I metadati della sessione dalla tabella sessions.
 * @return array<string> L'elenco dei parametri che risultano differenti definiti in base alle
 *               seguenti costanti:
 *                 - GDRCD_FINGERPRINT_SIGNAL_USERAGENT
 *                 - GDRCD_FINGERPRINT_SIGNAL_IP
 *                 - GDRCD_FINGERPRINT_SIGNAL_ACCEPT
 *                 - GDRCD_FINGERPRINT_SIGNAL_LANGUAGE
 *                 - GDRCD_FINGERPRINT_SIGNAL_ENCODING
 */
function gdrcd_session_fingerprint_signals(array $session_metadata): array
{
    $client = is_string($session_metadata['client'])
        ? json_decode($session_metadata['client'], true)
        : ($session_metadata['client'] ?? []);

    $signals = [];

    // Confronto User-Agent
    $current_ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stored_ua = $client[GDRCD_FINGERPRINT_SIGNAL_USERAGENT] ?? '';

    if ($current_ua !== $stored_ua) {
        $signals[] = GDRCD_FINGERPRINT_SIGNAL_USERAGENT;
    }

    // Confronto IP
    $current_ip = gdrcd_session_client_ip();
    $stored_ip = $session_metadata['ip'] ?? '';

    if ($current_ip !== $stored_ip) {
        $signals[] = GDRCD_FINGERPRINT_SIGNAL_IP;
    }

    // Confronto Accept
    $current_accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $stored_accept = $client[GDRCD_FINGERPRINT_SIGNAL_ACCEPT] ?? '';

    if ($current_accept !== $stored_accept) {
        $signals[] = GDRCD_FINGERPRINT_SIGNAL_ACCEPT;
    }

    // Confronto Accept-Language
    $current_lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    $stored_lang = $client[GDRCD_FINGERPRINT_SIGNAL_LANGUAGE] ?? '';

    if ($current_lang !== $stored_lang) {
        $signals[] = GDRCD_FINGERPRINT_SIGNAL_LANGUAGE;
    }

    // Confronto Accept-Encoding
    $current_enc = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
    $stored_enc = $client[GDRCD_FINGERPRINT_SIGNAL_ENCODING] ?? '';

    if ($current_enc !== $stored_enc) {
        $signals[] = GDRCD_FINGERPRINT_SIGNAL_ENCODING;
    }

    return $signals;
}

/**
 * Costruisce l'array dei dati client correnti da salvare nel campo JSON della tabella sessions.
 *
 * @return array Array associativo con le informazioni del client attuale.
 */
function gdrcd_session_client_data(): array
{
    return [
        GDRCD_FINGERPRINT_SIGNAL_USERAGENT  => $_SERVER['HTTP_USER_AGENT'] ?? '',
        GDRCD_FINGERPRINT_SIGNAL_ACCEPT     => $_SERVER['HTTP_ACCEPT'] ?? '',
        GDRCD_FINGERPRINT_SIGNAL_LANGUAGE   => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
        GDRCD_FINGERPRINT_SIGNAL_ENCODING   => $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
    ];
}

/**
 * Ritorna l'IP corrente del client in formato binario per l'archiviazione nel database.
 *
 * @return string L'indirizzo IP in formato binario (compatibile con VARBINARY(16)).
 */
function gdrcd_session_client_ip(): string
{
    return inet_pton(gdrcd_client_ip());
}
