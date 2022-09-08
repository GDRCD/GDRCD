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
    public static function render(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'modificapassword/edit-form', [
                'pagetitle' => $GLOBALS['MESSAGE']['interface']['user']['pass']['page_name'],
                'formlabel' => [
                    'email' => $GLOBALS['MESSAGE']['interface']['user']['pass']['email'],
                    'oldpass' => $GLOBALS['MESSAGE']['interface']['user']['pass']['old'],
                    'newpass' => $GLOBALS['MESSAGE']['interface']['user']['pass']['new'],
                    'repeatpass' => $GLOBALS['MESSAGE']['interface']['user']['pass']['repeat'],
                    'submit' => $GLOBALS['MESSAGE']['interface']['user']['pass']['submit']['user']
                ],
                'response' => [
                    'success' => $GLOBALS['MESSAGE']['warning']['modified'],
                    'error' => $GLOBALS['MESSAGE']['warning']['cant_do']
                ]
        ]);
    }

    /**
     * @fn updateUserPassword
     * @note Verifica la correttezza delle informazioni immesse e aggiorna la password dell'utente connesso
     * @param string $userEmail l'email di iscrizione fornita dall'utente per verificare che corrisponda
     * @param string $oldPassword la password in questo momento associata alla user identificata dall'email
     * @param string $newPassword la nuova password da aggiornare per la user
     * @param string|null $repeatPassword se fornita, verifica che sia uguale a $newPassword
     * @return void
     * @throws Throwable
     */
    public static function updateLoggedUserPassword(
        string $userEmail,
        string $oldPassword,
        string $newPassword,
        ?string $repeatPassword = null
    ): void
    {
        $User = DB::queryStmt(
            'SELECT email, pass FROM personaggio WHERE id = :userid', [
                'userid' => Session::read('login_id')
        ]);

        if (!count($User)) {
            throw new Exception(
                sprintf(
                    'La user connessa non ha una riga corrispondente nel db! (login_id: %s; IP: %s; UA: %s)',
                    Session::read('login_id'),
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT']
                )
            );
        }

        $MailCrypter = CrypterAlgo::withAlgo('CrypterSha256');

        if (!$MailCrypter->verify($User['email'], $userEmail)) {
            throw new Exception('La mail non corrisponde', -1);
        }

        if (!Password::verify($User['pass'], $oldPassword)) {
            throw new Exception('La vecchia password non corrisponde', -1);
        }

        if (!is_null($repeatPassword) && $newPassword !== $repeatPassword) {
            throw new Exception('La due password non corrispondono', -1);
        }

        // Se sono arrivato fino a qui è tutto OK!
        DB::queryStmt(
            'UPDATE personaggio SET pass = :userpassword WHERE id = :userid', [
                'userpassword' => Password::hash($newPassword),
                'userid' => Session::read('login_id')
            ]
        );
    }
}