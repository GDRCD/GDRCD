<?php
/**
 * Funzioni di core di gdrcd
 * Il file contiene una revisione del core originario introdotto in GDRCD5
 * @version 5.4
 * @author Breaker
 */

/**
 * Funzionalità di escape
 * Set di funzioni escape per filtrare i possibili contenuti introdotti da un utente ;-)
 */

/**
 * Funzione di hashing delle password.
 * @param string $str : la password o stringa di cui calcolare l'hash
 * @return l'hash calcolato a partire da $str con l'algoritmo specificato nella configurazione
 */
function gdrcd_encript($str)
{
    require_once(dirname(__FILE__) . '/../includes/PasswordHash.php');
    $hasher = new PasswordHash(8, true);

    return $hasher->HashPassword($str);
}

function gdrcd_password_check($pass, $stored)
{
    require_once(dirname(__FILE__) . '/../includes/PasswordHash.php');
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
 * @param string $pg : il nome del pg da ricercare
 * @return true se il pg è esiliato, false altrimenti
 */
function gdrcd_controllo_esilio($pg)
{
    $exiled = gdrcd_query("SELECT autore_esilio, esilio, motivo_esilio FROM personaggio WHERE nome='" . gdrcd_filter('in', $pg) . "' LIMIT 1");

    if (strtotime($exiled['esilio']) > time()) {
        echo '<div class="error">', Filters::out($pg), ' ', Filters::out($GLOBALS['MESSAGE']['warning']['character_exiled']), ' ', gdrcd_format_date($exiled['esilio']), ' (', $exiled['motivo_esilio'], ' - ', $exiled['autore_esilio'], ')</div>';

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
    $time_hours = date('H', strtotime($time));
    $time_minutes = date('i', strtotime($time));

    if ($time_hours == date('H')) {
        return date('i') - $time_minutes;
    } elseif ($time_hours == (date('H') - 1) || $time_hours == (strftime('H') + 11)) {
        return date('i') - $time_minutes + 60;
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
 * @param string $path : il percorso filesystem del file da includere
 * @param array $params : un array di dati aggiuntivi passabili al modulo
 */
function gdrcd_load_modules($page, $params = [])
{
    global $MESSAGE;
    global $PARAMETERS;

    // Costruisco i parametri del modulo
    $MODULE = $params;

    try {
        // Controllo la tipologia di informazione passata (file o page) e poi determino il percorso del modulo
        $modulePath = is_file($page) ? $page : gdrcd_pages_path($page);

        if(!file_exists($modulePath)) {
            throw new Exception($MESSAGE['interface']['layout_not_found']);
        }

        // Includo il modulo
        include($modulePath);
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }
}

/**
 * Formatto il nome della pagina per consentire la ricerca
 * @param string $page il nome della pagina
 * @return string
 */
function gdrcd_pages_format($page)
{
    $page = str_replace('\\',DIRECTORY_SEPARATOR, $page);
    //converte la combinaizone di caratteri __ nel separatore di directory
    $page = str_replace('__',DIRECTORY_SEPARATOR, $page);
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
    if(empty($page)) {
        throw new Exception($MESSAGE['interface']['page_missing']);
    }

    // Inizializzo le variabili del metodo
    $pagesPath = dirname(__FILE__) . DIRECTORY_SEPARATOR. '..'.DIRECTORY_SEPARATOR.'pages';
    $pageFormatted = gdrcd_pages_format($page);

    // Imposto i possibili percorsi che posso caricare
    $routes = [
        '.inc.php',
        DIRECTORY_SEPARATOR.'index.inc.php'
    ];

    // Inizializzo la variabile contenitore dei moduli
    $modules = [];

    // Scorro i percorsi impostati per individuare corrispondenze
    foreach ($routes AS $route) {
        $file = implode(DIRECTORY_SEPARATOR, [$pagesPath, $pageFormatted.$route]);
        // Se esiste la corrispondenza, allora inserisco
        if(file_exists($file)) {
            $modules[] = $file;
        }
    }

    // Controllo che sia stata trovata almeno una corrispondenza
    if(empty($modules)) {
        throw new Exception($MESSAGE['interface']['page_not_found']);
    }

    // Se sono state trovate piu corrispondenze, blocco il caricamento
    if(count($modules) > 1) {
        throw new Exception($MESSAGE['interface']['multiple_page_found']);
    }

    // Ritorno il modulo
    return $modules[0];
}

/**
 * Funzione di formattazione per la data nel formato italiano
 * @param string $date_in : la data in un formato leggibile da strtotime()
 * @return la data nel formato dd/mm/yyyy
 */
function gdrcd_format_date($date_in)
{
    return date('d/m/Y', strtotime($date_in));
}

/**
 * Funzione di formattazione del tempo nel formato italiano
 * @param string $time_in : la data-ora in un formato leggibile da strtotime()
 * @return l'ora nel formato hh:mm
 */
function gdrcd_format_time($time_in)
{
    return date('H:i', strtotime($time_in));
}

/**
 * Funzione di formattazione data completa nel formato italiano
 * @param $datetime_in : la data e ora in formato leggibile da strtotime()
 * @return la data/ora nel formato DD/MM/YYYY hh:mm
 */
function gdrcd_format_datetime($datetime_in)
{
    return date('d/m/Y H:i', strtotime($datetime_in));
}

/**
 * Funzione di formattazione data completa nel formato standard del database
 * @param $datetime_in : la data e ora in formato leggibile da strtotime()
 * @return la data/ora nel formato YYYY-MM-DD hh:mm
 */
function gdrcd_format_datetime_standard($datetime_in)
{
    return date('Y-m-d H:i', strtotime($datetime_in));
}

/**
 * Funzione di formattazione data completa nel formato ita per nome file da catalogare
 * @param string $datetime_in : la data e ora in formato leggibile da strtotime()
 * @return data ora formattata nel formato YYYYMMDD_hhmm
 */
function gdrcd_format_datetime_cat($datetime_in)
{
    return date('Ymd_Hi', strtotime($datetime_in));
}

/**
 * Trasforma la prima lettera della parola in maiuscolo
 * @param string $word : la parola da manipolare
 * @return $word con solo la prima lettera maiuscola
 */
function gdrcd_capital_letter($word)
{
    return ucwords(strtolower($word));
}

function gdrcd_safe_name($word)
{
    return trim(gdrcd_capital_letter(Filters::in($word)));
}

/**
 * Genera una password casuale, esclusivamente alfabetica con lettere maiuscole
 * @return una stringa casuale lunga 8 caratteri
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
 * @return $str con i tag bbcode tradotti in html
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
 * @return Il testo corretto
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
        for ($i = 0; $i < $unclosed; $i++) {
            $body .= '[/' . $tag . ']';
        }
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
    if (!headers_sent() && $tempo == false) {
        header('Location:' . $url);
    } elseif (!headers_sent() && $tempo != false) {
        header('Refresh:' . $tempo . ';' . $url);
    } else {
        if ($tempo == false) {
            $tempo = 0;
        }
        echo "<meta http-equiv=\"refresh\" content=\"" . $tempo . ";" . $url . "\">";
    }
}

/**
 * Sostituisce eventuali parentesi angolari in coppia in una stringa con parentesi quadre
 * @param string $str : la stringa da controllare
 * @return $str con la coppie di parentesi angolari sostituite con parentesi quadre
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
 * Colora in HTML le parti di testo comprese tra parentesi angolari o parentesi quadre
 * Si usa in chat
 * @param string $str : la stringa da controllare
 * @return $str con la parti colorate
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
 * Sottolinea in HTML una stringa presente in un testo. Usata per sottolineare il proprio nome in chat
 * @param string $user : la stringa da sottolineare, in genere un nome utente
 * @param string $str : la stringa in cui cercare e sottolineare $user
 * @return $str con tutte le occorrenze di $user sottolineate
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
 * @return il tag html <datalist> già pronto per essere stampato sulla pagina
 */
function gdrcd_list($str)
{
    switch (strtolower($str)) {
        case 'personaggi':
            $list = '<datalist id="personaggi">';
            $query = "SELECT nome FROM personaggio ORDER BY nome";
            $characters = gdrcd_query($query, 'result');

            while ($option = gdrcd_query($characters, 'fetch')) {
                $list .= '<option value="' . $option['nome'] . '" />';//TODO escape HTMl del nome!
            }
            gdrcd_query($characters, 'free');
            $list .= '</datalist>';
            break;
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

/** NUOVI HELPER */

/**
 * @fn gdrcd_filter
 * @note Funzione di filtraggio dati
 * @param string $type - Modalita' controllo utilizzata
 * @param string $val - Stringa da controllare
 */
function gdrcd_filter($type, $val)
{
    return Filters::gdrcd_filter($type, $val);
}

/**
 * @fn gdrcd_query
 * @note Funzione di appoggio per db
 * @param $sql
 * @param string $mode
 * @return bool|int|mixed|string
 */
function gdrcd_query($sql, string $mode = 'query')
{
    return DB::query($sql,$mode);
}