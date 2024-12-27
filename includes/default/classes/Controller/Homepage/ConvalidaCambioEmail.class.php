<?php

class ConvalidaCambioEmail extends BaseClass
{
    /**
     * @fn render
     * @note entry point del modulo recupero password, renderizza il form o messaggi di errore
     * @return string
     * @throws Throwable
     */
    public static function render(): string
    {
        $token = Filters::in($_GET['token']?? '');

        try {

            ModificaEmail::getInstance()->updateEmail($token);
            return self::renderEmailUpdatedStatus();

        } catch (UnknownTokenException $e) {

            return self::renderInvalidTokenStatus();

        } catch (UnexpectedValueException $e) {

            return self::renderEmailAlreadyUsedStatus();

        }

        return self::renderInternalError();
    }

    /**
     * @fn renderEmailUpdatedStatus
     * @note renderizza un messaggio di riuscita dell'operazione di aggiornamento email
     * @return string
     * @throws Throwable
     */
    public static function renderEmailUpdatedStatus(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'homepage/email/verification_status', [
            'pagetitle' => $GLOBALS['MESSAGE']['interface']['user']['email']['page_name'],
            'message' => $GLOBALS['MESSAGE']['interface']['user']['email']['success'],
        ]);
    }

    /**
     * @fn renderInvalidTokenStatus
     * @note renderizza un messaggio d'errore che informa del fatto che il token per l'aggiornamento della mail è invalido
     * @return string
     * @throws Throwable
     */
    public static function renderInvalidTokenStatus(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'homepage/email/verification_status', [
            'pagetitle' => $GLOBALS['MESSAGE']['interface']['user']['email']['page_name'],
            'message' => $GLOBALS['MESSAGE']['recoverypassword']['status']['invalidtoken']['text'],
        ]);
    }

    /**
     * @fn renderEmailAlreadyUsedStatus
     * @note renderizza un messaggio d'errore che informa del fatto che la mail è già in uso
     * @return string
     * @throws Throwable
     */
    public static function renderEmailAlreadyUsedStatus(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'homepage/email/verification_status', [
            'pagetitle' => $GLOBALS['MESSAGE']['interface']['user']['email']['page_name'],
            'message' => $GLOBALS['MESSAGE']['interface']['user']['email']['already-exists'],
        ]);
    }

    /**
     * @fn renderInternalError
     * @note Per problemi interni non è stato possibile aggiornare l'indirizzo email
     * @return string
     * @throws Throwable
     */
    public static function renderInternalError(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'homepage/email/verification_status', [
            'pagetitle' => $GLOBALS['MESSAGE']['interface']['user']['email']['page_name'],
            'message' => $GLOBALS['MESSAGE']['warning']['cant_do'],
        ]);
    }
}