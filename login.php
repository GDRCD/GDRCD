<?php

/* Includo i file principali */
require_once(__DIR__ . '/includes/required.php');

/* Connessione al database */
$handleDBConnection = gdrcd_connect();

/* Leggo i dati del form di login */
$login1 = gdrcd_filter('get', $_POST['login1']);
$pass1  = gdrcd_filter('get', $_POST['pass1']);
$theme  = gdrcd_filter('get', $_POST['theme']);

/* Fix per il funzionamento in locale dell'engine */
switch ($_SERVER['REMOTE_ADDR']) {
    case '::1':
    case '127.0.0.1':
        $host = 'localhost';
        break;
    default:
        $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        break;
}

/* Controllo blacklist */
$blacklistResult = gdrcd_query(
    "SELECT * FROM blacklist WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "' AND granted = 0",
    'result'
);

if (gdrcd_query($blacklistResult, 'num_rows') > 0) {
    gdrcd_query($blacklistResult, 'free');
    echo '<div class="error_box"><h2 class="error_major">' . $MESSAGE['warning']['blacklisted'] . '</h2></div>';
    gdrcd_log_warning('Tentativo di login bloccato', [
        'evento' => 'auth.login.bloccato.blacklist',
        'utente' => $login1,
        'ip'     => $_SERVER['REMOTE_ADDR'],
    ]);
    exit();
}

/* Rendo maiuscola la prima lettera del nome */
$login1 = ucwords(strtolower(trim($login1)));

/* Carico il profilo dal database */
$record = gdrcd_stmt_one(
    "
    SELECT
        personaggio.id_personaggio,
        personaggio.pass,
        personaggio.nome,
        personaggio.cognome,
        personaggio.permessi,
        personaggio.sesso,
        personaggio.ultima_mappa,
        personaggio.ultimo_luogo,
        personaggio.id_razza,
        personaggio.blocca_media,
        personaggio.ora_entrata,
        personaggio.ora_uscita,
        personaggio.ultimo_refresh,
        razza.sing_m,
        razza.sing_f,
        razza.icon AS url_img_razza,
        personaggio.posizione
    FROM personaggio
    LEFT JOIN razza ON personaggio.id_razza = razza.id_razza
    WHERE nome = ?",
    [$login1]
);

/* Valuto le condizioni di accesso */
$accountFound  = !empty($record) && gdrcd_password_check($pass1, $record['pass']);
$accountExiled = $accountFound && gdrcd_controllo_esilio($record['id_personaggio']);
$credentialsOk = $accountFound && !$accountExiled && ($record['permessi'] > -1);

$sessionExpired = $credentialsOk && (
    strtotime($record['ora_entrata']) < strtotime($record['ora_uscita'])
    || (strtotime($record['ultimo_refresh']) + $PARAMETERS['settings']['reconnection_cooldown']) < time()
);
$sessionActive  = $credentialsOk && !$sessionExpired;

