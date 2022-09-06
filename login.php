<?php
/*
 * Indichiamo che in questa pagina dobbiamo modificare/scrivere
 * il contenuto della sessione e che quindi il lock ci serve.
 * In caso contrario (default) possiamo solo "leggere" dalla sessione
 */
const SESSION_LOCK = true;
require_once __DIR__ . '/core/required.php';

$login = Filters::in($_POST['login']);
$pass = Filters::in($_POST['pass']);

try {
    Login::getInstance()->beforeLogin($login, $pass, $_SERVER['REMOTE_ADDR']);
    Login::getInstance()->execLogin($login, $_SERVER['REMOTE_ADDR']);
    Login::getInstance()->afterLogin();
} catch ( Throwable $e ) {
    Session::abort();
    Session::destroy();
    echo Template::getInstance()->startTemplate()->render(
        'login-error', [
        'login_error_text' => $e->getMessage(),
        'login_again_text' => $GLOBALS['MESSAGE']['warning']['please_login_again'],
        'homepage_url' => $GLOBALS['PARAMETERS']['info']['site_url'],
    ]);
    die();
}