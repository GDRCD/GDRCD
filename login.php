<?php

/**
 * Pagina di login.
 *
 * Gestisce l'autenticazione dell'utente tramite il sistema di sessioni.
 * Supporta il login normale e il flusso di Session Takeover Protection (OTP).
 */

require_once(__DIR__ . '/includes/required.php');


/*
 * Input
 */

// Dati in input da form di login
$login_user = $_POST['login1'] ?? '';
$login_password = $_POST['pass1'] ?? '';
$login_theme = $_POST['theme'] ?? '';

// Dati in input da form OTP
$otp_token = $_POST['token'] ?? '';
$otp_personaggio = $_POST['id_personaggio'] ?? '';

// Normalizza lo username
$login_user = ucwords(strtolower(trim($login_user)));


/*
 * Variabili di stato
 */

$login_result = null;
$id_personaggio = null;
$show_stp_form = false;
$conteggio_login_falliti = 0;


/*
 * Login: Verifica OTP
 */

if (!empty($otp_token) && !empty($otp_personaggio)) {

    $id_personaggio_takeover = (int) $otp_personaggio;

    if (!gdrcd_session_takeover($id_personaggio_takeover, $otp_token)) {
        gdrcd_error_fatal('Token di verifica non valido o scaduto.'); // TODO vocabulary
    }

    // Risultati del login
    $login_result = GDRCD_LOGIN_SUCCESS;
    $id_personaggio = $id_personaggio_takeover;


/*
 * Login: Accesso con user e password
 */

} elseif (!empty($login_user) && !empty($login_password)) {

    $login_procedure = gdrcd_session_login($login_user, $login_password);

    // Risultati del login
    $login_result = $login_procedure['result'];
    $id_personaggio = $login_procedure['id_personaggio'];
    $conteggio_login_falliti = $login_procedure['attempt'];

}


/*
 * Login: gestione casi di errore
 */

switch ($login_result){
    case GDRCD_LOGIN_WRONG:
        break;

    case GDRCD_LOGIN_TAKEOVER:
        $show_stp_form = true;
        break;

    case GDRCD_LOGIN_DISABLED:
        gdrcd_error_fatal('Account disabilitato.');

    case GDRCD_LOGIN_BLACKLISTED:
        gdrcd_error_fatal($MESSAGE['warning']['blacklisted']);
}


/*
 * Accesso riuscito
 */

