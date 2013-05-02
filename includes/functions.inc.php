<?php
/** * Funzioni di core di gdrcd
	* Il file contiene una revisione del core originario introdotto in GDRCD5
	* @version 5.2
	* @author Breaker
*/

/** * Funzionalità di dialogo col database 
	* Set di funzioni da core che implementano il dialogo gestito col db
*/

/** * Connettore al database MySql
*/
function gdrcd_connect()
{
	$db_user 	= $GLOBALS['PARAMETERS']['database']['username'];
	$db_pass 	= $GLOBALS['PARAMETERS']['database']['password'];
	$db_name 	= $GLOBALS['PARAMETERS']['database']['database_name'];
	$db_host 	= $GLOBALS['PARAMETERS']['database']['url'];
	$db_error 	= $GLOBALS['MESSAGE']['error']['db_not_found'];

    $db = mysql_connect($db_host, $db_user, $db_pass)or die(gdrcd_mysql_error());
    mysql_select_db($db_name)or die(gdrcd_mysql_error($db_error));

	return $db;
}


/** * Chiusura della connessione col db MySql
*/
function gdrcd_close_connection($db)
{
    mysql_close($db);
}


/** * Gestore delle query
*/
function gdrcd_query($sql, $mode = 'query')
{
	global $handleDBConnection;


	switch (strtolower(trim($mode)))
	{
		case 'query':
		
			switch (strtoupper(substr(trim($sql), 0, 6)))
			{
				case 'SELECT':
					
					$result = mysql_query($sql, $handleDBConnection)or die(gdrcd_mysql_error($sql));
					$row = mysql_fetch_assoc($result);
					mysql_free_result($result);
			
					return $row;
			
				break;
						
				default:
		
					return mysql_query($sql, $handleDBConnection)or die(gdrcd_mysql_error($sql));
		
				break;
			}
			
			
		case 'result':
			
			$result = mysql_query($sql, $handleDBConnection)or die(gdrcd_mysql_error($sql));
			return $result;
		
		break;
			
			
		case 'num_rows':
		
			return (int)mysql_num_rows($sql);
		
		break;
		
				
		case 'fetch':
				
			$row = mysql_fetch_assoc($sql);
			return $row;
				
		break;
		
				
		case 'free':
					
			return mysql_free_result($sql);
					
		break;
	}


}


/** * Funzione di recupero delle colonne e della loro dichiarazione della tabella specificata.
	* Si usa per la verifica dell'aggiornamento db di gdrcd5.1
*/
function gdrcd_check_tables($table)
{
        $result 	= gdrcd_query("SELECT * FROM $table LIMIT 1", 'result');
        $describe 	= gdrcd_query("SHOW COLUMNS FROM $table", 'result');
        $num 		= mysql_num_fields($result);
        
        $output = array();
        
        for ($i = 0; $i < $num; ++$i)
        {
			$field = mysql_fetch_field($result, $i);
			$field->auto_increment = (strpos(mysql_result($describe, $i, 'Extra'), 'auto_increment') === FALSE ? 0 : 1);
			$field->definition = mysql_result($describe, $i, 'Type');
			
			if ($field->not_null && !$field->primary_key)
					$field->definition .= ' NOT NULL';
					
			if ($field->def) 
					$field->definition .= " DEFAULT '" . mysql_real_escape_string($field->def) . "'";
					
			if ($field->auto_increment)
					$field->definition .= ' AUTO_INCREMENT';
			
			if ($key = mysql_result($describe, $i, 'Key'))
			{
					if ($field->primary_key) 
							$field->definition .= ' PRIMARY KEY';
					else 
							$field->definition .= ' UNIQUE KEY';
			}
			
			$field->len = mysql_field_len($result, $i);
			$output[$field->name] = $field;
		}
        
		
		return $output;
}


/** * Gestione degli errori tornati dalle query
*/
function gdrcd_mysql_error($details = false)
{
	$backtrace = debug_backtrace();

	$error_msg = 	'<strong>GDRCD MySQL Error</strong> [File: '. basename($backtrace[1]['file']) .'; Line: '. $backtrace[1]['line'] .']<br>'.
					'<strong>ErrorCode</strong>: '. mysql_errno() .'<br>'.
					'<strong>ErrorString</strong>: '. mysql_error();

	if ($details !== false)
			$error_msg .= '<br><br><strong>Dettaglio dell\'errore</strong>: ' . $details;
			

	return $error_msg;
}




