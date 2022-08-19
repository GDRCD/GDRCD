<?php
/**
 * @class Session
 * @note Classe responsabile per la gestione della sessione utente nel sito
 */
class Session
{
    /**
     * @fn start
     * @note Inizializza la sessione e popola i dati di
     * @note Sessione eventualmente associati all'utente connesso
     * @param bool $readAndClose Se true chiude la sessione dopo averla letta
     * @return void
     */
    public static function start(bool $readAndClose = false): void
    {
        self::secureSessionConfiguraton();
        session_start(['read_and_close' => $readAndClose]);
    }

    /**
     * @fn reset
     * @note Ripristina nei dati in sessione i valori che erano presenti all'avvio
     * @return void
     */
    public static function reset(): void {
        session_reset();
    }

    /**
     * @fn commit
     * @note Salva eventuali modifiche appese, rilascia il lock e chiude la sessione
     * @return void
     */
    public static function commit(): void {
        session_write_close();
    }

    /**
     * @fn abort
     * @note Scarta le modifiche locali, rilascia il lock e chiude la sessione
     * @return void
     */
    public static function abort(): void {
        session_abort();
    }

    /**
     * @fn read
     * @note Legge e ritorna il valore in sessione associato alla chiave indicata
     * @param string|null $key Se non specificata alcuna chiave ritorna l'intero contenuto in sessione come array
     * @return mixed
     */
    public static function read(?string $key = null): mixed {
        return is_null($key)? $_SESSION : ($_SESSION[$key]?? null);
    }

    /**
     * @fn save
     * @note Memorizza localmente le modifiche in sessione
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function store(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    /**
     * @fn login
     * @note Verifica che la coppia $username e $password sia valida e valorizza le informazioni in sessione
     * @param string $username La username proveniente dal form di login
     * @param string $password
     * @return bool
     * @throws Throwable
     */
    public static function login(string $username, string $password): bool
    {
        $User = DB::queryStmt(
            'SELECT personaggio.id, 
                    personaggio.nome, 
                    personaggio.cognome,
                    personaggio.sesso,
                    personaggio.razza,
                    personaggio.pass, 
                    personaggio.permessi,
                    personaggio.ora_entrata,
                    personaggio.ora_uscita,
                    personaggio.ultimo_refresh,
                    personaggio.posizione,
                    personaggio.ultima_mappa,
                    personaggio.ultimo_luogo,
                    razze.sing_f,
                    razze.sing_m,
                    razze.icon AS icona_razza

            FROM personaggio 
                LEFT JOIN razze ON(personaggio.razza = razze.id)

            WHERE personaggio.nome = :username
                AND personaggio.permessi > -1',
            ['username' => $username]
        );

        if (count($User)) {
            if (Password::verify($User['pass'], $password, $User['id'])) {
                // Valorizzo la sessione se tutto ok
                self::store('login', $User['nome']);
                self::store('login_id', $User['id']);
                self::store('cognome', $User['cognome']);
                self::store('permessi', $User['permessi']);
                self::store('sesso', $User['sesso']);
                self::store('blocca_media', $User['blocca_media']);
                self::store('ultima_entrata', $User['ora_entrata']);
                self::store('ultima_uscita', $User['ora_uscita']);
                self::store('ultimo_refresh', $User['ultimo_refresh']);
                self::store('razza', $User['sing_'. $User['sesso']]?? $User['sing_m']);
                self::store('img_razza', $User['icona_razza']);
                self::store('id_razza', $User['razza']);
                self::store('posizione', $User['posizione']);
                self::store('mappa', empty($User['ultima_mappa'])? 1 : $User['ultima_mappa']);
                self::store('luogo', empty($User['ultimo_luogo'])? -1 : $User['ultimo_luogo']);
                self::store('tag', '');
                self::store('last_message', 0);

                return true;
            }
        }

        return false;
    }

    /**
     * @fn isLogged
     * @note indica se la sessione esiste ed è attualmente attiva
     * @return bool
     */
    public static function isLogged(): bool {
        return !is_null(self::read('login'));
    }

    /**
     * @fn secureSessionConfiguraton
     * @note Si occupa d'impostare correttamente tutti i valori di configurazione per garantire la sicurezza delle sessioni
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
        ini_set('session.cookie_secure', 'Off');

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