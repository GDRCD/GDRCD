<?php

/**
 * @class Modifica Email
 * @note Questo controller serve a gestire la pagina per la modifica email utente
 */
class ModificaEmail extends BaseClass
{
    /**
     * @fn render
     * @note si occupa di renderizzare il form di modifica email
     * @return string
     * @throws Throwable
     */
    public static function render(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'utenti/email/email_update', [
            'pagetitle' => $GLOBALS['MESSAGE']['interface']['user']['email']['page_name'],
            'formlabel' => [
                'email' => $GLOBALS['MESSAGE']['interface']['user']['email']['email'],
                'password' => $GLOBALS['MESSAGE']['interface']['user']['email']['password'],
                'submit' => $GLOBALS['MESSAGE']['interface']['user']['email']['submit']['user'],
            ],
            'response' => [
                'success' => $GLOBALS['MESSAGE']['interface']['user']['email']['awaiting-verification'],
                'error' => $GLOBALS['MESSAGE']['warning']['cant_do'],
            ],
        ]);
    }

    /**
     * @fn updateEmail
     * @note aggiorna l'indirizzo email dell'account in base ai dati associati al token fornito
     * @param string $token
     * @return void
     * @throws Throwable
     */
    public function updateEmail(string $token): void
    {
        $Token = AccessToken::fromToken($token);

        if ( !$Token->isValid($Token::TYPE_EMAIL_UPDATE) ) {
            throw new UnknownTokenException('Invalid token');
        }

        if ( !self::isUniqueEmail($Token->getData()) ) {
            throw new UnexpectedValueException('Email already exists');
        }

        DB::queryStmt(
            'UPDATE personaggio SET email = :email WHERE id = :personaggio',
            [
                'email' => $Token->getData(),
                'personaggio' => $Token->getAccountId()
            ]
        );

        // elimino tutti i token disponibili
        AccessToken::deleteByAccountId($Token->getAccountId(), $Token::TYPE_EMAIL_UPDATE);
    }

    /**
     * @fn sendVerificationEmail
     * @note Invia una email con un link di verifica per autorizzare il cambio dell'indirizzo dell'account
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function sendVerificationEmail(array $post): array
    {
        $user_email = Filters::email($post['email']);
        $password = $post['password'];

        if ( !self::isValidUser(Session::read('login_id'), $password) ) {
            return [
                'response' => false,
                'swal_title' => $GLOBALS['MESSAGE']['warning']['cant_do'],
                'swal_message' => $GLOBALS['MESSAGE']['interface']['user']['email']['wrong-password'],
                'swal_type' => 'error',
            ];
        }

        $email = CrypterAlgo::withAlgo('CrypterSha256')->crypt($user_email);

        if ( !self::isUniqueEmail($email) ) {
            return [
                'response' => false,
                'swal_title' => $GLOBALS['MESSAGE']['warning']['cant_do'],
                'swal_message' => $GLOBALS['MESSAGE']['interface']['user']['email']['already-exists'],
                'swal_type' => 'error',
            ];
        }

        $token = self::createVerificationToken(Session::read('login_id'), $email);

        if ( !self::sendEmail($user_email, $token) ) {
            return [
                'response' => false,
                'swal_title' => $GLOBALS['MESSAGE']['warning']['cant_do'],
                'swal_message' => $GLOBALS['MESSAGES']['interface']['user']['email']['mailserver-error'],
                'swal_type' => 'error',
            ];
        }

        return [
            'response' => true,
            'swal_title' => $GLOBALS['MESSAGES']['warning']['success'],
            'swal_message' => $GLOBALS['MESSAGES']['interface']['user']['email']['awaiting-verification'],
            'swal_type' => 'success',
        ];
    }

    /**
     * @fn createVerificationToken
     * @note crea un token di accesso da usare per la verifica del nuovo indirizzo
     * @param int $accountId
     * @param string $newEmail nuova email da associare all'account in caso di iter positivo
     * @return string
     * @throws Throwable
     */
    protected static function createVerificationToken(int $accountId, string $newEmail): string
    {
        if (($validity_hours = Functions::get_constant('EMAIL_TOKEN_VALIDITY_HOUR', false)) === false) {
            $validity_hours = 24;
        }

        // Nuovo access token e invio mail di verifica
        $Token = AccessToken::create(
            $accountId,
            AccessToken::TYPE_EMAIL_UPDATE,
            $validity_hours * 3600,
            $newEmail
        );

        if (!$Token->isValid(AccessToken::TYPE_EMAIL_UPDATE)) {
            throw new LogicException('Errore nella creazione del token di cambio email');
        }

        return $Token->toString();
    }

    /**
     * @fn isUniqueEmail
     * @note verifica che la mail non sia già presente nel db
     * @param string $email
     * @return bool
     * @throws Throwable
     */
    protected static function isUniqueEmail(string $email): bool
    {
        return ! DB::queryStmt(
                'SELECT id FROM personaggio WHERE email = :email', [
                'email' => $email,
            ])->getNumRows();
    }

    /**
     * @fn isValidUser
     * @note verifica che la user esista sul db e la password sia corretta
     * @param int $accountId
     * @param string $password
     * @return bool
     * @throws Throwable
     */
    protected static function isValidUser(int $accountId, string $password): bool
    {
        $user_data = DB::queryStmt(
            'SELECT pass FROM personaggio WHERE id = :user_id', [
            'user_id' => $accountId,
        ]);

        return $user_data->getNumRows() && Password::verify($user_data['pass'], $password);
    }

    /**
     * @fn sendVerificationEmail
     * @note Invia un link per la verifica del nuovo indirizzo email
     * @param string $userEmail
     * @param string $token
     * @return bool true se la mail è stata inviata, false altrimenti
     * @throws Throwable
     */
    protected static function sendEmail(string $userEmail, string $token): bool
    {
        $url =  "{$GLOBALS['PARAMETERS']['info']['site_url']}/index.php?page=homepage&content=email/update_status&token={$token}";

        # TODO Creare una classe Email

        // Creo le intestazioni minime per l'invio della mail come formato HTML
        $headers = implode("\n", [
            "From: {$GLOBALS['PARAMETERS']['info']['webmaster_name']} <{$GLOBALS['PARAMETERS']['info']['webmaster_email']}>",
            "X-Mailer: PHP/GDRCD6",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=\"iso-8859-1\"",
            "Content-Transfer-Encoding: 7bitnn"
        ]);

        $subject = gdrcd_filter(
            'out',
            $GLOBALS['MESSAGE']['interface']['user']['email']['page_name'] . ' ' . $GLOBALS['PARAMETERS']['info']['site_name']
        );

        $message = Template::getInstance()->startTemplate()->render(
            'utenti/email/verification_mail', [
            'mailtext' => $GLOBALS['MESSAGE']['recoverypassword']['forms']['mail']['text'],
            'verificationurl' => $url,
        ]);

        return mail($userEmail, $subject, $message, $headers);
    }
}