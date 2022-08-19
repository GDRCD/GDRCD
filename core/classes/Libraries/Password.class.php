<?php
/**
 * @class Password
 * @note Questa classe permette di generare e verificare password utente per l'autenticazione al sito
 */
final class Password extends BaseClass {
    /**
     * @var null|CrypterAlgo
     * @note Istanza della classe Crypter configurata con l'algoritmo scelto da configurazione
     */
    private static ?CrypterAlgo $Crypter = null;

    /**
     * @var array
     * @note Elenco degli algoritmi di hashing disponibili
     */
    private static array $availableAlgo = [];

    /**
     * @var string|null
     * @note Contiene l'algoritmo di hashing attualmente selezionato per il criptaggio delle password
     */
    private static ?string $algorithm = null;

    /**
     * @fn init
     * @note Inizializza le proprietà essenziali al funzionamento della classe se necessario
     * @return void
     * @throws Throwable
     */
    private static function init(): void
    {
        if (is_null(self::$Crypter)) {
            $DefaultCrypterAlgo = strtok(Functions::get_constant('SECURITY_PASSWORD_CRYPTER'), ',');
            self::$algorithm = $DefaultCrypterAlgo;
            self::$Crypter = CrypterAlgo::withAlgo($DefaultCrypterAlgo);

            foreach (Gestione::getInstance()->getOptions('PasswordHash') as $option) {
                [$CrypterName, $hashId] = explode(',', $option['value'], 2);
                self::$availableAlgo[$CrypterName] = $hashId;
            }
        }
    }

    /**
     * @fn getAlgo
     * @note Ritorna l'algoritmo attualmente selezionato per il criptaggio delle password
     * @return string
     * @throws Throwable
     */
    public static function getAlgo(): string
    {
        self::init();
        return self::$algorithm;
    }

    /**
     * @fn setAlgo
     * @note Permette di cambiare l'algoritmo di hashing in uso per il criptaggio delle password
     * @param string $algo
     * @return void
     * @throws Throwable
     */
    public static function setAlgo(string $algo): void
    {
        self::init();

        if (!isset(self::$availableAlgo[$algo])) {
            throw new InvalidArgumentException('Algoritmo di hashing sconosciuto "'. $algo .'"');
        }

        self::$algorithm = $algo;
        self::$Crypter = CrypterAlgo::withAlgo($algo);
    }

    /**
     * @fn getAvailableAlgo
     * @note Ritorna l'elenco degli algoritmi di hashing disponibili per il criptaggio delle password
     * @return array
     * @throws Throwable
     */
    public static function getAvailableAlgo(): array
    {
        self::init();
        return array_keys(self::$availableAlgo);
    }

    /**
     * @fn hash
     * @note Genera l'hash della password fornita come parametro e la ritorna
     * @param string $password
     * @return string
     * @throws Throwable
     */
    public static function hash(string $password): string
    {
        self::init();
        return self::$Crypter->crypt($password);
    }

    /**
     * @fn verify
     * @note Confronta l'hash con la password in chiaro e indica se c'è un riscontro
     * @param string $hash la stringa rappresentante la password criptata
     * @param string $password la password in chiaro fornita al login
     * @param int|null $idPersonaggio l'id del personaggio che sta effettuando il login
     * @return bool true se la password coincide con l'hash, false altriemnti
     * @throws Throwable
     */
    public static function verify(string $hash, string $password, ?int $idPersonaggio = null): bool
    {
        self::init();

        if (self::getProperCrypter($hash)->verify($hash, $password)) {
            if (!is_null($idPersonaggio) && self::needsRehash($hash)) {
                DB::queryStmt(
                    'UPDATE personaggio SET pass = :newpass WHERE id = :id',
                    [
                        'newpass' => self::hash($password),
                        'id' => $idPersonaggio
                    ]
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @fn needsRehash
     * @note Indica se i criteri di sicurezza della password risultano obsoleti
     * @param string $hash
     * @return bool True se i criteri di sicurezza della password risultano obsoleti, false altrimenti
     * @throws Throwable
     */
    public static function needsRehash(string $hash): bool
    {
        self::init();

        if (self::getProperCrypter($hash) === self::$Crypter) {
            return self::$Crypter->needsNewEncryption($hash);
        }

        return true;
    }

    /**
     * @fn getProperCrypter
     * @note Identifica l'algoritmo Crypter usato per generare l'hash e lo ritorna
     * @param string $hash
     * @return CrypterAlgo
     */
    private static function getProperCrypter(string $hash): CrypterAlgo
    {
        $Crypter = self::$Crypter;
        $hashId = strtok($hash, '$');
        $CrypterAlgo = array_search($hashId, self::$availableAlgo, true);

        if ($CrypterAlgo !== false && self::$algorithm !== $CrypterAlgo) {
            $Crypter = CrypterAlgo::withAlgo($CrypterAlgo);
        }

        return $Crypter;
    }
}