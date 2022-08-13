<?php
/**
 * Descrive le caratteristiche che una tipologia di algoritmo
 * deve implementare per poter estendere le funzionalità della
 * classe Crypter
 */
interface CrypterAlgo {
    /**
     * Effettua la criptazione della stringa fornita e ritorna la sua forma codificata
     *
     * @param string $string la stringa in chiaro da criptare
     * @param string|null $key se l'algoritmo la supporta, si può specificare questo
     * parametro come chiave segreta di criptazione che servirà anche per la decriptazione
     * @param bool $raw posto su true permette di ottenere l'output binario della criptazione
     * se supportato
     * @return string
     */
    public function crypt(string $string, ?string $key = null, bool $raw = false): string;

    /**
     * Effettua la decriptazione del messaggio fornito e ritorna il contenuto originale
     *
     * @param string $crypted la stringa criptata in precedenza
     * @param string $key la chiave di decriptazione usata per la stringa
     * @return false|string ritorna il contenuto originale decriptato oppure FALSE se
     * l'algoritmo di criptazione in uso non è di tipo reversibile
     */
    public function decrypt(string $crypted, string $key): false|string;

    /**
     * Permette di sapere se una criptazione appartiene alla versione in chiaro fornita
     *
     * @param string $crypted la stringa criptata in precedenza
     * @param string $string il valore in chiaro da verificare
     * @param string|null $key se l'algoritmo la supporta, occorre specificare la chiave
     * segreta usata per la criptazione
     * @return bool true se la versione criptata appartiene alla stringa in chiaro fornita
     */
    public function verify(string $crypted, string $string, ?string $key = null): bool;

    /**
     * Indica se la criptazione fornita usa criteri di sicurezza non aggiornati
     *
     * @param string $crypted la stringa criptata
     * @return bool false significa che i criteri di sicurezza sono aggiornati, true
     * che la criptazione andrebbe rieseguita per garantire una protezione migliore
     */
    public function needsNewEncryption(string $crypted): bool;
}

/**
 * Permette di criptare stringhe con uno specifico algoritmo e
 * permette di fare confronti tra hash o decriptazione a seconda
 * dello specifico caso.
 */
class Crypter extends BaseClass {
    protected string $engine;
    protected CrypterAlgo $CrypterAlgo;

    /**
     * Avvia le procedure necessarie per inizializzare la classe
     * L'algoritmo utilizzato di default viene prelevato dalla costante
     * nel db "CRYPTER_ALGO_DEFAULT", se non diversamente indicato.
     *
     * @param string|null $CrypterAlgo facoltativamente si puà indicare
     * uno specifico algoritmo tra quelli disponibili con cui inizializzare
     * la classe, questo sovrascriverà l'impostazione di default
     */
    public function __construct(?string $CrypterAlgo = null)
    {
        parent::__construct();
        $CrypterAlgo ??= Functions::get_constant('CRYPTER_ALGO_DEFAULT');
        $this->engine = $CrypterAlgo;

        try {
            $this->CrypterAlgo = $CrypterAlgo::getInstance();
        } catch (Throwable $e) {
            error_log((string)$e);
            die('CRYPTER_ALGO_NOT_EXISTS');
        }
    }

    /**
     * Genera una nuova istanza della classe configurata con l'algoritmo scelto
     * @param string $CrypterAlgo
     * @return Crypter
     */
    public function withAlgo(string $CrypterAlgo): Crypter {
        return new Crypter($CrypterAlgo);
    }

    /**
     * Effettua la criptazione della stringa fornita e ritorna la sua forma codificata
     *
     * @param string $string la stringa in chiaro da criptare
     * @param string|null $key se l'algoritmo la supporta, si può specificare questo
     * parametro come chiave segreta di criptazione che servirà anche per la decriptazione
     * @param bool $raw posto su true permette di ottenere l'output binario della criptazione
     * @return string
     */
    public function crypt(string $string, ?string $key = null, bool $raw = false): string {
        return $this->CrypterAlgo->crypt($string, $key, $raw);
    }

    /**
     * Effettua la decriptazione del messaggio fornito e ritorna il contenuto originale
     *
     * @param string $crypted la stringa criptata in precedenza
     * @param string $key la chiave di decriptazione usata per la stringa
     * @return false|string ritorna il contenuto originale decriptato oppure FALSE se
     * l'algoritmo di criptazione in uso non è di tipo reversibile
     */
    public function decrypt(string $crypted, string $key): false|string {
        return $this->CrypterAlgo->decrypt($crypted, $key);
    }

    /**
     * Permette di sapere se una criptazione appartiene alla versione in chiaro fornita
     *
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
     * Indica se la criptazione fornita usa criteri di sicurezza non aggiornati
     *
     * @param string $crypted la stringa criptata
     * @return bool false significa che i criteri di sicurezza sono aggiornati, true
     * che la criptazione andrebbe rieseguita per garantire una protezione migliore
     */
    public function needsNewEncryption(string $crypted): bool {
        return $this->CrypterAlgo->needsNewEncryption($crypted);
    }
}