<?php
/**
 * @interface CrypterAlgo
 * @note Descrive le caratteristiche che una tipologia di algoritmo
 * @note deve implementare per poter estendere le funzionalità della
 * @note classe Crypter
 */
interface CrypterAlgo {
    /**
     * @fn crypt
     * @note Effettua la criptazione della stringa fornita e ritorna la sua forma codificata
     * @param string $string la stringa in chiaro da criptare
     * @param string|null $key se l'algoritmo la supporta, si può specificare questo
     * parametro come chiave segreta di criptazione che servirà anche per la decriptazione
     * @param bool $raw posto su true permette di ottenere l'output binario della criptazione
     * se supportato
     * @return string
     */
    public function crypt(string $string, ?string $key = null, bool $raw = false): string;

    /**
     * @fn decrypt
     * @note Effettua la decriptazione del messaggio fornito e ritorna il contenuto originale
     * @param string $crypted la stringa criptata in precedenza
     * @param string $key la chiave di decriptazione usata per la stringa
     * @return false|string ritorna il contenuto originale decriptato oppure FALSE se
     * l'algoritmo di criptazione in uso non è di tipo reversibile
     */
    public function decrypt(string $crypted, string $key): false|string;

    /**
     * @fn verify
     * @note Permette di sapere se una criptazione risulta essere il match alla versione in chiaro fornita
     * @param string $crypted la stringa criptata in precedenza
     * @param string $string il valore in chiaro da verificare
     * @param string|null $key se l'algoritmo la supporta, occorre specificare la chiave
     * segreta usata per la criptazione
     * @return bool true se la versione criptata appartiene alla stringa in chiaro fornita
     */
    public function verify(string $crypted, string $string, ?string $key = null): bool;

    /**
     * @fn needsNewEncryption
     * @note Indica se la criptazione fornita usa criteri di sicurezza non aggiornati
     * @param string $crypted la stringa criptata
     * @return bool false significa che i criteri di sicurezza sono aggiornati, true
     * che la criptazione andrebbe rieseguita per garantire una protezione migliore
     */
    public function needsNewEncryption(string $crypted): bool;
}

/**
 * @class Crypter
 * @note Permette di criptare stringhe con uno specifico algoritmo e
 * @note permette di fare confronti tra hash o decriptazione a seconda
 * @note dello specifico caso.
 */
class Crypter extends BaseClass {
    protected CrypterAlgo $CrypterAlgo;

    /**
     * @fn __construct
     * @note Inizializza la classe con l'algoritmo selezionato
     * @param string $CrypterAlgo il nome dell'implementazione dell'algoritmo
     * che estende l'interfaccia CrypterAlgo con cui inizializzare la classe.
     */
    public function __construct(string $CrypterAlgo)
    {
        parent::__construct();

        try {
            $this->CrypterAlgo = $CrypterAlgo::getInstance();
        } catch (Throwable $e) {
            error_log((string)$e);
            die('SECURITY_CRYPTER_NOT_EXISTS');
        }
    }

    /**
     * @fn withAlgo
     * @note Genera una nuova istanza della classe configurata con l'algoritmo scelto
     * @param string $CrypterAlgo
     * @return Crypter
     */
    public static function withAlgo(string $CrypterAlgo): Crypter {
        return new Crypter($CrypterAlgo);
    }

    /**
     * @fn crypt
     * @note Effettua la criptazione della stringa fornita e ritorna la sua forma codificata
     * @param string $string la stringa in chiaro da criptare
     * @param string|null $key se l'algoritmo la supporta, si può specificare questo
     * parametro come chiave segreta di criptazione che servirà anche per la decriptazione
     * @param bool $raw posto su true permette di ottenere l'output binario della criptazione
     * se supportato
     * @return string
     */
    public function crypt(string $string, ?string $key = null, bool $raw = false): string {
        return $this->CrypterAlgo->crypt($string, $key, $raw);
    }

    /**
     * @fn decrypt
     * @note Effettua la decriptazione del messaggio fornito e ritorna il contenuto originale
     * @param string $crypted la stringa criptata in precedenza
     * @param string $key la chiave di decriptazione usata per la stringa
     * @return false|string ritorna il contenuto originale decriptato oppure FALSE se
     * l'algoritmo di criptazione in uso non è di tipo reversibile
     */
    public function decrypt(string $crypted, string $key): false|string {
        return $this->CrypterAlgo->decrypt($crypted, $key);
    }

    /**
     * @fn verify
     * @note Permette di sapere se una criptazione risulta essere il match alla versione in chiaro fornita
     * @param string $crypted la stringa criptata in precedenza
     * @param string $string il valore in chiaro da verificare
     * @param string|null $key se l'algoritmo la supporta, occorre specificare la chiave
     * segreta usata per la criptazione
     * @return bool true se la versione criptata appartiene alla stringa in chiaro fornita
     */
    public function verify(string $crypted, string $string, ?string $key = null): bool {
        return $this->CrypterAlgo->verify($crypted, $string, $key);
    }

    /**
     * @fn needsNewEncryption
     * @note Indica se la criptazione fornita usa criteri di sicurezza non aggiornati
     * @param string $crypted la stringa criptata
     * @return bool false significa che i criteri di sicurezza sono aggiornati, true
     * che la criptazione andrebbe rieseguita per garantire una protezione migliore
     */
    public function needsNewEncryption(string $crypted): bool {
        return $this->CrypterAlgo->needsNewEncryption($crypted);
    }
}