<?php

/**
 * @class CrypterPasswordSha512
 * @note Implementa l'algoritmo sha512 col metodo PBKDF2 per la classe Crypter
 * @note Questo algoritmo è specifico per la creazione di hash di password adatti all'archiviazione
 * @link https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html#pbkdf2
 */
class CrypterPasswordSha512 extends CrypterAlgo implements CrypterInterface
{
    /**
     * @var string
     * @note Il nome interno usato da php per riconoscere la funzione di hashing
     */
    const ALGO = 'sha512';

    /**
     * @var int
     * @note Rappresenta il numero di rounds di criptazione per garantire la sicurezza dell'hash
     */
    const COST = 120_000;

    /*
     * @fn __construct
     * @note Costruttore della classe
     */
    public function __construct()
    {
        parent::__construct();

        if ( !in_array(static::ALGO, hash_algos(), true) ) {
            throw new RuntimeException(static::ALGO . ' non è disponibile');
        }
    }

    /**
     * @fn pbkdf2
     * @note Utilizza il metodo di criptaggio a derivazione di chiave (PBKDF2)
     * per produrre l'hash di una password robusta contro attacchi bruteforce
     * @param string $algo
     * @param string $password
     * @param string|null $salt
     * @param int $cost
     * @return string
     * @throws Exception
     */
    private function pbkdf2(string $algo, string $password, ?string $salt = null, int $cost = 10_000): string
    {
        $salt ??= random_bytes(32);
        return '$' . $algo
            . '$' . $cost
            . '$' . bin2hex($salt)
            . '$' . hash_pbkdf2($algo, $password, $salt, $cost, 128);
    }

    /**
     * @fn crypt
     * @note Codifica la stringa d'ingresso con l'algoritmo sha512.
     * @param string $string
     * @param string|null $key
     * @param bool $raw
     * @return string
     * @throws Exception
     */
    public function crypt(string $string, ?string $key = null, bool $raw = false): string
    {
        return $this->pbkdf2(static::ALGO, $string, null, static::COST);
    }

    /**
     * @fn decrypt
     * @note Decodifica la stringa d'ingresso con l'algoritmo sha512.
     * @param string $crypted
     * @param string $key
     * @return false|string
     */
    public function decrypt(string $crypted, string $key): false|string
    {
        return false;
    }

    /**
     * @fn verify
     * @note Verifica la correttezza della stringa d'ingresso con l'algoritmo sha512.
     * @param string $crypted
     * @param string $string
     * @param string|null $key
     * @return bool
     * @throws Exception
     */
    public function verify(string $crypted, string $string, ?string $key = null): bool
    {
        $hashParts = explode('$', $crypted);
        $newHash = $this->pbkdf2($hashParts[1], $string, hex2bin($hashParts[3]), (int)$hashParts[2]);
        return hash_equals($crypted, $newHash);
    }

    /**
     * @fn needsNewEncryption
     * @note Verifica se la stringa d'ingresso deve essere criptata con una nuova chiave.
     * @param string $crypted
     * @return bool
     */
    public function needsNewEncryption(string $crypted): bool
    {
        return (int)explode('$', $crypted)[2] < static::COST;
    }
}