<?php session_start();


	require 'config.inc.php';   
	require 'includes/constant_values.inc.php';
	require 'vocabulary/'.$PARAMETERS['languages']['set'].'.vocabulary.php';
 	require 'includes/functions.inc.php';
 	
	/*Connessione al database*/
	$handleDBConnection = gdrcd_connect();
	

/*Leggo i dati del form di login*/
$login1 = $_POST['login1'];

if($PARAMETERS['mode']['encriptpassword']=='OFF'){
  $pass1 = $_POST['pass1'];
} else {
  $pass1 = gdrcd_encript($_POST['pass1']);
}


/** * Fix per il funzionamento in locale dell'engine
	* @author Blancks
*/

switch ($_SERVER['REMOTE_ADDR'])
{
	case '::1':
	case '127.0.0.1':	$host = 'localhost';	break;

	default:			$host = gethostbyaddr($_SERVER['REMOTE_ADDR']); 	break;
}
		
/** * Fine Fix
*/


/*Controllo se la postazione non sia stata esclusa dal sito*/
$result = gdrcd_stmt("SELECT * FROM blacklist WHERE ip LIKE ? AND granted = ?", array('si', $_SERVER['REMOTE_ADDR'], 0));

if (gdrcd_query($result, 'num_rows') > 0)
{
	gdrcd_query($result, 'free');
	
	/*Se la postazione è stata esclusa*/  
    echo '<div class="error_box"><h2 class="error_major">'.$MESSAGE['warning']['blacklisted'].'</h2></div>';
	/*Registro l'evento (Tentativo di connessione da postazione esclusa)*/
    gdrcd_stmt(
        "INSERT INTO log (
            nome_interessato, 
            autore, 
            data_evento, 
            codice_evento,
            descrizione_evento
        ) VALUES (?, ?, NOW(), ?, ?)",
        array('ssis', $login1, 'Login_procedure', BLOCKED, $_SERVER['REMOTE_ADDR'])
    );
        
    die();
} 




$login1 = ucwords(strtolower($login1));


/*Carico dal database il profilo dell'account (personaggio)*/
$result = gdrcd_stmt(
    "SELECT personaggio.nome, 
                 personaggio.cognome, 
                 personaggio.permessi, 
                 personaggio.sesso, 
                 personaggio.ultima_mappa, 
                 personaggio.ultimo_luogo, 
                 personaggio.id_razza, 
                 personaggio.ultimo_messaggio, 
                 personaggio.blocca_media, 
                 personaggio.ora_entrata, 
                 personaggio.ora_uscita, 
                 personaggio.ultimo_refresh, 
                 personaggio.posizione,
                 razza.sing_m, 
                 razza.sing_f, 
                 razza.icon AS url_img_razza 
    FROM personaggio 
        LEFT JOIN razza ON personaggio.id_razza = razza.id_razza 
    WHERE nome LIKE ? AND pass LIKE ? 
    LIMIT 1",
    array('ss', $login1, $pass1)
);

$record = null;

if (gdrcd_query($result, 'num_rows'))
    $record = gdrcd_query($result, 'fetch');

