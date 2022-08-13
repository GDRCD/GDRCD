<?php
/**
 * Implementa l'algoritmo sha256 col metodo PBKDF2 per la classe Crypter
 * Questo algoritmo è specifico per la generazione di hash ad uso
 * archiviazione e confronto password per sistemi di autenticazione.
 *
 * @link https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html#pbkdf2
 */
class CrypterPasswordSha256 extends CrypterPasswordSha512 {
    const ALGO = 'sha256';
    const COST = 310_000;
}