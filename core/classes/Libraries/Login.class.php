<?php

class Login extends BaseClass
{

    /**
     * @fn beforeLogin
     * @note Funzione di controllo dei dati di login
     * @param string $login
     * @param string $pass
     * @param string $ip
     * @return void
     * @throws Throwable
     */
    public function beforeLogin(string $login, string $pass, string $ip): void
    {

        // Controllo che l'account esista
        if ( !Session::isLogged() && !Session::login($login, $pass) ) {
            throw new Exception($GLOBALS['MESSAGE']['error']['unknown_username']);
        }

        // Controllo che l'indirizzo ip non sia bloccato
        if ( Functions::isBlacklistedIp($ip) ) {
            throw new Exception($GLOBALS['MESSAGE']['warning']['blacklisted']);
        }

        // Controllo che l'account non sia bloccato
        if ( Functions::isAccountBanned($login) ) {
            throw new Exception($GLOBALS['MESSAGE']['warning']['blacklisted']);
        }

        // Controllo che l'account non sia già attivo
        if (
            !Session::isLogged() &&
            CarbonWrapper::DatesDifferenceMinutes(Session::read('ultimo_refresh'), CarbonWrapper::getNow()) < 2 &&
            CarbonWrapper::greaterThan(Session::read('ultima_entrata'), Session::read('ultima_uscita'))
        ) {
            throw new Exception($GLOBALS['MESSAGE']['warning']['double_connection']);
        }
    }

    /**
     * @fn execLogin
     * @note Funzione di login
     * @param string $login
     * @param string $ip
     * @return void
     * @throws Throwable
     */
    public function execLogin(string $login, string $ip): void
    {

        // Aggiorno alcune informazioni utili sul personaggio
        DB::queryStmt(
            'UPDATE personaggio 
            SET ora_entrata = NOW(), 
                ultimo_luogo = :ultimo_luogo,
                ultimo_refresh = NOW(), 
                last_ip = :address,  
                is_invisible = 0 
        WHERE nome = :username',
            [
                'ultimo_luogo' => Session::read('luogo'),
                'address' => $ip,
                'username' => $login,
            ]
        );

        // Confermo la sessione
        Session::commit();
    }

    /**
     * @fn afterLogin
     * @note Esecuzione azioni post-login
     * @return void
     * @throws Throwable
     */
    public function afterLogin(): void
    {
        if ( Functions::get_constant('LOGIN_BACK_LOCATION') ) {
            Session::store('luogo', -1);
            $redirectUrl = 'main.php?page=mappaclick&map_id=' . Session::read('mappa');
        } else {
            // Configuro la url più appropriata a cui reindirizzare l'utente
            $redirectUrl = 'main.php?dir=' . Session::read('luogo');
        }

        // Redirect all'interno del sito
        Functions::redirect($redirectUrl);
    }

    /**
     * @throws Throwable
     */
    public function logout()
    {
        // Aggiorno alcune informazioni utili sul personaggio
        DB::queryStmt(
            "UPDATE personaggio SET ora_uscita = NOW(), ultimo_refresh = NOW() WHERE id = :id",
            ['id' => $this->me_id]
        );

        // Elimino la sessione
        Session::destroy();
    }
}