/*Se esiste un personaggio corrispondente al nome ed alla password specificati*/
/** * Aggiunti i controlli sugli orari di connessione e disconnessione per impedire i doppi login con gli stessi account
	* Se si esce non correttamente dal gioco, sarà possibile entrare dopo 5 minuti dall'ultimo refresh registrato
	
	*@author Blancks
*/
if (!empty($record) && $record['permessi'] > -1 && (strtotime($record['ora_entrata']) < strtotime($record['ora_uscita']) || (strtotime($record['ultimo_refresh'])+300) < time()))
{
	$_SESSION['login'] = $record['nome'];
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
	
	
	if ($record['sesso']=='f')
        $_SESSION['razza'] = $record['sing_f'];
	else
        $_SESSION['razza'] = $record['sing_m'];
        
	$_SESSION['img_razza'] = $record['url_img_razza'];
	$_SESSION['id_razza'] = $record['id_razza'];
	$_SESSION['posizione'] = $record['posizione'];
    
    if (empty($record['ultima_mappa']))
        $_SESSION['mappa'] = 1;
	else
        $_SESSION['mappa'] = $record['ultima_mappa'];
        
	if (empty($record['ultimo_luogo']))
        $_SESSION['luogo'] = -1;
	else
        $_SESSION['luogo'] = $record['ultimo_luogo'];
    
    $_SESSION['Tag'] = "";
	$_SESSION['last_message'] = 0;
	$_SESSION['last_istant_message'] = $record['ultimo_messaggio'];


	$res = gdrcd_stmt(
        "SELECT ruolo.gilda, 
                     ruolo.immagine 
        FROM ruolo 
            INNER JOIN clgpersonaggioruolo ON clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo 
        WHERE clgpersonaggioruolo.personaggio LIKE ?", 
        array('s', $record['nome'])
    );
	
    while($row = gdrcd_query($res, 'fetch')) {
        $_SESSION['gilda'] .= ',*'.$row['gilda'].'*';
        $_SESSION['img_gilda'] .= $row['immagine'].',';
	}
	
	gdrcd_query($res, 'free');
	
	
	/* Carico l'ultimo ip con cui si è collegato il personaggio */
    $lastloginres = gdrcd_stmt(
        "SELECT nome_interessato, 
                     autore 
        FROM log 
        WHERE nome_interessato LIKE ? AND codice_evento = ? 
        ORDER BY data_evento DESC 
        LIMIT 1", 
        array('si', $_SESSION['login'], LOGGEDIN)
    );
    
    $lastlogindata = null;
    
    if (gdrcd_query($lastloginres, 'num_rows'))
        $lastlogindata = gdrcd_query($lastloginres, 'fetch');

    /*Se la postazione ha già un cookie attivo per un personaggio differente registro l'evento (Possibile account multiplo)*/
	if (isset($_COOKIE['lastlogin']) && $_COOKIE['lastlogin'] != $_SESSION['login'])
	{
	    gdrcd_stmt(
            "INSERT INTO log (
                nome_interessato, 
                autore, 
                data_evento, 
                codice_evento, 
                descrizione_evento
            ) VALUES (?, ?, NOW(), ?, ?)",
            array('ssis', $_SESSION['login'], 'doppio (cookie)', ACCOUNTMULTIPLO, $_COOKIE['lastlogin'])
        );
	
	}elseif ($lastlogindata['autore'] == $_SERVER['REMOTE_ADDR'])
	{
        gdrcd_stmt(
            "INSERT INTO log (
                nome_interessato, 
                autore, 
                data_evento, 
                codice_evento, 
                descrizione_evento
            ) VALUES (?, ?, NOW(), ?, ?)",
            array('ssis', $_SESSION['login'], 'doppio (ip)', ACCOUNTMULTIPLO, $lastlogindata['nome_interessato'])
        );
	}
	

	/*Registro l'evento (Avvenuto login)*/
    gdrcd_stmt(
        "INSERT INTO log (
            nome_interessato, 
            autore, 
            data_evento, 
            codice_evento, 
            descrizione_evento
        ) VALUES (?, ?, NOW(), ?, ?)",
        array('ssis', $_SESSION['login'], $_SERVER['REMOTE_ADDR'], LOGGEDIN, $_SERVER['REMOTE_ADDR'])
    );	

	
} elseif (strtotime($record['ora_entrata']) > strtotime($record['ora_uscita']) || (strtotime($record['ultimo_refresh'])+300) > time())
{

	/*Se la postazione è stata esclusa*/  
    echo '<div class="error_box"><h2 class="error_major">'.$MESSAGE['warning']['double_connection'].'</h2></div>';
	/*Registro l'evento (Tentativo di connessione da postazione esclusa)*/
    gdrcd_stmt(
        "INSERT INTO log (
            nome_interessato, 
            autore, 
            data_evento, 
            codice_evento ,
            descrizione_evento
        ) VALUES (?, ?, NOW(), ?, ?)",
        array('ssis', $login1, 'Login_procedure', BLOCKED, $_SERVER['REMOTE_ADDR'])
    );
    
    die();

}else {

	/*Sono stati inseriti username e password errati*/
	$_SESSION['login'] = '';
	
	if (!empty($login1) && !empty($pass1))
	{
		/*Registro l'evento (Login errato)*/
		gdrcd_stmt(
            "INSERT INTO log (
                nome_interessato, 
                autore, 
                data_evento, 
                codice_evento, 
                descrizione_evento
            ) VALUES (?, ?, NOW(), ?, ?)",
            array('ssis', $_SESSION['login'], $host, ERRORELOGIN, $_SERVER['REMOTE_ADDR'])
        );   
   
		$result = gdrcd_stmt(
            "SELECT count(*) 
            FROM log 
            WHERE descrizione_evento LIKE ? 
                AND codice_evento = ? 
                AND DATE_ADD(data_evento, INTERVAL 60 MINUTE) > NOW()",
            array('si', $_SERVER['REMOTE_ADDR'], ERRORELOGIN)
        );
        
        $iErrorsNumber = 0;
        
        if (gdrcd_query($result, 'num_rows'))
        {
            $record = gdrcd_query($result, 'fetch');
            gdrcd_query($result, 'free');
            
            $iErrorsNumber = $record['count(*)'];
        }
        
		if ($iErrorsNumber >= 10)
		{
            gdrcd_stmt(
                "INSERT INTO blacklist (
                    ip, 
                    nota, 
                    ora, 
                    host
                ) VALUES (?, ?, NOW(), ?)",
                array('sss', $_SERVER['REMOTE_ADDR'], $login1.' (tenta password)', $Host)
            );            
		}
	}
}

