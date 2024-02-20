<?php

/**
 * @class CrypterSha256
 * @note Implementa l'algoritmo sha256 puro
 * @note Può essere usato per generare token di vario tipo o per l'offuscamento di dati sensibili
 * @note DA NON USARE MAI PER ARCHIVIARE PASSWORD
 */
class CrypterSha256 extends CrypterAlgo implements CrypterInterface
{
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
        return hash('sha256', $string, $raw);
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
        return hash_equals($crypted, $this->crypt($string));
    }

    /**
     * @fn needsNewEncryption
     * @note Verifica se la stringa d'ingresso deve essere criptata con una nuova chiave.
     * @param string $crypted
     * @return bool
     */
    public function needsNewEncryption(string $crypted): bool
    {
        return false;
    }
}