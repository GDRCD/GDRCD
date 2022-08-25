<?php
/*
 * Indichiamo che in questa pagina dobbiamo modificare/scrivere
 * il contenuto della sessione e che quindi il lock ci serve.
 * In caso contrario (default) possiamo solo "leggere" dalla sessione
 */
const SESSION_LOCK = true;
require_once __DIR__ . '/core/required.php';

$login = $_POST['login']?? '';
$pass = $_POST['pass']?? '';
$wasAlreadyLoggedIn = Session::isLogged();


if (Functions::isBlacklistedIp($_SERVER['REMOTE_ADDR'])) {
    echo Template::getInstance()->startTemplate()->render(
        'login-error', [
            'login_error_text' => $GLOBALS['MESSAGE']['warning']['blacklisted'],
            'login_again_text' => $GLOBALS['MESSAGE']['warning']['please_login_again'],
            'homepage_url' => $GLOBALS['PARAMETERS']['info']['site_url']
        ]);
    die();
}

if (!$wasAlreadyLoggedIn && !Session::login($login, $pass)) {
    echo Template::getInstance()->startTemplate()->render(
        'login-error', [
            'login_error_text' => $GLOBALS['MESSAGE']['error']['unknown_username'] ,
            'login_again_text' => $GLOBALS['MESSAGE']['warning']['please_login_again'],
            'homepage_url' => $GLOBALS['PARAMETERS']['info']['site_url']
        ]);
    die();
}

/*
 * Nota bene: dal momento che Session::login() è stata eseguita con successo
 * abbiamo in una sessione "temporanea" i dati del personaggio.
 * Se non si fa nulla o si lancia preventivamente un Session::commit() la
 * sessione verrà definitivamente salvata sul server e sarà operativa a tutti
 * gli effetti.
 * Per questo motivo, se da questo momento in avanti degli errori impediscono
 * il completamento della procedura di login è necessaio utilizzare il comando
 * Session::abort() per annullare il salvataggio sul server della sessione
 * temporanea
 */

if (Functions::isAccountBanned($login)) {
    Session::abort();
    echo Template::getInstance()->startTemplate()->render(
        'login-error', [
            'login_error_text' => $GLOBALS['MESSAGE']['warning']['blacklisted'],
            'login_again_text' => $GLOBALS['MESSAGE']['warning']['please_login_again'],
            'homepage_url' => $GLOBALS['PARAMETERS']['info']['site_url']
        ]);
    die();
}

if (
    !$wasAlreadyLoggedIn
    && strtotime(Session::read('ultima_entrata')) > strtotime(Session::read('ultima_uscita'))
    && strtotime(Session::read('ultimo_refresh'))+120 > time()
) {
    Session::abort();
    echo Template::getInstance()->startTemplate()->render(
        'login-error', [
            'login_error_text' => $GLOBALS['MESSAGE']['warning']['double_connection'],
            'login_again_text' => $GLOBALS['MESSAGE']['warning']['please_login_again'],
            'homepage_url' => $GLOBALS['PARAMETERS']['info']['site_url']
        ]);
    die();
}

// Arrivati fin qui è tutto OK: convalido la sessione!
Session::commit();


// Configuro la url più appropriata a cui redirezionare l'utente
$redirectUrl = 'main.php?dir=' . Session::read('luogo');

if (($PARAMETERS['mode']['log_back_location']?? 'OFF') === 'OFF') {
    Session::store('luogo', -1);
    $redirectUrl = 'main.php?page=mappaclick&map_id=' . Session::read('mappa');
}


// Aggiorno alcune informazioni utili sul personaggio/player
DB::queryStmt(
    'UPDATE personaggio 
            SET ora_entrata = NOW(), 
                ultimo_luogo = :ultimoluogo,
                ultimo_refresh = NOW(), 
                last_ip = :address,  
                is_invisible = 0 
        WHERE nome = :username',
    [
        'ultimoluogo' => Session::read('luogo'),
        'address' => $_SERVER['REMOTE_ADDR'],
        'username' => $login
    ]
);


// Redirect all'interno del sito
Functions::redirect($redirectUrl);

# EOF