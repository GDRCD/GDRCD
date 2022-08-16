<?php
/**
 * @class Session
 * @note Classe responsabile per la gestione della sessione utente nel sito
 */
class Session extends BaseClass
{
    /**
     * @fn start
     * @note Inizializza la sessione e popola i dati di
     * @note sessione eventualmente associati all'utente connessso
     * @param bool $readAndClose Default false. Se posto su true
     * la sessione rilascierà il lock immediatamente dopo esser stata letta
     * @return void
     */
    public static function start(bool $readAndClose = false): void
    {
        self::secureSessionConfiguraton();
        session_start(['read_and_close' => $readAndClose]);
    }

    /**
     * @fn commit
     * @note salva eventuali modifiche appese, rilascia il lock e chiude la sessione
     * @return void
     */
    public static function commit(): void {
        session_write_close();
    }

    /**
     * @fn read
     * @note legge e riorna il valore in sessione associato alla chiave indicata
     * @param string|null $key
     * @return mixed
     */
    public static function read(?string $key = null): mixed {
        return is_null($key)? $_SESSION : ($_SESSION[$key]?? null);
    }

    /**
     * @fn save
     * @note memorizza localmente le modifiche in sessione (fino quado non viene esplicitamente chiamato un ::commit())
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function save(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    /**
     * @fn isHttpsRequest
     * @note indica se la richiesta corrente sta avvenendo sotto protocollo https
     * @return bool
     */
    private static function isHttpsRequest(): bool
    {
        $isHttps = $_SERVER['HTTPS']
            ?? $_SERVER['REQUEST_SCHEME']
            ?? $_SERVER['HTTP_X_FORWARDED_PROTO']
            ?? null;

        return $isHttps && (
            strcasecmp('on', $isHttps) == 0
            || strcasecmp('https', $isHttps) == 0
        );
    }

    /**
     * @fn secureSessionConfiguraton
     * @note si occupa di impostare correttamente tutti i valori di
     * @note configurazione per garantire la sicurezza delle sessioni
     * @return void
     */
    private static function secureSessionConfiguraton(): void
    {
        /*
         * Le sicurezza delle sessioni dipende in larga parte da
         * una corretta configurazione dei parametri del php.ini.
         * Conviene definirli per bene qui, di modo che in caso di
         * un settaggio errato da parte del fornitore dei servizi
         * di hosting la sicurezza del sito non venga compromessa.
         */

        // Usiamo la sessione con files e cookie
        ini_set('session.use_cookies', 'On');
        ini_set('session.use_only_cookies', 'On');

        // Cookie scade quando si chiude il browser
        ini_set('session.cookie_lifetime', '0');

        // previene l'uso di id di sessione non inizializzati, mitigando alcune tipologie di attacco
        ini_set('session.use_strict_mode', 'On');

        // impedisce l'accesso alla sessione da parte di script javascript (utile contro xss)
        ini_set('session.cookie_httponly', 'On');

        // permette al cookie di sessione di essere letto soltanto sotto connessione sicura (https)
        ini_set('session.cookie_secure', 'On');

        // agisce come un controllo CSRF, non accettando cookie di richieste che non provengano dallo stesso dominio
        ini_set('session.cookie_samesite', 'SameSite');

        // disattiva la gestione degli id di sessione trasparenti, impedendo di esibirli/riceverli tramite url
        ini_set('session.use_trans_sid', 'Off');

        // id di sessione più lunghi sono anche più robusti. Molti provider ancora li configurano a 26 caratteri
        ini_set('session.sid_length', '48');

        // quanti più bit sono indicati, tanto più robusto sarà l'id di sessione generato a parità di lunghezza
        ini_set('session.sid_bits_per_character', '6');

        // garantiamo l'uso di un algoritmo di hashing bello robusto per generare i nostri id di sessione
        ini_set('session.hash_function', 'sha256');
    }
}