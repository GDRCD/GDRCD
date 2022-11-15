<?php

/**
 * @class CrypterPasswordBlowfish
 * @note Implementa l'algoritmo Blowfish per la classe Crypter
 * @note Questo algoritmo è specifico per la creazione di hash di password adatti all'archiviazione
 */
class CrypterPasswordBlowfish extends CrypterAlgo implements CrypterInterface
{
    /**
     * @fn __construct
     * @note Costruttore della classe
     */
    public function __construct()
    {
        parent::__construct();

        if ( !defined('PASSWORD_BCRYPT') ) {
            throw new RuntimeException('Blowfish non è disponibile');
        }
    }

    /**
     * @fn options
     * @note Ritorna un array con la regolazione dei criteri di sicurezza dell'algoritmo
     * @return array
     */
    private function options(): array
    {
        return ['cost' => PASSWORD_BCRYPT_DEFAULT_COST];
    }

    /**
     * @fn normalizeInput
     * @note Normalizza la stringa d'input prima di codificarla.
     * @param string $string
     * @return string
     */
    private function normalizeInput(string $string): string
    {
        return mb_strlen($string) <= 72 ? $string : base64_encode(hash('sha512', $string, true));
    }

    /**
     * @fn crypt
     * @note Codifica la stringa d'ingresso con l'algoritmo Blowfish.
     * @param string $string
     * @param string|null $key
     * @param bool $raw
     * @return string
     */
    public function crypt(string $string, ?string $key = null, bool $raw = false): string
    {
        return password_hash($this->normalizeInput($string), PASSWORD_BCRYPT, $this->options());
    }

    /**
     * @fn decrypt
     * @note Decodifica la stringa d'ingresso con l'algoritmo Blowfish.
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
     * @note Verifica la correttezza della stringa d'ingresso con l'algoritmo Blowfish.
     * @param string $crypted
     * @param string $string
     * @param string|null $key
     * @return bool
     */
    public function verify(string $crypted, string $string, ?string $key = null): bool
    {
        return password_verify($this->normalizeInput($string), $crypted);
    }

    /**
     * @fn needsNewEncryption
     * @note Verifica se la stringa d'input deve essere codificata con un nuovo hash.
     * @param string $crypted
     * @return bool
     */
    public function needsNewEncryption(string $crypted): bool
    {
        return password_needs_rehash($crypted, PASSWORD_BCRYPT, $this->options());
    }
}