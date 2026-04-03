<?php
/**
 * @class UnknownEmailException
 * @note tipo di eccezione usata per avvisare che una data mail non ha riscontro nel database
 */
class UnknownEmailException extends UnexpectedValueException {}


/**
 * @class RecuperoPassword
 * @note Questo controller serve a gestire le operazioni e i form necessari per il recupero password
 */
class RecuperoPassword extends BaseClass
{
    /**
     * @fn render
     * @note entry point del modulo recupero password, renderizza il form o messaggi di errore
     * @return string
     * @throws Throwable
     */
    public static function render(): string
    {
        // Se non ci sono dati post renderizzo il form
        if (empty($_POST) || empty($_POST['new_password'])) {
            return self::renderEditForm();
        }

        $token = $_POST['token'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Se le pass non coincidono
        if ($new_password !== $confirm_password) {
            return self::renderMismatchingPassword();
        }

        try {

            // Aggiorno la password e renderizzo un messaggio di esito positivo
            self::updateUserPasswordFromToken($token, $new_password);
            return self::renderPasswordUpdatedStatus();

        } catch (UnknownTokenException $e) {

            // renderizzo un messaggio di token invalido o scaduto
            return self::renderInvalidTokenStatus();

        }
    }

    /**
     * @fn renderEditForm
     * @note renderizza il form di aggiornamento password
     * @return string
     * @throws Throwable
     */
    public static function renderEditForm(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'homepage/password/recovery_form', [
            'pagetitle' => $GLOBALS['MESSAGE']['interface']['user']['pass']['page_name'],
            'formaction' => $_SERVER['REQUEST_URI'],
            'formlabel' => [
                'newpass' => $GLOBALS['MESSAGE']['interface']['user']['pass']['new'],
                'repeatpass' => $GLOBALS['MESSAGE']['interface']['user']['pass']['repeat'],
                'submit' => $GLOBALS['MESSAGE']['interface']['user']['pass']['submit']['user'],
            ],
            'token' => Filters::html($_GET['token']?? ''),
        ]);
    }