/** * Funzionalità di escape
	* Set di funzioni escape per filtrare i possibili contenuti introdotti da un utente ;-)
*/


/** * Funzione di criptaggio delle password
*/
function gdrcd_encript($str)
{
	$encript_password = $GLOBALS['PARAMETERS']['mode']['encriptpassword'];
	$encript_algorithm = $GLOBALS['PARAMETERS']['mode']['encriptalgorithm'];

	if ($encript_password == 'ON')
	{
		switch ($encript_algorithm)
		{
			case 'MD5':		$str = md5($str);		break;
			case 'SHA-1':	$str = sha1($str);		break;
		}
	}
	
	
	return $str;
}


/** * Controllo della validità della password
	* Funzione work in progress, da implementare.
	
	* Deve essere disabilitabile da config
	* Funzionalità da ON/OFF:
	* - numero di caratteri minimo scelto dall'utente
	* - non accettazione di password contenenti lettere accentate
	* - non accettazione di password troppo semplici (ad esempio uguali al nickname del personaggio)
*/
function gdrcd_check_pass($str){
    return true;
}


/** * Funzione di filtraggio di codici malevoli negli input utente
*/
function gdrcd_filter($what, $str)
{
	switch (strtolower($what))
	{
		case 'in':
		case 'get':
				$str = addslashes(str_replace('\\','',$str));
		break;
		
		case 'num':
				$str = (int)$str;
		break;
		
		case 'out':
				$str = htmlentities($str, ENT_QUOTES, 'utf-8');
		break;
		
		case 'addslashes':
				$str = addslashes($str);
		break;
		
		case 'email':
				$str = (preg_match("#^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$#is", $str))? $str : false;
		break;
		
		case 'includes':
				$str = (preg_match("#[^:]#is"))? htmlentities($str, ENT_QUOTES) : false;
		break;
	}
	
	return $str;
}


/** * Funzioni di alias per gdrcd_filter()
*/
function gdrcd_filter_in($str){ return gdrcd_filter('in', $str); }
function gdrcd_filter_out($str){ return gdrcd_filter('out', $str); }
function gdrcd_filter_get($str){ return gdrcd_filter('get', $str); }
function gdrcd_filter_num($str){ return gdrcd_filter('num', $str); }
function gdrcd_filter_addslashes($str){ return gdrcd_filter('addslashes', $str); }
function gdrcd_filter_email($str){ return gdrcd_filter('email', $str); }
function gdrcd_filter_includes($str){ return gdrcd_filter('includes', $str); }


/** * Funzione di filtraggio degli elementi pericolosi in html
	* Serve a consentire l'uso di html e css in sicurezza nelle zone editabili della scheda
*/
function gdrcd_html_filter($str)
{
	$notAllowed = array(
				"#(<script.*?>.*?(<\/script>)?)#is" 	=> "Script non consentiti",
				"#(<img.*?\/?>)#is"						=> "Immagini non consentite",
				"#(<iframe.*?\/?>.*?(<\/iframe>)?)#is" 	=> "Frame non consentiti",
				"#(<object.*?>.*?(<\/object>)?)#is"		=> "Contenuti multimediali non consentiti",
				"#(<embed.*?\/?>.*?(<\/embed>)?)#is"	=> "Contenuti multimediali non consentiti",
				"#(url\(.*?\))#is"						=> "none",
				"#( on[a-zA-Z]+=\"?'?[^\s\"']+'?\"?)#is"=> "NonConsentiti",
				"#(javascript:[^\s\"']+)#is"			=> "NonConsentiti"
						);


	return preg_replace(array_keys($notAllowed), array_values($notAllowed), $str);
}



/** * Controlli di routine di gdrcd sui personaggi
	* Set di funzione per semplificare controlli frequenti sui personaggi nell'engine
*/


