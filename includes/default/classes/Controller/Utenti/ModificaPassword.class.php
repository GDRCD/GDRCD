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
     * @throws Throwable
     */
    public static function render(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'utenti/password/password_update', [
            'pagetitle' => $GLOBALS['MESSAGE']['interface']['user']['pass']['page_name'],
            'formlabel' => [
                'email' => $GLOBALS['MESSAGE']['interface']['user']['pass']['email'],
                'oldpass' => $GLOBALS['MESSAGE']['interface']['user']['pass']['old'],
                'newpass' => $GLOBALS['MESSAGE']['interface']['user']['pass']['new'],
                'repeatpass' => $GLOBALS['MESSAGE']['interface']['user']['pass']['repeat'],
                'submit' => $GLOBALS['MESSAGE']['interface']['user']['pass']['submit']['user'],
            ],
            'response' => [
                'success' => $GLOBALS['MESSAGE']['warning']['modified'],
                'error' => $GLOBALS['MESSAGE']['warning']['cant_do'],
            ],
        ]);
    }

    /**
     * @fn updatePassword
     * @note Update la password dell'utente
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function updatePassword(array $post): array
    {
        $user_email = Filters::email($post['email']);
        $old_password = $post['old_pass'];
        $new_password = $post['new_pass'];
        $repeated_password = $post['repeat_pass'];

        if ( !self::isValidUser(Session::read('login_id'), $old_password, $user_email) ) {
            return [
                'response' => false,
                'swal_title' => $GLOBALS['MESSAGE']['warning']['cant_do'],
                'swal_message' => $GLOBALS['MESSAGE']['interface']['user']['pass']['error'],
                'swal_type' => 'error',
            ];
        }

        // Controllo che la nuova password inserita corrisponda alla ripetizione
        if ( !is_null($repeated_password) && $new_password !== $repeated_password ) {
            return [
                'response' => false,
                'swal_title' => $GLOBALS['MESSAGE']['warning']['cant_do'],
                'swal_message' => $GLOBALS['MESSAGE']['interface']['user']['pass']['mismatch-password'],
                'swal_type' => 'error',
            ];
        }

        DB::queryStmt(
            'UPDATE personaggio SET pass = :user_password, ultimo_cambiopass = NOW() WHERE id = :user_id', [
                'user_password' => Password::hash($repeated_password),
                'user_id' => Session::read('login_id'),
            ]
        );

        return [
            'response' => true,
            'swal_title' => $GLOBALS['MESSAGE']['warning']['modified'],
            'swal_message' => $GLOBALS['MESSAGE']['interface']['user']['pass']['success'],
            'swal_type' => 'success',
        ];
    }

    /**
     * @fn isValidUser
     * @note verifica che la user esista sul db e che password e email siano corrette
     * @param int $accountId
     * @param string $password
     * @param string $email
     * @return bool
     * @throws Throwable
     */
    protected static function isValidUser(int $accountId, string $password, string $email): bool
    {
        $user_data = DB::queryStmt(
            'SELECT email, pass FROM personaggio WHERE id = :user_id', [
            'user_id' => $accountId,
        ]);

        return $user_data->getNumRows()
            && Password::verify($user_data['pass'], $password)
            && CrypterAlgo::withAlgo('CrypterSha256')->verify($user_data['email'], $email);
    }
}