    /**
     * @fn renderPassowrdUpdatedStatus
     * @note renderizza un messaggio di riuscita dell'operazione di aggiornamento password
     * @return string
     * @throws Throwable
     */
    public static function renderPasswordUpdatedStatus(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'homepage/password/recovery_status', [
            'pagetitle' => $GLOBALS['MESSAGE']['interface']['user']['pass']['page_name'],
            'message' => $GLOBALS['MESSAGE']['recoverypassword']['status']['success']['text'],
        ]);
    }

    /**
     * @fn renderInvalidTokenStatus
     * @note renderizza un messaggio d'errore che informa del fatto che il token per l'aggiornamento della password è scaduto
     * @return string
     * @throws Throwable
     */
    public static function renderInvalidTokenStatus(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'homepage/password/recovery_status', [
            'pagetitle' => $GLOBALS['MESSAGE']['interface']['user']['pass']['page_name'],
            'message' => $GLOBALS['MESSAGE']['recoverypassword']['status']['invalidtoken']['text'],
        ]);
    }

    /**
     * @fn renderInvalidTokenStatus
     * @note renderizza un messaggio d'errore che le due password non coincidono
     * @return string
     * @throws Throwable
     */
    public static function renderMismatchingPassword(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'homepage/password/recovery_status', [
            'pagetitle' => $GLOBALS['MESSAGE']['interface']['user']['pass']['page_name'],
            'message' => $GLOBALS['MESSAGE']['recoverypassword']['status']['mismatch']['text'],
        ]);
    }

    /**
     * @fn renderRecoveryMailSentStatus
     * @note renderizza un messaggio che informa che la mail è stata spedita all'account
     * @return string
     */
    public static function renderRecoveryMailSentStatus(): string
    {
        //  non adoperato allo stato attuale. Il metodo recoverPassword ritorna questa info in una chiamata xhr
    }

    /**
     * @fn renderRecoveryMailUnknownStatus
     * @note renderizza un messaggio che informa che la mail non è stata trovata nel db
     * @return string
     */
    public static function renderRecoveryMailUnknownStatus(): string
    {
        // non adoperato allo stato attuale. Il metodo recoverPassword ritorna questa info in una chiamata xhr
    }

    /**
     * @fn updateUserPasswordFromToken
     * @note verifica che recoveryToken sia valido e aggiorna la password all'utente associato
     * @param string $recoveryToken
     * @param string $newPassword
     * @return void
     * @throws UnknownTokenException|Throwable lancia un eccezione se il token è invalido
     */
    public static function updateUserPasswordFromToken(string $recoveryToken, string $newPassword): void
    {
        $Token = AccessToken::fromToken($recoveryToken);

        if (!$Token->isValid($Token::TYPE_PASSWORD_RECOVERY)) {
            throw new UnknownTokenException('Token invalido o personaggio non trovato');
        }

        DB::queryStmt(
            'UPDATE personaggio SET pass = :password, ultimo_cambiopass = NOW() WHERE id = :personaggio',
            [
                'password' => Password::hash($newPassword),
                'personaggio' => $Token->getAccountId()
            ]
        );

        // elimino tutti i token disponibili
        AccessToken::deleteByAccountId($Token->getAccountId(), $Token::TYPE_PASSWORD_RECOVERY);
    }

    /**
     * @fn sendRecoveryEmail
     * @note Verifica se la mail esiste nel db e provvede all'invio di un link per il recupero password
     * @param string $userEmail
     * @param string $token
     * @return bool true se la mail è stata inviata, false altrimenti
     * @throws Throwable
     */
    protected static function sendRecoveryEmail(string $userEmail, string $token): bool
    {
        $url =  "{$GLOBALS['PARAMETERS']['info']['site_url']}/index.php?page=homepage&content=password/edit_form&token={$token}";

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
            $GLOBALS['MESSAGE']['register']['forms']['mail']['sub'] . ' ' . $GLOBALS['PARAMETERS']['info']['site_name']
        );

        $message = Template::getInstance()->startTemplate()->render(
            'homepage/password/recovery_mail', [
            'mailtext' => $GLOBALS['MESSAGE']['recoverypassword']['forms']['mail']['text'],
            'recoveryurl' => $url,
        ]);

        return mail($userEmail, $subject, $message, $headers);
    }

    /**
     * @fn makePasswordRecoveryToken
     * @note Chiede di generare un nuovo token di recupero password che sarà inviato per mail all'utente
     * @note Il token deve avere validità di sessione di N ore ( definito da una costante sul db )
     * @note Se viene richiesto un nuovo token, il precedente viene immediatamente invalidato
     * @param string $userEmail
     * @return string
     * @throws Throwable
     */
    protected static function makePasswordRecoveryToken(string $userEmail): string
    {
        $user_data = DB::queryStmt(
            'SELECT id FROM personaggio WHERE email = :user_email', [
            'user_email' => CrypterAlgo::withAlgo('CrypterSha256')->crypt($userEmail),
        ]);

        if (!$user_data->getNumRows()) {
            throw new UnknownEmailException('L\'email non corrisponde a nessun utente del db');
        }

        // Se non trovo la costante sul db imposto un default
        if (($validity_hours = Functions::get_constant('RECOVERY_TOKEN_VALIDITY_HOUR', false)) === false) {
            $validity_hours = 24;
        }

        $Token = AccessToken::create(
            $user_data['id'],
            AccessToken::TYPE_PASSWORD_RECOVERY,
            $validity_hours * 3600
        );

        if (!$Token->isValid(AccessToken::TYPE_PASSWORD_RECOVERY)) {
            throw new LogicException('Errore nella creazione del token di recupero password');
        }

        return $Token->toString();
    }

    /**
     * @fn recoverPassword
     * @note Recupera la password dell'utente
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function recoverPassword(array $post): array
    {
        try {

            $user_email = Filters::email($post['email']);
            $token = self::makePasswordRecoveryToken($user_email);

            if (self::sendRecoveryEmail($user_email, $token)) {
                return [
                    'response' => true,
                    'swal_title' => $GLOBALS['MESSAGE']['warning']['success'],
                    'swal_message' => $GLOBALS['MESSAGE']['interface']['user']['pass']['success'],
                    'swal_type' => 'success',
                ];
            }

        } catch (UnknownEmailException) {

            return [
                'response' => false,
                'swal_title' => $GLOBALS['MESSAGE']['warning']['cant_do'],
                'swal_message' => $GLOBALS['MESSAGE']['interface']['user']['pass']['unknown-email'],
                'swal_type' => 'error',
            ];

        }

        return [
            'response' => false,
            'swal_title' => $GLOBALS['MESSAGE']['warning']['cant_do'],
            'swal_message' => '',
            'swal_type' => 'error',
        ];
    }
}