/* CASO 0: Account in esilio */
if ($accountExiled) {

    echo '<div class="error_box"><h2 class="error_major">' . $MESSAGE['warning']['exiled'] . '</h2></div>';
    gdrcd_log_warning('Tentativo di login su account in esilio', [
        'evento'         => 'auth.login.bloccato',
        'utente'         => $login1,
        'id_personaggio' => $record['id_personaggio'],
        'ip'             => $_SERVER['REMOTE_ADDR'],
    ], $record['id_personaggio']);
    exit();
}
/* CASO 1: Login OK */ 
elseif ($credentialsOk && $sessionExpired) {

    $_SESSION['id_personaggio'] = $record['id_personaggio'];
    $_SESSION['login']          = gdrcd_filter_in($record['nome']);
    $_SESSION['cognome']        = $record['cognome'];
    $_SESSION['permessi']       = $record['permessi'];
    $_SESSION['sesso']          = $record['sesso'];
    $_SESSION['blocca_media']   = $record['blocca_media'];
    $_SESSION['ultima_uscita']  = $record['ora_uscita'];
    $_SESSION['razza']          = ($record['sesso'] == 'f') ? $record['sing_f'] : $record['sing_m'];
    $_SESSION['img_razza']      = $record['url_img_razza'];
    $_SESSION['id_razza']       = $record['id_razza'];
    $_SESSION['posizione']      = $record['posizione'];
    $_SESSION['mappa']          = empty($record['ultima_mappa'])  ? 1  : $record['ultima_mappa'];
    $_SESSION['luogo']          = empty($record['ultimo_luogo'])  ? -1 : $record['ultimo_luogo'];
    $_SESSION['tag']            = '';
    $_SESSION['last_message']   = 0;

    /* Tema */
    if (!empty($theme) && array_key_exists($theme, $PARAMETERS['themes']['available'])) {
        $_SESSION['theme'] = $theme;
    }

    /* Gilde */
    $res = gdrcd_query(
        "
        SELECT ruolo.gilda, ruolo.immagine
        FROM ruolo
        JOIN clgpersonaggioruolo ON clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo
        WHERE clgpersonaggioruolo.id_personaggio = '" . gdrcd_filter('in', $record['id_personaggio']) . "'",
        'result'
    );
    while ($row = gdrcd_query($res, 'fetch')) {
        $_SESSION['gilda']     .= ',*' . $row['gilda'] . '*';
        $_SESSION['img_gilda'] .= $row['immagine'] . ',';
    }
    gdrcd_query($res, 'free');

    /* Rilevamento multi-account tramite cookie */
    if (isset($_COOKIE['lastlogin']) && $_COOKIE['lastlogin'] != $_SESSION['id_personaggio']) {
        $otherAccountData = gdrcd_query(
            "
            SELECT id_personaggio, nome FROM personaggio
            WHERE id_personaggio = " . $_SESSION['id_personaggio']
        );
        gdrcd_log_warning('Rilevato possibile account multiplo tramite cookie attivo', [
            'evento'           => 'auth.multiaccount.cookie',
            'utente_corrente'  => $_SESSION['login'],
            'id_altro_account' => $otherAccountData['id_personaggio'],
            'altro_account'    => !empty($otherAccountData) ? $otherAccountData['nome'] : '-Sconosciuto-',
        ], $_SESSION['id_personaggio']);
    }

    /* Rilevamento multi-account tramite IP */
    $lastlogindata = [];
    foreach (
        gdrcd_stmt_all(
            "SELECT
                    JSON_UNQUOTE(JSON_EXTRACT(contesto, '$.utente'))         AS nome_interessato,
                    JSON_UNQUOTE(JSON_EXTRACT(contesto, '$.ip'))             AS autore,
                    JSON_UNQUOTE(JSON_EXTRACT(contesto, '$.id_personaggio')) AS id_personaggio
                FROM logs
                WHERE JSON_EXTRACT(contesto, '$.ip')    = ?
                AND JSON_EXTRACT(contesto, '$.evento') = ?
                ORDER BY data DESC",
            [$_SERVER['REMOTE_ADDR'], 'auth.login.successo']
        ) as $row
    ) {
        $lastlogindata[] = $row;
    }
    $lastlogindata = array_unique($lastlogindata, SORT_REGULAR);

    if (count($lastlogindata) > 1) {
        foreach ($lastlogindata as $row) {
            if ($row['autore'] == $_SERVER['REMOTE_ADDR'] && $row['nome_interessato'] != $_SESSION['login']) {
                gdrcd_log_warning('Possibile correlazione tra account tramite IP', [
                    'evento'          => 'auth.multiaccount.ip',
                    'utente_corrente' => $_SESSION['login'],
                    'altro_account'   => $row['nome_interessato'],
                    'id_altro_account' => $row['id_personaggio'],
                    'ip'              => $_SERVER['REMOTE_ADDR'],
                ], $_SESSION['id_personaggio']);
            }
        }
    }

    gdrcd_log_info('Login effettuato con successo', [
        'evento' => 'auth.login.successo',
        'utente' => $_SESSION['login'],
        'ip'     => $_SERVER['REMOTE_ADDR'],
    ], $_SESSION['id_personaggio']);

    /* ------------------------------------------------------------------ */
    /* CASO 2: Credenziali OK ma sessione ancora attiva (doppio login)      */
    /* ------------------------------------------------------------------ */
} elseif ($sessionActive) {

    echo '<div class="error_box"><h2 class="error_major">' . $MESSAGE['warning']['double_connection'] . '</h2></div>';
    gdrcd_log_warning('Tentativo di connessione da postazione ancora attiva', [
        'evento'         => 'auth.login.bloccato',
        'utente'         => $login1,
        'id_personaggio' => $record['id_personaggio'],
        'ip'             => $_SERVER['REMOTE_ADDR'],
    ], $record['id_personaggio']);
    exit();

    /* ------------------------------------------------------------------ */
    /* CASO 3: Credenziali errate                                           */
    /* ------------------------------------------------------------------ */
} else {

    $_SESSION['id_personaggio'] = null;
    $_SESSION['login']          = '';

    if ($login1 !== '' && $pass1 !== '') {
        gdrcd_log_notice('Tentativo di login non riuscito', [
            'evento' => 'auth.login.fallito',
            'utente' => $login1,
            'host'   => $host,
            'ip'     => $_SERVER['REMOTE_ADDR'],
        ]);

        $failRecord = gdrcd_stmt_one(
            "SELECT COUNT(*) AS totale
             FROM logs
             WHERE contesto LIKE ?
               AND contesto LIKE ?
               AND DATE_ADD(data, INTERVAL 60 MINUTE) > NOW()",
            [
                '%\"ip\":\"' . $_SERVER['REMOTE_ADDR'] . '\"%',
                '%\"evento\":\"auth.login.fallito\"%',
            ]
        );

        $iErrorsNumber = $failRecord['totale'];

        if ($iErrorsNumber >= 10) {
            gdrcd_query("INSERT INTO blacklist (ip, nota, ora, host)
                         VALUES ('" . $_SERVER['REMOTE_ADDR'] . "', '" . $login1 . " (tenta password)', NOW(), '" . $host . "')");
        }
    }
}

/* ------------------------------------------------------------------ */
/* Eseguo l'accesso o mostro errore                                     */
/* ------------------------------------------------------------------ */
if (!empty($_SESSION['id_personaggio'])) {

    if (gdrcd_controllo_esilio($_SESSION['id_personaggio']) === true) {
        session_destroy();
        echo '<a href="index.php">' . $PARAMETERS['info']['homepage_name'] . '</a>';
        exit();
    }

    /* Cookie */
    setcookie('lastlogin', $_SESSION['id_personaggio'], 0, '', '', 0);

    /* Stipendio automatico */
    if ($PARAMETERS['settings']['auto_salary'] == 'ON') {
        $salaryRow = gdrcd_query(
            "
            SELECT soldi, banca, ultimo_stipendio FROM personaggio
            WHERE id_personaggio = '" . $_SESSION['id_personaggio'] . "' LIMIT 1"
        );

        if ($salaryRow['ultimo_stipendio'] != strftime("%Y-%m-%d")) {
            $salaryResult = gdrcd_query(
                "
                SELECT ruolo.stipendio
                FROM clgpersonaggioruolo
                LEFT JOIN ruolo ON clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo
                WHERE clgpersonaggioruolo.id_personaggio = '" . $_SESSION['id_personaggio'] . "'",
                'result'
            );
            $stipendio = 0;
            while ($row = gdrcd_query($salaryResult, 'fetch')) {
                $stipendio += $row['stipendio'];
            }
            gdrcd_query("UPDATE personaggio
                         SET banca = banca + " . $stipendio . ", ultimo_stipendio = NOW()
                         WHERE id_personaggio = '" . $_SESSION['id_personaggio'] . "'");
        }
    }

    /* Redirect */
    if ($PARAMETERS['mode']['log_back_location'] == 'OFF') {
        $_SESSION['luogo'] = '-1';
        gdrcd_query("UPDATE personaggio
                     SET ora_entrata = NOW(), ultimo_luogo = '-1', ultimo_refresh = NOW(),
                         last_ip = '" . $_SERVER['REMOTE_ADDR'] . "', is_invisible = 0
                     WHERE id_personaggio = " . $_SESSION['id_personaggio']);
        header('Location: main.php?page=mappaclick&map_id=' . $_SESSION['mappa'], true);
    } else {
        gdrcd_query("UPDATE personaggio
                     SET ora_entrata = NOW(), ultimo_refresh = NOW(),
                         last_ip = '" . $_SERVER['REMOTE_ADDR'] . "', is_invisible = 0
                     WHERE id_personaggio = " . $_SESSION['id_personaggio']);
        header('Location: main.php?dir=' . $_SESSION['luogo'], true);
    }
} else {
?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/main.css" type="text/css">
        <link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/homepage.css" type="text/css">
        <link rel="shortcut icon" href="imgs/favicon.ico">
    </head>

    <body>
        <div class="error_box">
            <h2 class="error_major"><?php echo $MESSAGE['error']['unknown_username']; ?></h2>
            <span class="error_details"><?php echo $MESSAGE['error']['unknown_username_details']; ?></span>
            <span class="error_details"><?php echo $MESSAGE['error']['unknown_username_failure_count']; ?></span>
            <span class="error_details"><?php echo $iErrorsNumber ?? 0; ?></span>
            <span class="error_details"><?php echo $MESSAGE['error']['unknown_username_warning']; ?></span>
            <span class="error_details"><?php echo $MESSAGE['warning']['mailto']; ?></span>
            <a href="mailto:<?php echo $PARAMETERS['menu']['webmaster_email']; ?>">
                <?php echo $PARAMETERS['menu']['webmaster_email']; ?>
            </a>.
        </div>
    <?php
    session_destroy();
}
    ?>
    </body>

    </html>
    <?php
    gdrcd_close_connection($handleDBConnection);
