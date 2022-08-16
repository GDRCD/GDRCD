<?php
/**
 * @class CrypterPasswordBlowfish
 * @note Implementa l'algoritmo Blowfish per la classe Crypter
 * @note Questo algoritmo è specifico per la creazione di hash di password adatti all'archiviazione
 */
class CrypterPasswordBlowfish extends BaseClass implements CrypterAlgo
{
    public function __construct()
    {
        parent::__construct();

        if (!defined('PASSWORD_BCRYPT')) {
            throw new RuntimeException('Blowfish non è disponibile');
        }
    }

    /**
     * @fn options
     * @note Ritorna un array con la regolazione dei criteri di sicurezza dell'algoritmo
     * @return array
     */
    private function options(): array {
        return ['cost' => PASSWORD_BCRYPT_DEFAULT_COST];
    }

    /**
     * @fn normalizeInput
     * @note Normalizza la stringa di input prima di codificarla.
     * @note La normalizzazione si rende necessaria nel momento in cui viene fornito
     * @note un input di lunghezza superiore ai 72 bytes a causa di un limite del
     * @note cifratore di blowfish. La soluzione prevede di effettuare un pre-hash
     * @note delle stringhe troppo lunghe di modo da non superare i 72 bytes di limite.
     * @note Questo sistema fa perdere un po' di entropia per le password che innescano
     * @note la normalizzazione ma è perfettamente sicuro in termini pratici.
     * @link https://blog.ircmaxell.com/2015/03/security-issue-combining-bcrypt-with.html
     * @param string $string
     * @return string
     */
    private function normalizeInput(string $string): string {
        return mb_strlen($string) <= 72? $string : base64_encode(hash('sha512', $string, true));
    }

    public function crypt(string $string, ?string $key = null, bool $raw = false): string {
        return password_hash($this->normalizeInput($string), PASSWORD_BCRYPT, $this->options());
    }

    public function decrypt(string $crypted, string $key): false|string {
        return false;
    }

    public function verify(string $crypted, string $string, ?string $key = null): bool {
        return password_verify($this->normalizeInput($string), $crypted);
    }

    public function needsNewEncryption(string $crypted): bool {
        return password_needs_rehash($crypted, PASSWORD_BCRYPT, $this->options());
    }
}