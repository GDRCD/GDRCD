<?php
/**
 * @class Password
 * @note Questa classe permette di generare e verificare password utente per l'autenticazione al sito
 */
final class Password extends BaseClass {
    /**
     * @var Crypter
     * @note istanza della classe Crypter configurata con l'algoritmo scelto da configurazione
     */
    private static Crypter $Crypter;

    /**
     * @var array
     * @note elenco degli algoritmi di hashing disponibili
     */
    private static array $availableAlgos = [];

    /**
     * @var string|null
     * @note contiene l'algoritmo di hashing attualmente selezionato per il criptaggio delle password
     */
    private static ?string $algorithm = null;

    /**
     * @fn init
     * @note inizializza le proprietà essenziali al funzionamento della classe se necessario
     * @return void
     * @throws Throwable
     */
    private static function init(): void
    {
        if (is_null(self::$Crypter)) {
            $DefaultCrypterAlgo = strtok(Functions::get_constant('SECURITY_PASSWORD_CRYPTER'), ',');
            self::$algorithm = $DefaultCrypterAlgo;
            self::$Crypter = Crypter::withAlgo($DefaultCrypterAlgo);

            foreach (Gestione::getInstance()->getOptions('PasswordHash') as $option) {
                [$CrypterName, $hashId] = explode(',', $option['value'], 2);
                self::$availableAlgos[$CrypterName] = $hashId;
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

        if (!isset(self::$availableAlgos[$algo])) {
            throw new InvalidArgumentException('Algoritmo di hashing sconosciuto "'. $algo .'"');
        }

        self::$algorithm = $algo;
        self::$Crypter = Crypter::withAlgo($algo);
    }

    /**
     * @fn getAvailableAlgos
     * @note Ritorna l'elenco degli algoritmi di hashing disponibili per il criptaggio delle password
     * @return array
     * @throws Throwable
     */
    public static function getAvailableAlgos(): array
    {
        self::init();
        return array_keys(self::$availableAlgos);
    }

    /**
     * @fn hash
     * @note Genera l'hash della password fornita come parametro e la ritona
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
     * @param int|null $idPersonaggio se specificato l'id di un personaggio la classe
     * si occuperà in autonomia di aggiornare i criteri di sicurezza della sua password
     * qualora obsoleti e solo in caso di verifica positiva
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
     * @note indica se i criteri di sicurezza della password risultano obsoleti
     * @param string $hash
     * @return bool true se la password usa criteri di sicurezza non aggiornati, false se tutto nella norma
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
     * @note identifica l'algoritmo Crypter usato per generare l'hash e lo ritorna
     * @param string $hash
     * @return Crypter
     */
    private static function getProperCrypter(string $hash): Crypter
    {
        $Crypter = self::$Crypter;
        $hashId = strtok($hash, '$');
        $CrypterAlgo = array_search($hashId, self::$availableAlgos, true);

        if ($CrypterAlgo !== false && self::$algorithm !== $CrypterAlgo) {
            $Crypter = Crypter::withAlgo($CrypterAlgo);
        }

        return $Crypter;
    }
}