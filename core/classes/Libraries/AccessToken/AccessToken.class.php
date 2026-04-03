<?php

/**
 * @class AccessToken
 * @note Permette la creazione, verifica e gestione di token di accesso usa e getta associati ad uno specifico account
 */
class AccessToken extends BaseClass
{
    const TYPE_PASSWORD_RECOVERY = 'recupero-password';
    const TYPE_EMAIL_UPDATE = 'cambio-email';

    protected ?string $token = null;
    protected ?string $accountId = null;
    protected ?string $type = null;
    protected ?string $data = null;
    protected ?int $expirationTime = null;

    /**
     * @fn fromToken
     * @note Ritorna un istanza di AccessToken
     * @param string $token token univoco di cui verificarne la validità
     * @return AccessToken
     * @throws Throwable
     */
    public static function fromToken(string $token): self
    {
        $recovery = DB::queryStmt(
            'SELECT personaggio, 
                    UNIX_TIMESTAMP(scadenza) AS timestamp_scadenza,
                    tipo,
                    dati
            FROM personaggio_tokens 
                INNER JOIN personaggio ON(personaggio.id = personaggio_tokens.personaggio)
            WHERE token = :token',
            ['token' => $token]
        );

        $tokenInstance = self::getInstance();

        if ($recovery->getNumRows()) {
            $tokenInstance->token = $token;
            $tokenInstance->accountId = $recovery['personaggio'];
            $tokenInstance->type = $recovery['tipo'];
            $tokenInstance->expirationTime = $recovery['timestamp_scadenza'];
            $tokenInstance->data = $recovery['dati'];
        }

        return $tokenInstance;
    }

    /**
     * @fn create
     * @note genera un nuovo token di accesso
     * @param int $accountId id dell'account a cui il token è associato
     * @param string $type tipologia di token, esistono costanti col prefisso TYPE_* da cui attingere per i tipi consentiti
     * @param int $ttl tempo di vita del token in secondi
     * @param string|null $data dati facoltativi ausiliari
     * @return AccessToken ritorna un istanza di AccessToken per il token appena creato
     * @throws Throwable
     */
    public static function create(int $accountId, string $type, int $ttl, ?string $data = null): self
    {
        self::assertAllowedType($type);

        // Genero un token casuale da 36 caratteri. random_bytes è crittograficamente sicuro, non serve altro
        $token = bin2hex(random_bytes(18));

        DB::queryStmt(
            'INSERT INTO personaggio_tokens (token, personaggio, tipo, scadenza, dati) 
                VALUES (:token, :personaggio, :tipo, :scadenza, :dati)',
            [
                'token' => $token,
                'personaggio' => $accountId,
                'tipo' => $type,
                'scadenza' => (new Datetime)->add(new DateInterval("PT{$ttl}S"))->format('Y-m-d H:i:s'),
                'dati' => $data
            ]
        );

        return self::fromToken($token);
    }

    /**
     * @fn invalidate
     * @note Invalida il token aggiornando la sua data di scadenza a now()
     * @param string $token
     * @return void
     * @throws Throwable
     */
    public static function invalidate(string $token): void
    {
        DB::queryStmt(
            'UPDATE personaggio_tokens SET scadenza = NOW() WHERE token = :token',
            ['token' => $token]
        );
    }

    /**
     * @fn invalidate
     * @note invalida tutti i token di accesso associati ad $accountId
     * @param string $accountId
     * @param string|null $type se fornito invalida i token di accesso dello specifico tipo soltanto
     * @return void
     * @throws Throwable
     */
    public static function invalidateByAccountId(string $accountId, ?string $type = null): void
    {
        if (!is_null($type)) {
            self::assertAllowedType($type);
            DB::queryStmt(
                'UPDATE personaggio_tokens SET scadenza = NOW() WHERE personaggio = :personaggio AND tipo = :tipo',
                ['personaggio' => $accountId, 'tipo' => $type]
            );
            return;
        }

        DB::queryStmt(
            'UPDATE personaggio_tokens SET scadenza = NOW() WHERE personaggio = :personaggio',
            ['personaggio' => $accountId]
        );
    }

    /**
     * @fn delete
     * @note Elimina il token dal database
     * @param string $token
     * @return void
     * @throws Throwable
     */
    public static function delete(string $token): void
    {
        DB::queryStmt(
            'DELETE FROM personaggio_tokens WHERE token = :token',
            ['token' => $token]
        );
    }

    /**
     * @fn deleteByAccountId
     * @note elimina tutti i token di accesso associati ad $accountId
     * @param string $accountId
     * @param string|null $type se fornito elimina i token di accesso dello specifico tipo soltanto
     * @return void
     * @throws Throwable
     */
    public static function deleteByAccountId(string $accountId, ?string $type = null): void
    {
        if (!is_null($type)) {
            self::assertAllowedType($type);
            DB::queryStmt(
                'DELETE FROM personaggio_tokens WHERE personaggio = :personaggio AND tipo = :tipo',
                ['personaggio' => $accountId, 'tipo' => $type]
            );
            return;
        }

        DB::queryStmt(
            'DELETE FROM personaggio_tokens WHERE personaggio = :personaggio',
            ['personaggio' => $accountId]
        );
    }

    /**
     * @fn Asserisce che $type abbia un valore consentito, lancia un eccezione di tipo ValueError altrimenti
     * @param string $type
     * @return void
     */
    protected static function assertAllowedType(string $type): void
    {
        switch ($type) {
            case self::TYPE_PASSWORD_RECOVERY:
            case self::TYPE_EMAIL_UPDATE:
                break;
            default:
                throw new ValueError('Tipo token sconosciuto: '. $type);
        }
    }

    /**
     * @fn isValid
     * @param string $type la tipologia di accesso prevista per token
     * @return bool true se il token è valido, false altrimenti
     */
    public function isValid(string $type): bool
    {
        self::assertAllowedType($type);
        return !is_null($this->token)
            && $this->getExpirationTime() > time()
            && $this->getType() === $type;
    }

    /**
     * @fn toString
     * @note ritorna il token
     * @return string|null
     */
    public function toString(): ?string
    {
        return $this->token;
    }

    /**
     * @fn getAccountId
     * @note ritorna l'id dell'account a cui il token è associato
     * @return int|null
     */
    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    /**
     * @fn getType
     * @return string|null tipologia di token
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @fn getData
     * @return string|null dati ausiliari associati al token
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @fn getExpirationTime
     * @return int|null timestamp di scadenza del token
     */
    public function getExpirationTime(): ?int
    {
        return $this->expirationTime;
    }
}