/** * Check validità della sessione utente
*/
function gdrcd_controllo_sessione()
{    
    if (empty($_SESSION['login']))
    {
	 	echo 	'<div class="error">', $GLOBALS['MESSAGE']['error']['session_expired'],
				'<br />', $GLOBALS['MESSAGE']['warning']['please_login_again'],
				'<a href="', $GLOBALS['PARAMETERS']['info']['site_url'], '">Homepage</a></div>';
	    
	    die();
    }
}


/** * Funzione di check per l'esilio del pg
*/
function gdrcd_controllo_esilio($pg)
{
   $exiled = gdrcd_query("SELECT autore_esilio, esilio, motivo_esilio FROM personaggio WHERE nome LIKE '$pg' LIMIT 1");

   if(strtotime($exiled['esilio']) > time())
   {
		echo 	'<div class="error">', gdrcd_filter_out($pg), ' ', 
				gdrcd_filter_out($GLOBALS['MESSAGE']['warning']['character_exiled']), ' ', 
				gdrcd_format_date($exiled['esilio']), ' (', $exiled['motivo_esilio'], ' - ', $exiled['autore_esilio'], ')</div>';
		
		return true;
   }
   
   return false;
}


/** * Controlla se l'utente è loggato da pochi minuti
	* si usa per l'icona entra/esce
*/
function gdrcd_check_time($time)
{
	$time_hours 	= date('H', strtotime($time));
	$time_minutes 	= date('i', strtotime($time));
	
	if ($time_hours == date('H'))
	{
		return date('i')-$time_minutes;
	
	}elseif ($time_hours == (date('H')-1) || $time_hours == (strftime('H')+11))
	{
	    return date('i')-$time_minutes+60;
	}
	
	return 61; 
}



/** * Utilità
	* Set di funzioni di utilità generica per l'engine
*/


/** * Provvede al caricamento degli elementi nell'interfaccia
	* E' approssimata ma funziona, se qualcuno vuol far di meglio si faccia avanti :p
*/
function gdrcd_load_modules($path)
{
	global $MESSAGE;
	global $PARAMETERS;

	if (file_exists($path))
			include($path);
	else
			echo $MESSAGE['interface']['layout_not_found'];
			
	
	unset($MESSAGE);
	unset($PARAMETERS);
}


/** * Funzione di formattazione per la data nel formato italiano
*/ 
function gdrcd_format_date($date_in)
{
	return date('d/m/Y', strtotime($date_in));
}


/** * Funzione di formattazione del tempo nel formato italiano
*/ 
function gdrcd_format_time($time_in)
{
	return date('H:i', strtotime($time_in));
}


/** * Funzione di formattazione data completa nel formato ita
*/ 
function gdrcd_format_datetime($datetime_in)
{
	return date('d/m/Y H:i', strtotime($datetime_in));
}

/** * Funzione di formattazione data completa nel formato ita per nome file da catalogare
*/
function gdrcd_format_datetime_cat($datetime_in)
{
	return date('Ymd_Hi', strtotime($datetime_in));
}

/** * Trasforma la prima lettera della parola in maiuscolo
*/
function gdrcd_capital_letter($word)
{
	return ucwords(strtolower($word));
}


/** * Crea una password casuale di 8 caratteri
*/
function gdrcd_genera_pass()
{
	$pass = '';
	for ($i=0; $i<8; ++$i){ $pass.= chr(mt_rand(0, 24) + ord("A")); }	
	
	return $pass;
}


