<?php

/*Includo i file principali */
require_once(__DIR__.'/includes/required.php');

/*Connessione al database*/
$handleDBConnection = gdrcd_connect();

/*Leggo i dati del form di login*/
$login1 = gdrcd_filter('get', $_POST['login1']);
$pass1 = gdrcd_filter('get', $_POST['pass1']);
$theme = gdrcd_filter('get', $_POST['theme']);

/* Fix per il funzionamento in locale dell'engine */
switch($_SERVER['REMOTE_ADDR']) {
    case '::1':
    case '127.0.0.1':
        $host = 'localhost';
        break;
    default:
        $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        break;
}

/*Controllo se la postazione non sia stata esclusa dal sito*/
$result = gdrcd_query("SELECT * FROM blacklist WHERE ip = '".$_SERVER['REMOTE_ADDR']."' AND granted = 0", 'result');

if(gdrcd_query($result, 'num_rows') > 0) {
    gdrcd_query($result, 'free');

    /*Se la postazione è stata esclusa*/
    echo '<div class="error_box"><h2 class="error_major">'.$MESSAGE['warning']['blacklisted'].'</h2></div>';
    /*Registro l'evento (Tentativo di connessione da postazione esclusa)*/
    gdrcd_log_warning(
        'Tentativo di login bloccato',
        [
            'evento' => 'auth.login.bloccato.blacklist',
            'utente' => $login1,
            'ip' => $_SERVER['REMOTE_ADDR'],
        ]
    );
    exit();
}

/* Rendo maiuscola la prima lettera del nome */
$login1 = ucwords(strtolower(trim($login1)));

/* Carico dal database il profilo dell'account (personaggio) */
$record = gdrcd_stmt_one("SELECT 
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
    WHERE nome = ? ",

     [$login1]);
  // gdrcd_brute_debug(gdrcd_password_check($pass1, $record['pass']) );
/**
 * Se esiste un personaggio corrispondente al nome ed alla password specificati
 *
 * Controllo gli orari di connessione e disconnessione per impedire i doppi login con gli stessi account
 * Se si esce non correttamente dal gioco, sarà possibile entrare dopo 5 minuti dall'ultimo refresh registrato
 */
