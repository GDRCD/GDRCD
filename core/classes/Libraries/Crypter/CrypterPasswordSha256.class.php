<?php

/**
 * @class CrypterPasswordSha256
 * @note Implementa l'algoritmo sha256 col metodo PBKDF2 per la classe Crypter
 * @note Questo algoritmo è specifico per la creazione di hash di password adatti all'archiviazione
 * @link https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html#pbkdf2
 */
class CrypterPasswordSha256 extends CrypterPasswordSha512
{
    /**
     * @var string
     * @note Il nome interno usato da php per riconoscere la funzione di hashing
     */
    const ALGO = 'sha256';

    /**
     * @var int
     * @note Rappresenta il numero di rounds di criptazione per garantire la sicurezza dell'hash
     */
    const COST = 310_000;
}