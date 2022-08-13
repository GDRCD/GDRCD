<?php
/**
 * Questa classe permette di generare e verificare password
 * utente per l'autenticazione al sito
 */
final class Password extends BaseClass {
    /**
     * @var string|null contiene l'algoritmo di hashing attualmente
     * selezionato per il criptaggio delle password
     */
    private static ?string $algorithm = null;

    /**
     * Ritorna l'algoritmo attualmente selezionato per il
     * criptaggio delle password
     * @return string
     */
    public static function getAlgo(): string {
        return self::$algorithm;
    }

    /**
     * Permette di cambiare l'algoritmo di hashing in uso per
     * il criptaggio delle password
     * @param string $algo
     * @return void
     */
    public static function setAlgo(string $algo): void
    {
        // TODO
    }

    /**
     * Ritorna l'elenco degli algoritmi di hashing disponibili
     * per il criptaggio delle password. L'elenco è ordinato
     * a partire dagli algoritmi più robusti fino a quelli
     * più deboli. Detto questo, tutti gli algoritmi in questo
     * elenco restano opzioni valide tra cui scegliere
     * @return array
     */
    public static function getAvailableAlgos(): array {
        // TODO
    }

    /**
     * Genera l'hash della password fornita come parametro e la ritona
     * @param string $password
     * @return string
     * @throws Exception
     */
    public static function hash(string $password): string
    {
        // TODO
    }

    public static function verify(): bool
    {
        // TODO
    }

    public static function needsRehash(): bool
    {
        // TODO
    }
}