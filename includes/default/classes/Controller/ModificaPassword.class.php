<?php

/**
 * @class ModificaPassword
 * @note Questo controller serve a gestire la pagina per la modifica password utente interna al sito
 */
class ModificaPassword extends BaseClass
{
    /**
     * @fn renderEditForm
     * @note si occupa di renderizzare il form di modifica password
     * @return string
     */
    public static function renderEditForm(): string
    {
        // ...
    }

    /**
     * @fn renderSuccess
     * @note renderizza un template che si occupa di informare l'utente dell'esito positivo dell'operazione
     * @return string
     */
    public static function renderSuccess(): string
    {
        // ...
    }

    /**
     * @fn renderFailure
     * @note renderizza un template che si occupa di informare l'utente dell'insuccesso dell'operazione
     * @return string
     */
    public static function renderFailure(): string
    {
        // ...
    }

    /**
     * @fn updateUserPassword
     * @note Verifica la correttezza delle informazioni immesse e aggiorna la password dell'utente.
     * @param string $userEmail l'email che identifica la user di cui aggiornare la password
     * @param string $oldPassword la password in questo momento associata alla user identificata dall'email
     * @param string $newPassword la nuova password da aggiornare per la user
     * @return void
     * @throws Exception eccezione con la descrizione dell'errore se le informazioni fornite non corisspondono
     */
    public static function updateUserPassword(string $userEmail, string $oldPassword, string $newPassword): void
    {
        // ...
    }
}