if( !empty($record) && gdrcd_password_check($pass1, $record['pass']) && ($record['permessi'] > -1) && (strtotime($record['ora_entrata']) < strtotime($record['ora_uscita']) || (strtotime($record['ultimo_refresh']) + $PARAMETERS['settings']['reconnection_cooldown']) < time())) {
    
    $_SESSION['id_personaggio'] = $record['id_personaggio'];
    $_SESSION['login'] = gdrcd_filter_in($record['nome']);
    $_SESSION['cognome'] = $record['cognome'];
    $_SESSION['permessi'] = $record['permessi'];
    $_SESSION['sesso'] = $record['sesso'];

    /* Controllo sul bloccaggio dei suoni per l'utente */
    $_SESSION['blocca_media'] = $record['blocca_media'];

    /* Se è stato scelto un tema valido, lo imposto */
    if(!empty($theme) && array_key_exists($theme, $PARAMETERS['themes']['available'])) {
        $_SESSION['theme'] = $theme;
    }

    /* Archiviazione dato utile per capire quanti nuovi topic in bacheca ci sono rispetto all'ultima visita */
    $_SESSION['ultima_uscita'] = $record['ora_uscita'];

    $_SESSION['razza'] = ($record['sesso'] == 'f') ? $record['sing_f'] : $record['sing_m'];

    $_SESSION['img_razza'] = $record['url_img_razza'];
    $_SESSION['id_razza'] = $record['id_razza'];
    $_SESSION['posizione'] = $record['posizione'];
    $_SESSION['mappa'] = (empty($record['ultima_mappa']) === true) ? 1 : $record['ultima_mappa'];
    $_SESSION['luogo'] = (empty($record['ultimo_luogo']) === true) ? -1 :  $_SESSION['luogo'] = $record['ultimo_luogo'];
    $_SESSION['tag'] = "";
    $_SESSION['last_message'] = 0;

    $res = gdrcd_query("SELECT ruolo.gilda, ruolo.immagine FROM ruolo JOIN clgpersonaggioruolo ON clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo WHERE clgpersonaggioruolo.id_personaggio = '".gdrcd_filter('in', $record['id_personaggio'])."'", 'result');

    while($row = gdrcd_query($res, 'fetch')) {
        $_SESSION['gilda'] .= ',*'.$row['gilda'].'*';
        $_SESSION['img_gilda'] .= $row['immagine'].',';
    }
    gdrcd_query($res, 'free');
 
    /* Se la postazione ha già un cookie attivo per un personaggio differente registro l'evento (Possibile account multiplo) */
    if((isset($_COOKIE['lastlogin']) === true) && ($_COOKIE['lastlogin'] != $_SESSION['id_personaggio'])) {

        $otherAccountData = gdrcd_query("SELECT id_personaggio, nome FROM personaggio WHERE id_personaggio = ". $_SESSION['id_personaggio']);
        $otherAccountNome = !empty($otherAccountData)? $otherAccountData['nome'] : '-Sconosciuto-';
        gdrcd_log_warning(
            'Rilevato possibile account multiplo tramite cookie attivo',
            [
                'evento' => 'auth.multiaccount.cookie',
                'utente_corrente' => $_SESSION['login'],
                'id_altro_account' => $otherAccountData['id_personaggio'],
                'altro_account' => $otherAccountNome,
            ],
            $_SESSION['id_personaggio']
        );
        

    } 
    //carico gli ultimi login con lo stesso ip selezionando solo i successi e  i distinti utenti
    $lastlogindata = [];
     foreach(gdrcd_stmt_all(
        "SELECT 
            JSON_UNQUOTE(JSON_EXTRACT(contesto, '$.utente')) AS nome_interessato,
            JSON_UNQUOTE(JSON_EXTRACT(contesto, '$.ip')) AS autore,
            JSON_UNQUOTE(JSON_EXTRACT(contesto, '$.id_personaggio')) AS id_personaggio
        FROM log
        WHERE   JSON_EXTRACT(contesto, '$.ip') = ?
        AND JSON_EXTRACT(contesto, '$.evento') = ?
        ORDER BY data DESC
        ",
        [ $_SERVER['REMOTE_ADDR'], 'auth.login.successo']) as $row) {
        $lastlogindata[] = $row;
    }
    $lastlogindata = array_unique($lastlogindata, SORT_REGULAR);
    if(count($lastlogindata) > 1) {
        //ciclo i nominativi e inserisco i log per ogni utente con lo stesso ip
        foreach($lastlogindata as $row) {
            if($row['autore'] == $_SERVER['REMOTE_ADDR'] && $row['nome_interessato'] != $_SESSION['login']) {
                
                gdrcd_log_warning(
                    'Possibile correlazione tra account tramite IP',
                    [
                        'evento' => 'auth.multiaccount.ip',
                        'utente_corrente' => $_SESSION['login'],
                        'altro_account' => $row['nome_interessato'],
                        'id_altro_account' => $row['id_personaggio'],
                        'ip' => $_SERVER['REMOTE_ADDR']
                    ],
                    $_SESSION['id_personaggio']
                );
            }
        }
        /*possibile account multiplo tramite IP*/
        

    }

    /* Registro l'evento (Avvenuto login) */
    gdrcd_log_info(
        'Login effettuato con successo',
         [
            'evento' => 'auth.login.successo',
            'utente' => $_SESSION['login'],
            'ip' => $_SERVER['REMOTE_ADDR'],
        ],
        $_SESSION['id_personaggio']
    );
} 
 elseif(
    !empty($record) && gdrcd_password_check($pass1, $record['pass']) && ($record['permessi'] > -1) &&
    (strtotime($record['ora_entrata']) > strtotime($record['ora_uscita']) || 
     (strtotime($record['ultimo_refresh']) + $PARAMETERS['settings']['reconnection_cooldown']) > time())
) {
    /* Se la postazione è stata esclusa */
    echo '<div class="error_box"><h2 class="error_major">'.$MESSAGE['warning']['double_connection'].'</h2></div>';
    /* Registro l'evento (Tentativo di connessione da postazione esclusa) */
    gdrcd_log_warning(
        'Tentativo di connessione da postazione esclusa',
        [
            'evento' => 'auth.login.bloccato',
            'utente' => $login1,
            'id_personaggio' => $record['id_personaggio'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ],
        $record['id_personaggio']
    );
    exit();
} else {
    /* Sono stati inseriti username e password errati */
    $_SESSION['id_personaggio'] = null;
    $_SESSION['login'] = '';

    if(($login1 != '') && ($pass1 != '')) {
        /* Registro l'evento (Login errato) */
            gdrcd_log_notice(
                'Tentativo di login non riuscito',
                [
                    'evento' => 'auth.login.fallito',
                    'utente' => $login1,
                    'host' => $host,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                ]
            );

        $record = gdrcd_stmt_one(
            "SELECT COUNT(*) AS totale
            FROM log
            WHERE contesto LIKE ?
            AND contesto LIKE ?
            AND DATE_ADD(data, INTERVAL 60 MINUTE) > NOW()",
            [
                '%\"ip\":\"' . $_SERVER['REMOTE_ADDR'] . '\"%',
                '%\"evento\":\"auth.login.fallito\"%'
            ]
        );  

        /* Se ho tentato 10 login fallendo nel giro di un ora */
        $iErrorsNumber = $record['totale'];

        if($iErrorsNumber >= 10) {
            gdrcd_query("INSERT INTO blacklist (ip, nota, ora, host) VALUES ('".$_SERVER['REMOTE_ADDR']."', '".$login1." (tenta password)', NOW(), '".$Host."')");
        }
    }
}

/* Eseguo l'accesso */
if( !empty($_SESSION['id_personaggio']) ) {
    if(gdrcd_controllo_esilio($_SESSION['id_personaggio']) === true) {
        session_destroy();
        echo '<a href="index.php">'.$PARAMETERS['info']['homepage_name'].'</a>';
        exit();
    } else {
        /* Creo un cookie */
        setcookie('lastlogin', $_SESSION['id_personaggio'], 0, '', '', 0);

        if($PARAMETERS['settings']['auto_salary'] == 'ON') {
            /* Stipendio */
            $row = gdrcd_query("SELECT soldi, banca, ultimo_stipendio FROM personaggio WHERE id_personaggio = '". $_SESSION['id_personaggio'] ."' LIMIT 1");

            if($row['ultimo_stipendio'] != strftime("%Y-%m-%d")) {
                $soldi=0+$row['soldi'];
                $banca=0+$row['banca'];
                $ultimo=$row['ultimo_stipendio'];
                $query="SELECT ruolo.stipendio FROM clgpersonaggioruolo LEFT JOIN ruolo on clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo WHERE clgpersonaggioruolo.id_personaggio = '".$_SESSION['id_personaggio']."'";
                $result=gdrcd_query($query, 'result');
                $stipendio=0;
                while($row=gdrcd_query($result, 'fetch')) {
                    $stipendio+=$row['stipendio'];
                }
                gdrcd_query("UPDATE personaggio SET banca = banca + ".$stipendio.", ultimo_stipendio = NOW() WHERE id_personaggio = '". $_SESSION['id_personaggio'] ."'");
            }
        }

        if($PARAMETERS['mode']['log_back_location'] == 'OFF') {
            $_SESSION['luogo'] = '-1';
            /* Inserisco nei presenti */
            gdrcd_query("UPDATE personaggio SET ora_entrata = NOW(), ultimo_luogo='-1', ultimo_refresh = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."', is_invisible = 0 WHERE id_personaggio = ". $_SESSION['id_personaggio']);

            /* Redirigo alla pagina del gioco */
            header('Location: main.php?page=mappaclick&map_id='.$_SESSION['mappa'], true);
        } else {
            /* Inserisco nei presenti */
            gdrcd_query("UPDATE personaggio SET ora_entrata = NOW(), ultimo_refresh = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."',  is_invisible = 0 WHERE id_personaggio = ". $_SESSION['id_personaggio']);

            /* Redirigo alla pagina del gioco */
            header('Location: main.php?dir='.$_SESSION['luogo'], true);
        }
    }//else
} else {
    /* Dichiaro il fallimento dell'operazione di login */
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
        <link rel='stylesheet' href='themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/main.css' TYPE='text/css'>
        <link rel='stylesheet' href='themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/homepage.css'
              TYPE='text/css'>
        <link rel='shortcut icon' href='imgs/favicon.ico' />
    </head>
    <body>
        <div class="error_box">
            <h2 class="error_major"><?php echo $MESSAGE['error']['unknown_username']; ?></h2>
            <span class="error_details"><?php echo $MESSAGE['error']['unknown_username_details']; ?></span>
            <span class="error_details"><?php echo $MESSAGE['error']['unknown_username_failure_count']; ?></span>
            <span class="error_details"><?php echo $iErrorsNumber; ?></span>
            <span class="error_details"><?php echo $MESSAGE['error']['unknown_username_warning']; ?></span>
            <span class="error_details"><?php echo $MESSAGE['warning']['mailto']; ?></span>
            <a href="mailto:<?php echo $PARAMETERS['menu']['webmaster_email'] ?>">
                <?php echo $PARAMETERS['menu']['webmaster_email'] ?>
            </a> .
        </div>
    <?php
    session_destroy();
}
?>
    </body>
</html>
<?php
gdrcd_close_connection($handleDBConnection);
