<?php

/**
 * Funzioni di CORE di GDRCD
 */

/**
 * Funzionalità di escape
 * Set di funzioni escape per filtrare i possibili contenuti introdotti da un utente ;-)
 */

/**
 * Funzione di hashing delle password.
 * @param string $str : la password o stringa di cui calcolare l'hash
 * @return string|null l'hash calcolato a partire da $str con l'algoritmo specificato nella configurazione
 */
function gdrcd_encript($str)
{
    require_once(dirname(__FILE__) . '/PasswordHash.php');
    $hasher = new PasswordHash(8, true);

    return $hasher->HashPassword($str);
}

/**
 * Funzione di controllo sulla corrispondenza tra password e hash
 * @param $pass
 * @param $stored
 * @return bool
 */
function gdrcd_password_check($pass, $stored)
{
    require_once(dirname(__FILE__) . '/PasswordHash.php');
    $hasher = new PasswordHash(8, true);

    return $hasher->CheckPassword($pass, $stored);
}

/**
 * TODO Controllo della validità della password
 * Funzione work in progress, da implementare.
 * Deve essere disabilitabile da config
 * Funzionalità da ON/OFF:
 * - numero di caratteri minimo scelto dall'utente
 * - non accettazione di password contenenti lettere accentate
 * - non accettazione di password troppo semplici (ad esempio uguali al nickname del personaggio)
 * @param string $str : la password da controllare
 * @return true se la password è valida, false altrimenti
 */
function gdrcd_check_pass($str)
{
    return true;
}

/**
 * Funzione di filtraggio di codici malevoli negli input utente
 * @param string $what : modalità da utilizzare per controllare la stringa. Sono opzioni valide: in o get, num, out, addslashes, email, includes
 * @param string $str : la stringa da controllare
 * @return string|int|false una versione filtrata di $str
 */
