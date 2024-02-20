<?php
/**
 * @class UnknownEmailException
 * @note tipo di eccezione usata per avvisare che una data mail non ha riscontro nel database
 */
class UnknownEmailException extends UnexpectedValueException {}

/**
 * @class UnknownTokenException
 * @note tipo di eccezione usata per avvisare che un token di ripristino è invalido o scaduto
 */
class UnknownTokenException extends UnexpectedValueException {}


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
            'servizi/password/recovery_form', [
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
            'servizi/password/recovery_status', [
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
            'servizi/password/recovery_status', [
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
            'servizi/password/recovery_status', [
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
        $recovery = DB::queryStmt(
            'SELECT personaggio, UNIX_TIMESTAMP(scadenza) AS scadenza_timestamp 
            FROM recupero_password 
                INNER JOIN personaggio ON(personaggio.id = recupero_password.personaggio)
            WHERE token = :token',
            ['token' => $recoveryToken]
        );

        if (!$recovery->getNumRows() || $recovery['scadenza_timestamp'] < time()) {
            throw new UnknownTokenException('Token invalido o personaggio non trovato');
        }

        DB::queryStmt(
            'UPDATE personaggio SET pass = :password, ultimo_cambiopass = NOW() WHERE id = :personaggio',
            [
                'password' => Password::hash($newPassword),
                'personaggio' => $recovery['personaggio']
            ]
        );

        DB::queryStmt('DELETE FROM recupero_password WHERE token = :token', ['token' => $recoveryToken]);
    }

    /**
     * @fn sendRecoveryEmail
     * @note Verifica se la mail esiste nel db e provvede all'invio di un link per il recupero password
     * @param string $userEmail
     * @return bool true se la mail è stata inviata, false altrimenti
     * @throws Throwable
     */
    public static function sendRecoveryEmail(string $userEmail): bool
    {
        $token = self::makePasswordRecoveryToken($userEmail);
        $url =  "{$GLOBALS['PARAMETERS']['info']['site_url']}/index.php?page=homepage&content=resetpassword&token={$token}";

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
            'servizi/password/recovery_mail', [
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

        // Cancello eventuali token presenti in precedenza per uno stesso account
        DB::queryStmt(
            'DELETE FROM recupero_password WHERE personaggio = :personaggio',
            ['personaggio' => $user_data['id']]
        );

        // Genero un token casuale. random_bytes è crittograficamente sicuro, non serve altro
        $token = bin2hex(random_bytes(18));

        // Salvo il token nel database con la relativa scadenza. Se ne esisteva uno vecchio lo sovrascrivo
        DB::queryStmt(
            'INSERT INTO recupero_password (token, personaggio, scadenza) VALUES (:token, :personaggio, :scadenza)',
            [
                'token' => $token,
                'personaggio' => $user_data['id'],
                'scadenza' => (new Datetime)->add(new DateInterval("PT{$validity_hours}H"))->format('Y-m-d H:i:s')
            ]
        );

        return $token;
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
        $user_email = Filters::email($post['email']);

        try {

            if (self::sendRecoveryEmail($user_email)) {
                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Inviato link di recupero alla mail indicata.',
                    'swal_type' => 'success',
                ];
            }

        } catch (UnknownEmailException) {

            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Nessuna corrispondenza trovata per la mail fornita.',
                'swal_type' => 'error',
            ];

        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => 'Operazione non riuscita per problemi interni, si invita a riprovare più tardi',
            'swal_type' => 'error',
        ];
    }
}