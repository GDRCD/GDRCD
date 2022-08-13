<?php
/**
 * Implementa l'algoritmo Argon2 per la classe Crypter
 * Questo algoritmo è specifico per la generazione di hash
 * ad uso archiviazione e confronto password per sistemi di
 * autenticazione.
 */
class CrypterPasswordArgon2 extends BaseClass implements CrypterAlgo
{
    public function __construct()
    {
        parent::__construct();

        if (!defined('PASSWORD_ARGON2ID')) {
            throw new RuntimeException('Argon2 non è disponibile');
        }
    }

    /**
     * Ritorna un array con la regolazione dei criteri di sicurezza dell'algoritmo
     * @return array
     */
    private function options(): array {
        return [
            'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
            'time_cost' => PASSWORD_ARGON2_DEFAULT_TIME_COST,
            'threads' => PASSWORD_ARGON2_DEFAULT_THREADS
        ];
    }

    public function crypt(string $string, ?string $key = null, bool $raw = false): string {
        return password_hash($string, PASSWORD_ARGON2ID, $this->options());
    }

    public function decrypt(string $crypted, string $key): false|string {
        return false;
    }

    public function verify(string $crypted, string $string, ?string $key = null): bool {
        return password_verify($string, $crypted);
    }

    public function needsNewEncryption(string $crypted): bool {
        return password_needs_rehash($crypted, PASSWORD_ARGON2ID, $this->options());
    }
}