function gdrcd_filter($what, $str)
{
    switch (strtolower($what)) {
        case 'in':
        case 'get':
            $str = addslashes(str_replace('\\', '', $str));
            break;

        case 'num':
            $str = (int)$str;
            break;

        case 'out':
            $str = gdrcd_html_filter(htmlentities($str, ENT_QUOTES, "UTF-8"));
            break;

        case 'addslashes':
            $str = addslashes($str);
            break;

        case 'email':
            $str = (preg_match("#^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$#is", $str)) ? $str : false;
            break;

        case 'includes':
            $str = (preg_match("#[^:]#is", $str)) ? htmlentities($str, ENT_QUOTES) : false;
            break;

        case 'url':
            $str = urlencode($str);
            break;

        case 'fullurl':
            $str = filter_var(str_replace(' ', '%20', $str), FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
            break;
    }

    return $str;
}

/**
 * Funzioni di alias per gdrcd_filter()
 */
function gdrcd_filter_in($str)
{
    return gdrcd_filter('in', $str);
}

function gdrcd_filter_out($str)
{
    return gdrcd_filter('out', $str);
}

function gdrcd_filter_get($str)
{
    return gdrcd_filter('get', $str);
}

function gdrcd_filter_num($str)
{
    return gdrcd_filter('num', $str);
}

function gdrcd_filter_addslashes($str)
{
    return gdrcd_filter('addslashes', $str);
}

function gdrcd_filter_email($str)
{
    return gdrcd_filter('email', $str);
}

function gdrcd_filter_includes($str)
{
    return gdrcd_filter('includes', $str);
}

function gdrcd_filter_url($str)
{
    return gdrcd_filter('url', $str);
}

/**
 * Funzione basilare di filtraggio degli elementi pericolosi in html
 * Serve a consentire l'uso di html e css in sicurezza nelle zone editabili della scheda
 * Il livello di filtraggio viene controllato da config: $PARAMETERS['settings']['html']
 * @param string $str : la stringa da filtrare
 * @return $str con gli elementi illegali sosituiti con una stringa di errore
 */
function gdrcd_html_filter($str)
{
    $notAllowed = [
        "#<script(.*?)>(.*?)</script>#is" => "Script non consentiti",
        "#(<iframe.*?\/?>.*?(<\/iframe>)?)#is" => "Frame non consentiti",
        "#(<object.*?>.*?(<\/object>)?)#is" => "Contenuti multimediali non consentiti",
        "#(<embed.*?\/?>.*?(<\/embed>)?)#is" => "Contenuti multimediali non consentiti",
        "#\bon([a-z]*?)=(['|\"])(.*?)\\2#mi" => " ",
        "#(javascript:[^\s\"']+)#is" => ""
    ];

    if ($GLOBALS['PARAMETERS']['settings']['html'] == HTML_FILTER_HIGH) {
        $notAllowed = array_merge($notAllowed, [
            "#(<img.*?\/?>)#is" => "Immagini non consentite",
            "#(url\(.*?\))#is" => "none",
        ]);
    }

    return preg_replace(array_keys($notAllowed), array_values($notAllowed), $str);
}

/**
 * Controlli di routine di gdrcd sui personaggi
 * Set di funzione per semplificare controlli frequenti sui personaggi nell'engine
 */

/**
 * Check validità della sessione utente
 */
function gdrcd_controllo_sessione()
{
    if (empty($_SESSION['login'])) {
        echo '<div class="error">', $GLOBALS['MESSAGE']['error']['session_expired'], '<br />', $GLOBALS['MESSAGE']['warning']['please_login_again'], '<a href="', $GLOBALS['PARAMETERS']['info']['site_url'], '">Homepage</a></div>';
        die();
    }
}

/**
 * Controlla se l'utente è esiliato o meno
 * @param int $id_personaggio
 * @param bool $return default false. Se posto su true la funzione ritorna il messaggio d'esilio come stringa
 * @return bool|string false se il pg non è stato esiliato. True se return è stato impostato a false, altrimenti una stringa con la motivazione dell'esilio
 */
function gdrcd_controllo_esilio($id_personaggio, $return = false)
{
    $exiled = gdrcd_query("SELECT nome, autore_esilio, esilio, motivo_esilio FROM personaggio WHERE id_personaggio = '" . gdrcd_filter('in', $id_personaggio) . "' LIMIT 1");

    if (strtotime($exiled['esilio']) > time()) {

        $message = gdrcd_filter_out($exiled['nome'])
            . ' ' . gdrcd_filter_out($GLOBALS['MESSAGE']['warning']['character_exiled'])
            . ' ' . gdrcd_format_date($exiled['esilio'])
            . ' (' . $exiled['motivo_esilio'] . ' - ' . $exiled['autore_esilio'] . ')';

        if ($return) {
            return $message;
        }

        echo '<div class="error">', $message, '</div>';

        return true;
    }

    return false;
}

/**
 * Controlla se l'utente possiede i permessi indicati
 * @param string $permesso : il permesso da controllare
 * @return true se il pg possiede i permessi, false altrimenti
 */
function gdrcd_controllo_permessi($permesso)
{
    return (bool)$_SESSION['permessi'] >= $permesso;
}

/**
 * Funzione controllo permessi forum
 * @param int $tipo
 * @param mixed $proprietari
 * @return bool
 */
function gdrcd_controllo_permessi_forum($tipo, $proprietari = '')
{
    $tipo = gdrcd_filter('num', $tipo);
    $perm = gdrcd_filter('num', $_SESSION['permessi']);
    $razza = gdrcd_filter('num', $_SESSION['id_razza']);
    $gilda = gdrcd_filter('out', $_SESSION['gilda']);

    switch ($tipo) {
        case PERTUTTI:
        case INGIOCO:
            return true;

        case SOLORAZZA:
            return (($razza == $proprietari) || ($perm >= MODERATOR));

        case SOLOGILDA:

            if (empty($proprietari)) {
                return false;
            } else {
                return (strpos($gilda, '*' . $proprietari . '*') || ($perm >= MODERATOR));
            }

        case SOLOMASTERS:
            return ($perm >= GAMEMASTER);

        case SOLOMODERATORS:
            return ($perm >= MODERATOR);

        default:
            return ($perm >= SUPERUSER);
    }
}

// TODO: Eliminare questa funzione
/**
 * @deprecated
 * Funzione controllo permessi chat
 * @param $location
 * @return bool
 * @throws Exception
 */
function gdrcd_controllo_chat($location)
{
    global $PARAMETERS;

    $location = gdrcd_filter('num', $location);

    $chat_data = gdrcd_query("SELECT nome, stanza_apparente, invitati, privata, proprietario, scadenza FROM mappa WHERE id=" . $location . " LIMIT 1");
    $private = gdrcd_filter('num', $chat_data['privata']);

    // Se la stanza è privata
    if ($private) {

        // Controllo permessi utente
        $spy_room_enabled = $PARAMETERS['mode']['spyprivaterooms'] === 'ON';
        $isModerator = ($_SESSION['permessi'] >= MODERATOR);
        if ($spy_room_enabled && $isModerator) {
            return true;
        }

        // Controllo scadenza stanza, se non scaduta
        $expiring = $chat_data['scadenza'];
        $actual_time = strftime('%Y-%m-%d %H:%M:%S');
        if ($expiring > $actual_time) {

            // Controllo membri della stanza
            $owner = gdrcd_filter('out', $chat_data['proprietario']);
            $me = gdrcd_filter('out', gdrcd_capital_letter($_SESSION['login']));
            $mineGuild = gdrcd_filter('out', $_SESSION['gilda']);
            $chat_invited = explode(',', $chat_data['invitati']);

            if ($owner === $me) { // Se l'utente è il proprietario
                return true;
            }

            if (strpos($mineGuild, $owner)) {  // Se l'utente è nella gilda del proprietario
                return true;
            }

            if (in_array($me, $chat_invited, true)) { // Se l'utente è tra gli invitati
                return true;
            }
        }
    } else {
        return true;
    }

    return false;
}

/**
 * Controlla se l'utente è loggato da pochi minuti. Utile per l'icona entra/esce
 * @param string $time : data in un formato leggibile da strtotime()
 * @return int
 */
function gdrcd_check_time($time)
{
    // Converto l'orario $time in un formato leggibile
    $time_hours = (int)date('H', strtotime($time));
    $time_minutes = (int)date('i', strtotime($time));
    // Converto l'orario corrente in un formato leggibile
    $current_hours = (int)date('H');
    $current_minutes = (int)date('i');

    if ($time_hours == $current_hours) {
        return $current_minutes - $time_minutes;
    } elseif ($time_hours == ($current_hours - 1) || $time_hours == ($current_hours + 11)) {
        return $current_minutes - $time_minutes + 60;
    }

    return 61;
}

/**
 * Utilità
 * Set di funzioni di utilità generica per l'engine
 */

/**
 * Provvede al caricamento degli elementi nell'interfaccia
 * E' approssimata ma funziona, se qualcuno vuol far di meglio si faccia avanti
 * @param string $page : il percorso filesystem del file da includere
 * @param array $params : un array di dati aggiuntivi passabili al modulo
 * @param bool $throwOnError default false.
 */
function gdrcd_load_modules($page, $params = [], $throwOnError = false)
{
    global $MESSAGE;
    global $PARAMETERS;

    // Costruisco i parametri del modulo
    $MODULE = $params;

    // Sostituisco i __ con i /
    $page = gdrcd_pages_format($page);

    try {
        // Controllo la tipologia di informazione passata (file o page) e poi determino il percorso del modulo
        $modulePath = is_file($page) ? $page : gdrcd_pages_path($page);

        if (!file_exists($modulePath)) {
            throw new Exception($MESSAGE['interface']['layout_not_found']);
        }

        // Includo il modulo
        include_once($modulePath);
    } catch (Exception $e) {
        if ($throwOnError) {
            throw $e;
        }

        echo $e->getMessage();
    }
}

/**
 * Abilita il modulo specificato impostando la costante GDRCD_ENABLED_MODULE.
 * Utilizzato per garantire la legittimità del caricamento dei file inclusi dinamicamente.
 *
 * @see gdrcd_chat_op_require_enable
 *
 * @param string|int $id Identificativo del modulo da abilitare
 * @return void
 */
function gdrcd_module_enable($id)
{
    if (!defined('GDRCD_ENABLED_MODULE')) {
        define('GDRCD_ENABLED_MODULE', $id);
    }
}

/**
 * Verifica che le operazioni siano consentite per il modulo specificato.
 * Termina lo script con HTTP 403 se il modulo non è abilitato.
 *
 * @param string|int $id Identificativo del modulo da verificare
 * @return void Terminazione dello script se non consentito
 */
function gdrcd_module_allowed($id)
{
    if (!defined('GDRCD_ENABLED_MODULE') || GDRCD_ENABLED_MODULE !== $id) {

        if (!headers_sent()) {
            http_response_code(403);
        }

        die($GLOBALS['MESSAGE']['error']['unknown_operation']);
    }
}

/**
 * Formatto il nome della pagina per consentire la ricerca
 * @param string $page il nome della pagina
 * @return string
 */
function gdrcd_pages_format($page)
{
    // Rimuove i puntini di ritorno
    $page = str_replace('..', '', $page);
    // Rimuove i backslash (\)
    $page = str_replace('\\', DIRECTORY_SEPARATOR, $page);
    // Converte la combinazione di caratteri __ nel separatore di directory
    $page = str_replace('__', DIRECTORY_SEPARATOR, $page);
    //
    return gdrcd_filter('include', $page);
}

/**
 * Eseguo un controllo sul contenuto della cartella /pages
 * e cerco una corrispondenza tra i moduli e i file presenti
 * @param string $page il nome della pagina da cercare
 * @return string
 * @throws Exception
 */
function gdrcd_pages_path($page)
{
    global $MESSAGE;

    // Controllo che sia stato attribuito un valore a page
    if (empty($page)) {
        throw new Exception($MESSAGE['interface']['page_missing']);
    }

    // Inizializzo le variabili del metodo
    $pagesPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'pages';
    $pageFormatted = gdrcd_pages_format($page);

    // Imposto i possibili percorsi che posso caricare
    $routes = [
        '.inc.php',
        DIRECTORY_SEPARATOR . 'index.inc.php'
    ];

    // Inizializzo la variabile contenitore dei moduli
    $modules = [];

    // Scorro i percorsi impostati per individuare corrispondenze
    foreach ($routes as $route) {
        $file = implode(DIRECTORY_SEPARATOR, [$pagesPath, $pageFormatted . $route]);
        // Se esiste la corrispondenza, allora inserisco
        if (file_exists($file)) {
            $modules[] = $file;
        }
    }

    // Controllo che sia stata trovata almeno una corrispondenza
    if (empty($modules)) {
        throw new Exception($MESSAGE['interface']['page_not_found']);
    }

    // Se sono state trovate piu corrispondenze, blocco il caricamento
    if (count($modules) > 1) {
        throw new Exception($MESSAGE['interface']['multiple_page_found']);
    }

    // Ritorno il modulo
    return $modules[0];
}

/**
 * Funzione di formattazione per la data nel formato italiano
 * @param string $date_in : la data in un formato leggibile da strtotime()
 * @return string : la data nel formato dd/mm/yyyy
 */
function gdrcd_format_date($date_in)
{
    return date('d/m/Y', strtotime($date_in));
}

/**
 * Funzione di formattazione del tempo nel formato italiano
 * @param string $time_in : la data-ora in un formato leggibile da strtotime()
 * @return string : l'ora nel formato hh:mm
 */
function gdrcd_format_time($time_in)
{
    return date('H:i', strtotime($time_in));
}

/**
 * Funzione di formattazione data completa nel formato italiano
 * @param $datetime_in : la data e ora in formato leggibile da strtotime()
 * @return string : la data/ora nel formato DD/MM/YYYY hh:mm
 */
function gdrcd_format_datetime($datetime_in)
{
    return date('d/m/Y H:i', strtotime($datetime_in));
}

/**
 * Funzione di formattazione data completa nel formato standard del database
 * @param string $datetime_in : la data e ora in formato leggibile da strtotime()
 * @return string la data/ora nel formato YYYY-MM-DD hh:mm
 */
function gdrcd_format_datetime_standard($datetime_in)
{
    return date('Y-m-d H:i', strtotime($datetime_in));
}

/**
 * Funzione di formattazione data completa nel formato ita per nome file da catalogare
 * @param string $datetime_in : la data e ora in formato leggibile da strtotime()
 * @return string : data ora formattata nel formato YYYYMMDD_hhmm
 */
function gdrcd_format_datetime_cat($datetime_in)
{
    return date('Ymd_Hi', strtotime($datetime_in));
}

/**
 * Trasforma la prima lettera della parola in maiuscolo
 * @param string $word : la parola da manipolare
 * @return string : $word con solo la prima lettera maiuscola
 */
function gdrcd_capital_letter($word)
{
    return ucwords(strtolower($word));
}

/**
 * Funzione di sicurezza per i nomi dei personaggi
 * @param string $word : la parola da manipolare
 * @return string : $word con solo la prima lettera maiuscola e filtrata
 */
function gdrcd_safe_name($word)
{
    return trim(gdrcd_capital_letter(gdrcd_filter_in($word)));
}

/**
 * Genera una password casuale, esclusivamente alfabetica con lettere maiuscole
 * @return string : una stringa casuale lunga 8 caratteri
 */
function gdrcd_genera_pass()
{
    $pass = '';
    for ($i = 0; $i < 8; ++$i) {
        $pass .= chr(mt_rand(0, 24) + ord("A"));
    }

    return $pass;
}

/**
 * BBcode nativo di GDRCD
 * Secondo me, questo bbcode presenta non poche vulnerabilità.
 * TODO Andrebbe aggiornata per essere più sicura
 * @param string $str : la stringa con i bbcode da tradurre, dovrebbe già essere stata filtrata per l'output su pagina web
 * @return array|string|string[]|null $str con i tag bbcode tradotti in html
 * @author Blancks
 */
function gdrcd_bbcoder($str)
{
    global $MESSAGE;
    $str = gdrcd_close_tags('quote', $str);

    $search = [
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
    ];
    $replace = [
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
        '<div class="bb-quote">' . $MESSAGE['interface']['forums']['link']['quote'] . ':<blockquote class="bb-quote-body">',
        '<div class="bb-quote"><div class="bb-quote-name">$1 ha scritto:</div><blockquote class="bb-quote-body">',
        '</blockquote></div>'
    ];

    return preg_replace($search, $replace, $str);
}

/**
 * Aggiunge la chiusura dei tag BBCode per impedire agli utenti di rompere l'HTML del sito
 * @param array|string $tag : il tag da controllare, senza le parentesi quadre, può essere un array di tag
 * @param $body : il testo in cui controllare
 * @return string : Il testo corretto
 * TODO aggiunge correttamente i tag non chiusi, ma non fa nulla se ci sono troppi tag di chiusura
 */
function gdrcd_close_tags($tag, $body)
{
    if (is_array($tag)) {
        foreach ($tag as $value) {
            $body = gdrcd_close_tags($value, $body);
        }
    } else {
        $opentags = preg_match_all('/\[' . $tag . '/i', $body);
        $closed = preg_match_all('/\[\/' . $tag . '\]/i', $body);
        $unclosed = $opentags - $closed;
        $body .= str_repeat('[/' . $tag . ']', $unclosed);
    }

    return $body;
}

/**
 * Fa il redirect della pagina, diretto ocon delay
 * @param $url : l'URL verso cui fare redirect
 * @param $tempo : il numero di secondi da attendere prima di fare redirect. Se non attendere impostare a 0 o false
 */
function gdrcd_redirect($url, $tempo = false)
{
    if (!headers_sent() && !$tempo) {
        header('Location:' . $url);
    } elseif (!headers_sent() && $tempo) {
        header('Refresh:' . $tempo . ';' . $url);
    } else {
        if (!$tempo) {
            $tempo = 0;
        }
        echo "<meta http-equiv=\"refresh\" content=\"" . $tempo . ";" . $url . "\">";
    }
}

/**
 * @deprecated use gdrcd_chat_replace_angs
 *
 * Sostituisce eventuali parentesi angolari in coppia in una stringa con parentesi quadre
 * @param string $str : la stringa da controllare
 * @return string $str con la coppie di parentesi angolari sostituite con parentesi quadre
 */
function gdrcd_angs($str)
{
    $search = [
        '#\&lt;(.+?)\&gt;#is',
        '#\<(.+?)>#is',
    ];
    $replace = [
        '[$1]',
        '[$1]',
    ];

    return preg_replace($search, $replace, $str);
}

/**
 * @deprecated use gdrcd_chat_add_colors
 *
 * Colora in HTML le parti di testo comprese tra parentesi angolari o parentesi quadre
 * Si usa in chat
 * @param string $str : la stringa da controllare
 * @return array|string|string[]|null $str con la parti colorate
 */
function gdrcd_chatcolor($str)
{
    $search = [
        '#\&lt;(.+?)\&gt;#is',
        '#\[(.+?)\]#is',
    ];
    $replace = [
        '<span class="color2">&lt;$1&gt;</span>',
        '<span class="color2">&lt;$1&gt;</span>',
    ];

    return preg_replace($search, $replace, $str);
}

/**
 * @deprecated use gdrcd_chat_highlight_user
 *
 * Sottolinea in HTML una stringa presente in un testo. Usata per sottolineare il proprio nome in chat
 * @param string $user : la stringa da sottolineare, in genere un nome utente
 * @param string $str : la stringa in cui cercare e sottolineare $user
 * @param bool $master : determino se ad inviare l'azione è un master o meno
 * @return array|string|string[]|null $str con tutte le occorrenze di $user sottolineate
 */
function gdrcd_chatme($user, $str, $master = false)
{
    $search = "|\\b" . preg_quote($user, "|") . "\\b|si";
    if (!$master) {
        $replace = '<span class="chat_me">' . gdrcd_filter('out', $user) . '</span>';
    } else {
        $replace = '<span class="chat_me_master">' . gdrcd_filter('out', $user) . '</span>';
    }

    return preg_replace($search, $replace, $str);
}

/**
 * Crea un campo di autocompletamento HTML5 (<datalist>) per vari contenuti
 * @param string $str : specifica il soggetto di cui creare la lista. Attualmente è supportato solo 'personaggi', che crea una lista di tutti gli utenti del gdr
 * @return string il tag html <datalist> già pronto per essere stampato sulla pagina
 * @throws Exception
 */
function gdrcd_list($str)
{
    // Inizializzo la variabile
    $list = '';

    if (strtolower($str) == 'personaggi') {
        $list = '<datalist id="personaggi">';
        $query = "SELECT nome FROM personaggio ORDER BY nome";
        $characters = gdrcd_query($query, 'result');

        while ($option = gdrcd_query($characters, 'fetch')) {
            $list .= '<option value="' . $option['nome'] . '" />'; //TODO escape HTMl del nome!
        }
        gdrcd_query($characters, 'free');
        $list .= '</datalist>';
    }

    return $list;
}

/**
 * Mostro in modo leggibile le informazioni di una variabile, tra cui il suo contenuto
 * @param string $object Variabile da consultare
 * @return  void    Mostra a schermo il contenuto della variabile, formattato
 */
function gdrcd_dump($object)
{
    echo '<xmp style="text-align: left;font-size:13px;">';
    print_r($object);
    echo '</xmp><br />';
}

/**
 * Raccolgo le informazioni di una variabile e le mostro in modo leggibile
 * @param mixed $args Variabile da consultare
 * @return  void    Mostra a schermo il contenuto della variabile, formattato
 * @usage   gdrcd_debug($var); gdrcd_debug($var1, $var2, ...);
 */
function gdrcd_debug($args)
{
    $args = func_get_args();
    foreach ($args as $arg) {
        gdrcd_dump($arg);
    }
}

/**
 * Raccolgo le informazioni di una variabile e le mostro in modo leggibile, poi interrompo il caricamento della pagina
 * @param mixed $args Variabile da consultare
 * @return  void    Mostra a schermo il contenuto della variabile, formattato
 * @usage   gdrcd_brute_debug($var); gdrcd_brute_debug($var1, $var2, ...);
 */
function gdrcd_brute_debug($args)
{
    $args = func_get_args();
    foreach ($args as $arg) {
        gdrcd_dump($arg);
    }
    die('FINE');
}

/**
 * Restituisce una versione leggibile di un parametro o valore di configurazione.
 *
 * Converte il dato passato sostituendo gli underscore con spazi,
 * capitalizzando ogni parola e filtrando l'output per la visualizzazione.
 *
 * @param string $parametro il parametro o il valore di configurazione da formattare
 * @return string
 */
function gdrcd_configuration_label($parametro)
{
    return gdrcd_filter('out', ucwords(strtr($parametro, ['_' => ' '])));
}

/**
 * Recupera un valore di configurazione dal database
 *
 * @param string $parametro Il parametro nel formato "categoria.parametro"
 * @return string|null Il valore della configurazione o null se non trovato
 */
function gdrcd_configuration_get($parametro)
{
    // Recupera il valore tramite query
    // Se il valore nel db non esiste, ritorna esplicitamente null
    [$categoria, $parametro] = explode('.', $parametro, 2);
    $result =  gdrcd_stmt(
        "SELECT valore, `default` FROM configurazioni WHERE categoria = ? AND parametro = ?",
        [
            $categoria,
            $parametro
        ]
    );

    if (gdrcd_query($result, 'num_rows') === 0) {
        return null;
    }

    $value = gdrcd_query($result, 'assoc');
    return !is_null($value['valore']) && $value['valore'] !== ''
        ? $value['valore']
        : $value['default'];
}

/**
 * Imposta un valore di configurazione nel database
 *
 * @param string $parametro Il parametro nel formato "categoria.parametro"
 * @param string $value Il valore da salvare
 * @return void
 */
function gdrcd_configuration_set($parametro, $value)
{
    [$categoria, $parametro] = explode('.', $parametro, 2);
    // Query di salvataggio del $valore nel db per $parametro
    gdrcd_stmt(
        "UPDATE configurazioni
                    SET valore = ?
                WHERE categoria = ?
                    AND parametro = ?",
        [
            $value,
            $categoria,
            $parametro
        ]
    );
}

/**
 * Maschera un indirizzo IP (IPv4 o IPv6).
 * - IPv4: 192.168.X.X
 * - IPv6: mostra solo i primi 4 blocchi, il resto mascherato
 *   es. 2001:0db8:85a3:0000:X:X:X:X
 */
function gdrcd_mask_ip($ip)
{
    // IPv4
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $ip);

        if (count($parts) === 4) {
            $parts[2] = 'X';
            $parts[3] = 'X';
            return implode('.', $parts);
        }
    }

    // IPv6
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $packed = inet_pton($ip);

        if ($packed !== false) {
            $hex = unpack('H*', $packed)[1];
            $blocks = str_split($hex, 4);

            // Mantieni i primi 4 blocchi, maschera gli altri
            for ($i = 4; $i < 8; $i++) {
                $blocks[$i] = 'X';
            }

            return implode(':', $blocks);
        }
    }

    return $ip;
}