/*Eseguo l'accesso*/
if (!empty($_SESSION['login'])) {

    if (gdrcd_controllo_esilio($_SESSION['login']) === TRUE) {
        session_destroy();
        echo '<a href="index.php">'.$PARAMETERS['info']['homepage_name'].'</a>';
        die();
    } else { 
    
        /*Creo un cookie*/
        setcookie('lastlogin',$_SESSION['login'],0,'','',0);

        if ($PARAMETERS['mode']['log_back_location'] == 'OFF')
        {
            $_SESSION['luogo'] = '-1';
	   
            /*Inserisco nei presenti*/
            gdrcd_stmt(
                "UPDATE personaggio SET 
                    ora_entrata = NOW(), 
                    ultimo_luogo = ?, 
                    ultimo_refresh = NOW(), 
                    last_ip = ?,  
                    is_invisible = ? 
                WHERE nome LIKE ?",
                array('isis', -1, $_SERVER['REMOTE_ADDR'], 0, $_SESSION['login'])
            );
            
            /*Redirigo alla pagina del gioco*/
            header('Location: main.php?page=mappaclick&map_id='.$_SESSION['mappa'], true);
            die();
	   
        }else
        {
            /*Inserisco nei presenti*/
            gdrcd_stmt(
                "UPDATE personaggio SET 
                    ora_entrata = NOW(), 
                    ultimo_refresh = NOW(), 
                    last_ip = ?,  
                    is_invisible = ? 
                WHERE nome LIKE ?",
                array('sis', $_SERVER['REMOTE_ADDR'], 0, $_SESSION['login'])
            );
            
            /*Redirigo alla pagina del gioco*/
            header('Location: main.php?dir='.$_SESSION['luogo'], true);
            die();
        }
    
    }

} else { /*Dichiaro il fallimento dell'operazione di login*/ 


?><html>
<head>
   <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
   <link rel='stylesheet' href='themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/main.css' TYPE='text/css'>
   <link rel='stylesheet' href='themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/homepage.css' TYPE='text/css'>
   <link rel='shortcut icon' href='favicon.ico' />
</head>
<body>

<div class="error_box">
<h2 class="error_major"><?php echo $MESSAGE['error']['unknown_username'];?></h2>
<span class="error_details"><?php echo $MESSAGE['error']['unknown_username_details']; ?></span>
<span class="error_details"><?php echo $MESSAGE['error']['unknown_username_failure_count']; ?></span>
<span class="error_details"><?php echo $iErrorsNumber; ?></span>
<span class="error_details"><?php echo $MESSAGE['error']['unknown_username_warning']; ?></span>
<span class="error_details"><?php echo $MESSAGE['warning']['mailto'];?></span>
<a href="mailto:<?php echo $PARAMETERS['menu']['webmaster_email']?>">
  <?php echo $PARAMETERS['menu']['webmaster_email']?>
</a>.
</div>

<?php session_destroy(); } ?>

</body></html>

<?php gdrcd_close_connection($handleDBConnection);?>