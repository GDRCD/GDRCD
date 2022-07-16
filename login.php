<?php

/*Includo i file principali */
require_once(__DIR__.'/includes/required.php');

/*Connessione al database*/
$handleDBConnection = gdrcd_connect();

/*Leggo i dati del form di login*/
$login1 = gdrcd_filter('get', $_POST['login1']);
$pass1 = gdrcd_filter('get', $_POST['pass1']);

/** * Fix per il funzionamento in locale dell'engine
 * @author Blancks
 */
switch($_SERVER['REMOTE_ADDR']) {
    case '::1':
    case '127.0.0.1':
        $host = 'localhost';
        break;
    default:
        $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        break;
}
/** * Fine Fix
 */

/*Controllo se la postazione non sia stata esclusa dal sito*/
$result = gdrcd_query("SELECT * FROM blacklist WHERE ip = '".$_SERVER['REMOTE_ADDR']."' AND granted = 0", 'result');

if(gdrcd_query($result, 'num_rows') > 0) {
    gdrcd_query($result, 'free');

    /*Se la postazione è stata esclusa*/
    echo '<div class="error_box"><h2 class="error_major">'.$MESSAGE['warning']['blacklisted'].'</h2></div>';
    /*Registro l'evento (Tentativo di connessione da postazione esclusa)*/
    gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) VALUES ('".$login1."', 'Login_procedure', NOW(), ".BLOCKED.", '".$_SERVER['REMOTE_ADDR']."')");
    exit();
}

/*Rede maiuscola la prima leggera del nome:)*/
/*$login1=strtolower($login1);
$Maiusc=substr($login1,0,1);
$Maiusc=strtoupper($Maiusc);
$login1=$Maiusc.substr($login1,1);
*/
/**    * Magari però facciamolo meglio ;-)
 * @author Blancks
 */
$login1 = ucwords(strtolower(trim($login1)));

/*Carico dal database il profilo dell'account (personaggio)*/
$record = gdrcd_query("SELECT personaggio.pass, personaggio.nome, personaggio.cognome, personaggio.permessi, personaggio.sesso, personaggio.ultima_mappa, personaggio.ultimo_luogo, personaggio.id_razza, personaggio.blocca_media, personaggio.ora_entrata, personaggio.ora_uscita, personaggio.ultimo_refresh, razza.sing_m, razza.sing_f, razza.icon AS url_img_razza FROM personaggio LEFT JOIN razza ON personaggio.id_razza = razza.id_razza WHERE nome = '".gdrcd_filter('in', $login1)."' LIMIT 1");

/*Se esiste un personaggio corrispondente al nome ed alla password specificati*/
/** * Aggiunti i controlli sugli orari di connessione e disconnessione per impedire i doppi login con gli stessi account
 * Se si esce non correttamente dal gioco, sarà possibile entrare dopo 5 minuti dall'ultimo refresh registrato
 * @author Blancks
 */
