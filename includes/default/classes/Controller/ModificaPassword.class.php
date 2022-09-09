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
            'servizi/password/password_update', [
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


        $user_data = DB::queryStmt(
            'SELECT email, pass FROM personaggio WHERE id = :user_id', [
            'user_id' => Session::read('login_id'),
        ]);

        // Non esiste un pg in database
        if ( !count($user_data) ) {
            return [
                'response' => false,
                'swal_title' => sprintf(
                    'La user connessa non ha una riga corrispondente nel db! (login_id: %s; IP: %s; UA: %s)',
                    Session::read('login_id'),
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT']
                ),
                'swal_message' => 'La mail non corrisponde.',
                'swal_type' => 'error',
            ];
        }

        // Controllo che la mail inserita sia corretta
        if ( !CrypterAlgo::withAlgo('CrypterSha256')->verify($user_data['email'], $user_email) ) {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'La mail non corrisponde.',
                'swal_type' => 'error',
            ];
        }

        // Controllo che la vecchia password inserita sia corretta
        if ( !Password::verify($user_data['pass'], $old_password) ) {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'La vecchia password non corrisponde.',
                'swal_type' => 'error',
            ];
        }

        // Controllo che la nuova password inserita corrisponda alla ripetizione
        if ( !is_null($repeated_password) && $new_password !== $repeated_password ) {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Le nuove password inserite non corrispondono.',
                'swal_type' => 'error',
            ];
        }



        // Inserimento nuova password e risposta positiva
        DB::queryStmt(
            'UPDATE personaggio SET pass = :user_password WHERE id = :user_id', [
                'user_password' => Password::hash($repeated_password),
                'user_id' => Session::read('login_id'),
            ]
        );

        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Le password è stata modificata con successo.',
            'swal_type' => 'success',
        ];
    }
}