if ($login_result === GDRCD_LOGIN_SUCCESS && !empty($id_personaggio)) {

    /*
     * Controllo doppi
     */

    // TODO isola in funzione dedicata
    // Doppio per cookie
    if (isset($_COOKIE['lastlogin']) && (int) $_COOKIE['lastlogin'] !== $id_personaggio) {
        $personaggio_doppio = gdrcd_stmt_one(
            'SELECT `nome` FROM `personaggio` WHERE `id_personaggio` = ?',
            [(int) $_COOKIE['lastlogin']]
        );

        if ($personaggio_doppio) {
            gdrcd_stmt(
                'INSERT INTO `log` (`id_personaggio`, `nome_interessato`, `autore`, `data_evento`, `codice_evento`, `descrizione_evento`)
                VALUES (?, ?, ?, NOW(), ?, ?)',
                [
                    $id_personaggio,
                    gdrcd_session('login'),
                    'doppio (cookie)',
                    ACCOUNTMULTIPLO,
                    $personaggio_doppio['nome'],
                ]
            );
        }
    }

    // Doppio per ip
    // TODO: index
    $lista_stesso_ip = gdrcd_stmt_all(
        'SELECT `sessions`.`id_personaggio`, `personaggio`.`nome`
        FROM `sessions`
            INNER JOIN `personaggio` ON `personaggio`.`id_personaggio` = `sessions`.`id_personaggio`
        WHERE `ip` = ?
            AND `data_ultimavisita` > DATE_SUB(NOW(), INTERVAL 15 DAY)
            AND `sessions`.`id_personaggio` != ?
        ORDER BY `data_evento` DESC
        LIMIT 1',
        [gdrcd_session_client_ip(), $id_personaggio]
    );

    foreach ($lista_stesso_ip as $personaggio_stesso_ip) {
        gdrcd_stmt(
            'INSERT INTO `log` (`id_personaggio`, `nome_interessato`, `autore`, `data_evento`, `codice_evento`, `descrizione_evento`)
            VALUES (?, ?, ?, NOW(), ?, ?)',
            [$id_personaggio, gdrcd_session('login'), 'doppio (ip)', ACCOUNTMULTIPLO, $personaggio_stesso_ip['nome']]
        );
    }

    // Controllo esilio
    if (gdrcd_controllo_esilio($id_personaggio) === true) {
        gdrcd_session_logout();

        gdrcd_error_fatal(
            '<a href="index.php">' . gdrcd_filter_out($PARAMETERS['info']['homepage_name']) . '</a>',
            false
        );
    }


    /*
     * Cookie per ultimo account connesso da postazione
     */

    setcookie('lastlogin', (string) $id_personaggio, 0, '', '', false, true);


    /*
     * Aggiunta dati alla sessione
     */

    gdrcd_session_init();

    // Tema
    if (!empty($login_theme) && array_key_exists($login_theme, $PARAMETERS['themes']['available'])) {
        gdrcd_session_write('theme', $login_theme);
    }

    // Stipendio automatico
    if ($PARAMETERS['settings']['auto_salary'] === 'ON') {
        $salaryData = gdrcd_stmt_one(
            'SELECT `ultimo_stipendio` FROM `personaggio` WHERE `id_personaggio` = ? LIMIT 1',
            [$id_personaggio]
        );

        if ($salaryData !== null && $salaryData['ultimo_stipendio'] !== date('Y-m-d')) {
            $stipendio = 0;

            $ruoli = gdrcd_stmt_all(
                'SELECT `ruolo`.`stipendio`
                FROM `clgpersonaggioruolo`
                LEFT JOIN `ruolo` ON `clgpersonaggioruolo`.`id_ruolo` = `ruolo`.`id_ruolo`
                WHERE `clgpersonaggioruolo`.`id_personaggio` = ?',
                [$id_personaggio]
            );

            foreach ($ruoli as $ruolo) {
                $stipendio += (int) $ruolo['stipendio'];
            }

            if ($stipendio > 0) {
                gdrcd_stmt(
                    'UPDATE `personaggio` SET `banca` = `banca` + ?, `ultimo_stipendio` = NOW() WHERE `id_personaggio` = ?',
                    [$stipendio, $id_personaggio]
                );
            }
        }
    }

    // Aggiorna i dati del personaggio e redireziona dentro
    if ($PARAMETERS['mode']['log_back_location'] === 'OFF') {
        gdrcd_session_write('luogo', '-1');

        gdrcd_stmt(
            'UPDATE `personaggio`
            SET `ora_entrata` = NOW(), `ultimo_luogo` = ?, `ultimo_refresh` = NOW(), `last_ip` = ?, `is_invisible` = 0
            WHERE `id_personaggio` = ?',
            [gdrcd_session('luogo'), $remote_ip, $id_personaggio]
        );

        gdrcd_session_commit();

        header('Location: main.php?page=mappaclick&map_id=' . gdrcd_session('mappa'), true);
        die();
    }

    gdrcd_stmt(
        'UPDATE `personaggio`
        SET `ora_entrata` = NOW(), `ultimo_refresh` = NOW(), `last_ip` = ?, `is_invisible` = 0
        WHERE `id_personaggio` = ?',
        [$remote_ip, $id_personaggio]
    );

    gdrcd_session_commit();

    header('Location: main.php?dir=' . gdrcd_session('luogo'), true);
    die();
}


/*
 * Template form OTP
 */

if ($show_stp_form) {
    gdrcd_basic_page(
        <<<HTML
            <div class="info_box">
                <h2>Verifica richiesta</h2>
                <p>&Egrave; stata rilevata una sessione già attiva per il tuo account.</p>
                <p>Ti abbiamo inviato un codice di verifica via email.</p>
                <p>Inserisci il codice per completare l'accesso:</p>
                <form method="POST" action="login.php">
                    <input type="hidden" name="id_personaggio" value="{$id_personaggio}">
                    <label for="token">Codice di verifica:</label>
                    <input type="text" name="token" id="token" required maxlength="6" pattern="[0-9]{6}">
                    <button type="submit">Verifica</button>
                </form>
                <p><a href="index.php">Annulla e torna alla homepage</a></p>
            </div>
        HTML
    );

    die();
}


/*
 * Login fallito
 */

$errorMessage = ($MESSAGE['error']['unknown_username'] ?? '')
    . '<br>' . ($MESSAGE['error']['unknown_username_details'] ?? '')
    . '<br>' . ($MESSAGE['error']['unknown_username_failure_count'] ?? '') . ' ' . $conteggio_login_falliti
    . '<br>' . ($MESSAGE['error']['unknown_username_warning'] ?? '')
    . '<br>' . ($MESSAGE['warning']['mailto'] ?? '')
    . ' <a href="mailto:' . gdrcd_filter_out($PARAMETERS['menu']['webmaster_email']) . '">'
    . gdrcd_filter_out($PARAMETERS['menu']['webmaster_email']) . '</a>';

gdrcd_basic_page($errorMessage);
