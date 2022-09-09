<?php

/**
 * @class RecuperoPassword
 * @note Questo controller serve a gestire le operazioni e i form necessari per il recupero password
 */
class RecuperoPassword extends BaseClass
{

    /**
     * @fn renderRecoveryMailSentStatus
     * @note renderizza un messaggio che informa che la mail è stata spedita all'account
     * @return string
     */
    public static function renderRecoveryMailSentStatus(): string
    {
        // ...
    }

    /**
     * @fn renderRecoveryMailUnknownStatus
     * @note renderizza un messaggio che informa che la mail non è stata trovata nel db
     * @return string
     */
    public static function renderRecoveryMailUnknownStatus(): string
    {
        // ...
    }

    /**
     * @fn sendRecoveryEmail
     * @note Verifica se la mail esiste nel db e provvede all'invio di un link per il recupero password
     * @param string $userEmail
     * @return bool true se la mail è stata inviata, false altrimenti
     */
    public static function sendRecoveryEmail(string $userEmail): bool
    {
        // Genera un token con makePasswordRecoveryToken() e invialo per email a $userEmail
        // magari il testo della mail può essere salvato in una configurazione da DB
    }

    /**
     * @fn renderPassowrdUpdatedStatus
     * @note renderizza un messaggio di riuscita dell'operazione di aggiornamento password
     * @return string
     */
    public static function renderPassowrdUpdatedStatus(): string
    {
        // ...
    }

    /**
     * @fn renderInvalidTokenStatus
     * @note renderizza un messaggio d'errore che informa del fatto che il token per l'aggiornamento della password è scaduto
     * @return string
     */
    public static function renderInvalidTokenStatus(): string 
    {
        // ...
    }

    /**
     * @fn updateUserPasswordFromToken
     * @note verifica che recoveryToken sia valido e aggiorna la password all'utente associato
     * @param string $recoveryToken
     * @param string $newPassword
     * @return void
     * @throws Exception lancia un eccezione se il token è invalido
     */
    public static function updateUserPasswordFromToken(string $recoveryToken, string $newPassword): void
    {
        // ...
    }

    /**
     * @fn makePasswordRecoveryToken
     * @note Chiede gi generare un nuovo token di recupero password che sarà inviato per mail all'utente
     * @note Il token deve avere validità di sessione di N ore ( definito da una costante sul db )
     * @note Se viene richiesto un nuovo token, il precedente viene immediatamente invalidato
     * @param string $userEmail
     * @return string
     */
    protected static function makePasswordRecoveryToken(string $userEmail): string
    {
        // ...
    }
}