/** * BBcode nativo di GDRCD

	* Secondo me, questo bbcode presenta non poche vulnerabilità.
	* Andrebbe aggiornata per essere più sicura
	* @author Blancks
*/
function gdrcd_bbcoder($str){
global $MESSAGE;
$str=gdrcd_close_tags('quote',$str);

$search = array(
	    '#\n#',
	    '#\[BR\]#is',
        '#\[B\](.+?)\[\/B\]#is', 
	    '#\[i\](.+?)\[\/i\]#is',
        '#\[U\](.+?)\[\/U\]#is',
	    '#\[center\](.+?)\[\/center\]#is',
        '#\[img\](.+?)\[\/img\]#is',
        '#\[redirect\](.+?)\[\/redirect\]#is',
        '#\[url=(.+?)\](.+?)\[\/url\]#is', 
        '#\[color=(.+?)\](.+?)\[\/color\]#is',
		  '#\[quote(?::\w+)?\]#i',
		  '#\[quote=(?:&quot;|"|\')?(.*?)["\']?(?:&quot;|"|\')?\]#i',
		  '#\[/quote(?::\w+)?\]#si'
     );
    $replace = array(
		'<br />',
		'<br />',
        '<span style="font-weight: bold;">$1</span>', 
        '<span style="font-style: italic;">$1</span>',
        '<span style="border-bottom: 1px solid;">$1</span>',
        '<div style="width:100%; text-align: center;">$1</div>',
        '<img src="$1">',
        '<meta http-equiv="Refresh" content="5;url=$1">',
        '<a href="$1">$2</a>',
        '<span style="color: $1;">$2</span>',
		  '<div class="bb-quote">'.$MESSAGE['interface']['forums']['link']['quote'].':<blockquote class="bb-quote-body">',
		  '<div class="bb-quote"><div class="bb-quote-name">$1 ha scritto:</div><blockquote class="bb-quote-body">',
		  '</blockquote></div>'
    );
    return preg_replace($search, $replace, $str); 
}

/*
	Aggiunge la chisura dei tag BBCode per impedire agli utenti di rompere l'HTML del sito
	Argomenti:
		$tag: il tag da controllare, senza le parentesi quadre, può essere un array di tag
		$body: il testo in cui controllare
	Ritorna:
		Il testo corretto
	
	@author leoblacksoul
*/
function gdrcd_close_tags($tag,$body){
  if(is_array($tag))
  	foreach($tag as $value)
		$body=close_tags($value,$body);
  else{
	  preg_match_all('/\['.$tag.'/i', $body, $matches);
	  $opentags = count($matches['0']);
	  preg_match_all('/\[\/'.$tag.'\]/i', $body, $matches);
	  $unclosed = $opentags - count($matches['0']);
	  for ($i = 0; $i < $unclosed; $i++)
		 $body .= '[/'.$tag.']';
  }
  return $body;
}

function gdrcd_redirect($url,$tempo = FALSE )
{
	if(!headers_sent() && $tempo == FALSE )
	{
		header('Location:' . $url);
	}
	elseif(!headers_sent() && $tempo != FALSE )
	{
		header('Refresh:' . $tempo . ';' . $url);
	}
	else
	{
		if($tempo == FALSE )
		{
			$tempo = 0;
		}
		echo "<meta http-equiv=\"refresh\" content=\"" . $tempo . ";" . $url . "\">";
	}
}


/** * La funzione sostituisce eventuali apachi in una stringa con parentesi quadre
*/
function gdrcd_angs($str)
{
    $search = array(
        '#\&lt;(.+?)\&gt;#is', 
		'#\<(.+?)>#is', 
	);
    $replace = array(
		'[$1]',
		'[$1]',
    );
    return preg_replace($search, $replace, $str); 
}


/** * La funzione serve a definire degli stili differenti per parole contornate da parentesi
	* Si usa in chat
*/
function gdrcd_chatcolor($str)
{
    $search = array(
        '#\&lt;(.+?)\&gt;#is', 
	    '#\[(.+?)\]#is', 
    );
    $replace = array(
	    '<span class="color2">&lt;$1&gt;</span>',
		'<span class="color2">&lt;$1&gt;</span>',
    );
    return preg_replace($search, $replace, $str); 
}

function gdrcd_chatme($user, $str)
{
		$search = $user;
    	$replace = '<span style="text-decoration:underline;">'.$search.'</span>';

    	return str_ireplace($search, $replace, $str); 
}

function gdrcd_chatme_master($user, $str)
{
		$search = $user;
    	$replace = '<span style="text-decoration:underline;">'.$search.'</span>';

    	return str_ireplace($search, $replace, $str); 
}
function gdrcd_list($str)
{
	switch(strtolower($str))
	{	
		case 'personaggi':
			$list = '<datalist id="personaggi">';
	 		$query = "SELECT nome FROM personaggio ORDER BY nome";
			$characters=gdrcd_query($query, 'result');

			while($option=gdrcd_query($characters, 'fetch'))
			{
				$list .= '<option value="'.$option['nome'].'" />';
			} 
			gdrcd_query($characters, 'free');
			$list .= '</datalist>';
			break;
	}
	
	return $list;
}

?>