if( ! empty($record) and gdrcd_password_check($pass1, $record['pass']) && ($record['permessi'] > -1) && (strtotime($record['ora_entrata']) < strtotime($record['ora_uscita']) || (strtotime($record['ultimo_refresh']) + 300) < time())) {
    $_SESSION['login'] = gdrcd_filter_in($record['nome']);
    $_SESSION['cognome'] = $record['cognome'];
    $_SESSION['permessi'] = $record['permessi'];
    $_SESSION['sesso'] = $record['sesso'];

    /** * Controllo sul bloccaggio dei suoni per l'utente
     * @author Blancks
     */
    $_SESSION['blocca_media'] = $record['blocca_media'];

    /** * Archiviazione dato utile per capire quanti nuovi topic in bacheca ci sono rispetto all'ultima visita
     * @author Blancks
     */
    $_SESSION['ultima_uscita'] = $record['ora_uscita'];

    $_SESSION['razza'] = ($record['sesso'] == 'f') ? $record['sing_f'] : $record['sing_m'];

    $_SESSION['img_razza'] = $record['url_img_razza'];
    $_SESSION['id_razza'] = $record['id_razza'];
    $_SESSION['posizione'] = $record['posizione'];
    $_SESSION['mappa'] = (empty($record['ultima_mappa']) === true) ? 1 : $record['ultima_mappa'];
    $_SESSION['luogo'] = (empty($record['ultimo_luogo']) === true) ? -1 :  $_SESSION['luogo'] = $record['ultimo_luogo'];
    $_SESSION['tag'] = "";
    $_SESSION['last_message'] = 0;

    $res = gdrcd_query("SELECT ruolo.gilda, ruolo.immagine FROM ruolo JOIN clgpersonaggioruolo ON clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo WHERE clgpersonaggioruolo.personaggio = '".gdrcd_filter('in', $record['nome'])."'", 'result');

    while($row = gdrcd_query($res, 'fetch')) {
        $_SESSION['gilda'] .= ',*'.$row['gilda'].'*';
        $_SESSION['img_gilda'] .= $row['immagine'].',';
    }
    gdrcd_query($res, 'free');

    /* Carico l'ultimo ip con cui si è collegato il personaggio */
    $lastlogindata = gdrcd_query("SELECT nome_interessato, autore FROM log WHERE nome_interessato = '".gdrcd_filter('in', $_SESSION['login'])."' AND codice_evento=".LOGGEDIN." ORDER BY data_evento DESC LIMIT 1");

    /*Se la postazione ha già un cookie attivo per un personaggio differente registro l'evento (Possibile account multiplo)*/
    if((isset($_COOKIE['lastlogin']) === true) && ($_COOKIE['lastlogin'] != $_SESSION['login'])) {
        gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_SESSION['login'])."','doppio (cookie)', NOW(), ".ACCOUNTMULTIPLO.", '".$_COOKIE['lastlogin']."')");
    } elseif($lastlogindata['autore'] == $_SERVER['REMOTE_ADDR'] && $lastlogindata['nome_interessato'] != $_SESSION['login'] ) {
        gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_SESSION['login'])."','doppio (ip)', NOW(), ".ACCOUNTMULTIPLO.", '".gdrcd_filter('in', $lastlogindata['nome_interessato'])."')");
    }

    /*Registro l'evento (Avvenuto login)*/
    gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_SESSION['login'])."','".$_SERVER['REMOTE_ADDR']."', NOW(), ".LOGGEDIN." ,'".$_SERVER['REMOTE_ADDR']."')");
} elseif(strtotime($record['ora_entrata']) > strtotime($record['ora_uscita']) || (strtotime($record['ultimo_refresh']) + 300) > time()) {
    /*Se la postazione è stata esclusa*/
    echo '<div class="error_box"><h2 class="error_major">'.$MESSAGE['warning']['double_connection'].'</h2></div>';
    /*Registro l'evento (Tentativo di connessione da postazione esclusa)*/
    gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) VALUES ('".$login1."', 'Login_procedure', NOW(), ".BLOCKED.", '".$_SERVER['REMOTE_ADDR']."')");
    exit();
} else {
    /*Sono stati inseriti username e password errati*/
    $_SESSION['login'] = '';

    if(($login1 != '') && ($pass1 != '')) {
        /*Registro l'evento (Login errato)*/
        gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_SESSION['login'])."','".$host."', NOW(), ".ERRORELOGIN." ,'".$_SERVER['REMOTE_ADDR']."')");

        $record = gdrcd_query("SELECT count(*) FROM log WHERE descrizione_evento = '".$_SERVER['REMOTE_ADDR']."' AND codice_evento = ".ERRORELOGIN." AND DATE_ADD(data_evento, INTERVAL 60 MINUTE) > NOW()");
        /*Se ho tentato 10 login fallendo nel giro di un ora*/
        $iErrorsNumber = $record['count(*)'];

        if($iErrorsNumber >= 10) {
            gdrcd_query("INSERT INTO blacklist (ip, nota, ora, host) VALUES ('".$_SERVER['REMOTE_ADDR']."', '".$login1." (tenta password)', NOW(), '".$Host."')");
        }
    }
}
/*Eseguo l'accesso*/
if($_SESSION['login'] != '') {
    if(gdrcd_controllo_esilio($_SESSION['login']) === true) {
        session_destroy();
        echo '<a href="index.php">'.$PARAMETERS['info']['homepage_name'].'</a>';
        exit();
    } else {
        /*Creo un cookie*/
        setcookie('lastlogin', $_SESSION['login'], 0, '', '', 0);

        if($PARAMETERS['settings']['auto_salary'] == 'ON') {
            /*Stipendio*/
            $row = gdrcd_query("SELECT soldi, banca, ultimo_stipendio FROM personaggio WHERE nome = '".$_SESSION['login']."' LIMIT 1");

            if($row['ultimo_stipendio'] != strftime("%Y-%m-%d")) {
                $soldi=0+$row['soldi'];
                $banca=0+$row['banca'];
                $ultimo=$row['ultimo_stipendio'];
                $query="SELECT ruolo.stipendio FROM clgpersonaggioruolo LEFT JOIN ruolo on clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo WHERE clgpersonaggioruolo.personaggio = '".$_SESSION['login']."'";
                $result=gdrcd_query($query, 'result');
                $stipendio=0;
                while($row=gdrcd_query($result, 'fetch')) {
                    $stipendio+=$row['stipendio'];
                }
                gdrcd_query("UPDATE personaggio SET banca = banca + ".$stipendio.", ultimo_stipendio = NOW() WHERE nome = '".$_SESSION['login']."'");
            }
        }

        if($PARAMETERS['mode']['log_back_location'] == 'OFF') {
            $_SESSION['luogo'] = '-1';
            /*Inserisco nei presenti*/
            gdrcd_query("UPDATE personaggio SET ora_entrata = NOW(), ultimo_luogo='-1', ultimo_refresh = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."',  is_invisible = 0 WHERE nome =  '".gdrcd_filter('in', $_SESSION['login'])."'");

            /*Redirigo alla pagina del gioco*/
            header('Location: main.php?page=mappaclick&map_id='.$_SESSION['mappa'], true);
        } else {
            /*Inserisco nei presenti*/
            gdrcd_query("UPDATE personaggio SET ora_entrata = NOW(), ultimo_refresh = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."',  is_invisible = 0 WHERE nome =  '".$_SESSION['login']."'");

            /*Redirigo alla pagina del gioco*/
            header('Location: main.php?dir='.$_SESSION['luogo'], true);
        }
    }//else
} else {
    /*Dichiaro il fallimento dell'operazione di login*/
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