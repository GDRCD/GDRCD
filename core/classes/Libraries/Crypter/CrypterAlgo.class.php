<?php

/**
 * @interface CrypterInterface
 * @note Descrive le caratteristiche che una tipologia di algoritmo
 * @note Deve implementare per poter estendere le funzionalità della
 * @note classe CrypterAlgo
 */
interface CrypterInterface
{
    /**
     * @fn crypt
     * @note Effettua la criptazione della stringa fornita e ritorna la sua forma codificata
     * @param string $string La stringa in chiaro da criptare
     * @param string|null $key Chiave di criptazione
     * @param bool $raw Se true ritorna la stringa codificata in base 64
     * @return string
     */
    public function crypt(string $string, ?string $key = null, bool $raw = false): string;

    /**
     * @fn decrypt
     * @note Effettua la decriptazione del messaggio fornito e ritorna il contenuto originale
     * @param string $crypted La stringa criptata in precedenza
     * @param string $key Chiave di criptazione
     * @return false|string Se la decriptazione è andata a buon fine ritorna il contenuto originale, altrimenti false
     */
    public function decrypt(string $crypted, string $key): false|string;

    /**
     * @fn verify
     * @note Permette di sapere se una criptazione risulta essere il match alla versione in chiaro fornita
     * @param string $crypted La stringa criptata in precedenza
     * @param string $string Il valore in chiaro da verificare
     * @param string|null $key Chiave di criptazione
     * @return bool True se la criptazione è corretta, false altrimenti
     */
    public function verify(string $crypted, string $string, ?string $key = null): bool;

    /**
     * @fn needsNewEncryption
     * @note Indica se la criptazione fornita usa criteri di sicurezza non aggiornati
     * @param string $crypted la stringa criptata
     * @return bool True se la criptazione è obsoleta, false altrimenti
     */
    public function needsNewEncryption(string $crypted): bool;
}

/**
 * @class CrypterAlgo
 * @note Permette di criptare stringhe con uno specifico algoritmo e
 * @note Permette di fare confronti tra hash o decriptazione a seconda dello specifico caso.
 */
class CrypterAlgo extends BaseClass
{
    protected CrypterInterface $CrypterAlgo;

    /**
     * @fn init
     * @note Seleziona l'algoritmo di criptazione da utilizzare
     * @param string $CrypterAlgo Il nome dell'algoritmo da usare
     * @return void
     */
    public function init(string $CrypterAlgo): void
    {
        try {
            $this->CrypterAlgo = $CrypterAlgo::getInstance();
        } catch ( Throwable $e ) {
            error_log((string)$e);
            die('SECURITY_CRYPTER_NOT_EXISTS');
        }
    }

    /**
     * @fn withAlgo
     * @note Genera una nuova istanza della classe configurata con l'algoritmo scelto
     * @param string $CrypterAlgo
     * @return CrypterAlgo
     */
    public static function withAlgo(string $CrypterAlgo): CrypterAlgo
    {
        $Crypter = new CrypterAlgo();
        $Crypter->init($CrypterAlgo);
        return $Crypter;
    }

    /**
     * @fn crypt
     * @note Effettua la criptazione della stringa fornita e ritorna la sua forma codificata
     * @param string $string La stringa in chiaro da criptare
     * @param string|null $key Chiave di criptazione
     * @param bool $raw Se true ritorna la stringa codificata in base 64
     * @return string La stringa criptata
     */
    public function crypt(string $string, ?string $key = null, bool $raw = false): string
    {
        return $this->CrypterAlgo->crypt($string, $key, $raw);
    }

    /**
     * @fn decrypt
     * @note Effettua la decriptazione del messaggio fornito e ritorna il contenuto originale
     * @param string $crypted La stringa criptata in precedenza
     * @param string $key Chiave di criptazione
     * @return false|string Se la decriptazione è andata a buon fine ritorna il contenuto originale, altrimenti false
     */
    public function decrypt(string $crypted, string $key): false|string
    {
        return $this->CrypterAlgo->decrypt($crypted, $key);
    }

    /**
     * @fn verify
     * @note Permette di sapere se una criptazione risulta essere il match alla versione in chiaro fornita
     * @param string $crypted La stringa criptata in precedenza
     * @param string $string Il valore in chiaro da verificare
     * @param string|null $key Chiave di criptazione
     * @return bool True se la criptazione è corretta, false altrimenti
     */
    public function verify(string $crypted, string $string, ?string $key = null): bool
    {
        return $this->CrypterAlgo->verify($crypted, $string, $key);
    }

    /**
     * @fn needsNewEncryption
     * @note Indica se la criptazione fornita usa criteri di sicurezza non aggiornati
     * @param string $crypted La stringa criptata
     * @return bool True se la criptazione è obsoleta, false altrimenti
     */
    public function needsNewEncryption(string $crypted): bool
    {
        return $this->CrypterAlgo->needsNewEncryption($crypted);
    }
}