<?php
require_once __DIR__ . '/stmt_result.php';

/**
 * Funzioni di CORE di GDRCD
 */

/**
 * Funzionalità di dialogo col database
 * Set di funzioni da core che implementano il dialogo gestito col db
 */

/**
 * Connettore al database MySql
 */
function gdrcd_connect()
{
    static $db_link = false;

    if ($db_link === false) {
        $db_user = $GLOBALS['PARAMETERS']['database']['username'];
        $db_pass = $GLOBALS['PARAMETERS']['database']['password'];
        $db_name = $GLOBALS['PARAMETERS']['database']['database_name'];
        $db_host = $GLOBALS['PARAMETERS']['database']['url'];
        $db_error = $GLOBALS['MESSAGE']['error']['db_not_found'];

        $db_link = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

        mysqli_set_charset($db_link, "utf8mb4");

        if (mysqli_connect_errno()) {
            gdrcd_mysql_error($db_error);
        }
    }
    return $db_link;
}

/**
 * Chiusura della connessione col db MySql
 * @param mysqli $db : una connessione mysqli
 */
function gdrcd_close_connection($db)
{
    // Chiudo la connessione al database
    if (is_resource($db) && get_resource_type($db) === 'mysql link') mysqli_close($db);
}

/**
 * Esegue una query SQL o gestisce un risultato esistente.
 *
 * Questa funzione serve come un'interfaccia di astrazione per le operazioni sul database.
 * Mantiene la retrocompatibilità con i risultati di mysqli_query e aggiunge il supporto
 * per la gestione dei risultati restituiti da gdrcd_stmt.
 *
 * @param mixed $sql La query SQL (stringa) o il risultato (mysqli_result o array) da gestire.
 * @param string $mode La modalità di operazione ('query', 'result', 'num_rows', 'fetch', 'assoc', 'object', 'free', 'last_id', 'affected').
 * @param bool $throwOnError Se true, solleva un'eccezione in caso di errore. Altrimenti, termina lo script.
 * @return mixed Il risultato dell'operazione.
 */
function gdrcd_query($sql, $mode = 'query', $throwOnError = false)
{
    $db_link = gdrcd_connect();

    // Rileva se l'input è già un risultato (object o array) o una query string
    $isResultObject = is_object($sql) && ($sql instanceof mysqli_result);
    $isResultArray = is_object($sql) && ($sql instanceof StmtResult);

    //veccio metodo non modificato, mantiene la retrocompatibilità e gestisce le query normali
    switch (strtolower(trim($mode))) {
        case 'query':
            if ($isResultArray) {
                // Se è un array risultato da gdrcd_stmt, restituisci il primo elemento
                return isset($sql->data[0]) ? $sql->data[0] : null;
            }
            switch (strtoupper(substr(trim($sql), 0, 6))) {
                case 'SELECT':
                    $result = mysqli_query($db_link, $sql);
                    if ($result === false) {
                        if ($throwOnError) {
                            throw new Exception("Query DB Fallita: " . $sql . "\n\n" . mysqli_error($db_link));
                        } else {
                            die(gdrcd_mysql_error($sql));
                        }
                    }
                    $row = mysqli_fetch_array($result, MYSQLI_BOTH);
                    mysqli_free_result($result);

                    return $row;
                default:
                    $result = mysqli_query($db_link, $sql);
                    if ($result === false) {
                        if ($throwOnError) {
                            throw new Exception("Query DB Fallita: " . $sql . "\n\n" . mysqli_error($db_link));
                        } else {
                            die(gdrcd_mysql_error($sql));
                        }
                    }
                    return $result;
            }
            break;


        case 'result':
            if ($isResultArray) {
                // Restituisce l'intero array di dati per i prepared statements
                return $sql->data;
            }
            $result = mysqli_query($db_link, $sql);
            if ($result === false) {
                if ($throwOnError) {
                    throw new Exception("Query DB Fallita: " . $sql . "\n\n" . mysqli_error($db_link));
                } else {
                    die(gdrcd_mysql_error($sql));
                }
            }
            return $result;
            break;

        case 'num_rows':
            if ($isResultArray) {
                return $sql->num_rows;
            }
            return (int)mysqli_num_rows($sql);
            break;

        //aggiunto il supporto per i risultati di gdrcd_stmt per queste casistiche è necessario mantenere 
        //lo stato del "puntatore" all'interno dell'array
        case 'fetch':
            if ($isResultArray) {
                return array_shift($sql->data);
            }
            if ($isResultObject) {
                return mysqli_fetch_array($sql);
            }
            break;
        case 'assoc':
            if ($isResultArray) {
                $current = $sql->data->current();
                $sql->data->next();
                return $current;
            }
            if ($isResultObject) {
                return mysqli_fetch_array($sql, MYSQLI_ASSOC);
            }
            break;
        case 'object':
            if ($isResultArray) {
                $row = array_shift($sql->data);
                return is_array($row) ? (object)$row : null;
            }
            // Logica per i risultati mysqli_result standard, stessa logica di prima
            if ($isResultObject) {
                return mysqli_fetch_object($sql);
            }
            break;

        case 'free':
            if ($isResultArray) {
                // Per i risultati di gdrcd_stmt, non c'è nulla da liberare
                return true;
            }
            mysqli_free_result($sql);
            return true;
            break;

        case 'last_id':
            if ($isResultArray) {
                return $sql->last_id;
            }
            return mysqli_insert_id($db_link);
            break;

        case 'affected':
            if ($isResultArray) {
                return $sql->affected_rows;
            }
            return (int)mysqli_affected_rows($db_link);
            break;

        default:
            throw new Exception("Impossibile determinare l'operazione da eseguire sul database.");
    }
}


/**
 * Esegue una query SQL utilizzando prepared statements tramite MySQLi.
 *
 * Questa funzione permette di eseguire in modo sicuro query SQL, prevenendo SQL injection,
 * tramite l'utilizzo di prepared statements. I parametri della query vengono passati tramite
 * un array, dove il primo elemento specifica i tipi dei parametri secondo la sintassi MySQLi:
 *  - 'i' per integer
 *  - 'd' per double/float
 *  - 's' per string
 *  - 'b' per blob
 *
 * @param string $sql   La query SQL da eseguire, con i segnaposto (?) per i parametri.
 * @param array  $binds Array dei parametri da associare alla query. L'indice 0 deve contenere
 *                      una stringa con i tipi dei parametri, gli indici successivi i valori.
 *                      Esempio: ['si', 'nome', 42]
 *
 * @return array|false Restituisce il risultato della query (mysqli_result) in caso di SELECT,
 *                             true per query di modifica (INSERT/UPDATE/DELETE), oppure false in caso di errore.
 */
function gdrcd_stmt($sql, $binds = array(), $throwOnError = false)
{
    $db_link = gdrcd_connect();
    //Oggetto che sarà restituito dalla funzione
    $resultArr = array(
        'data' => null,
        'num_rows' => null,
        'affected_rows' => null,
        'last_id' => null,
    );

    $stmt = mysqli_prepare($db_link, $sql);

    if ($stmt === false) {
        $errorMsg = gdrcd_mysql_error('Failed when creating the statement.');
        if ($throwOnError) {
            throw new Exception($errorMsg);
        } else {
            die($errorMsg);
        }
    }

    if (!empty($binds)) {
        // MySQLi requires references for bind_param
        $refs = array();
        foreach ($binds as $k => $v) {
            $refs[$k] = &$binds[$k];
        }
        array_unshift($refs, $stmt);
        call_user_func_array('mysqli_stmt_bind_param', $refs);
    }

    if (!mysqli_stmt_execute($stmt)) {
        $stmtError = mysqli_stmt_error($stmt);
        $errorMsg = gdrcd_mysql_error($stmtError);
        mysqli_stmt_close($stmt);
        if ($throwOnError) {
            throw new Exception($errorMsg);
        } else {
            die($errorMsg);
        }
    }

    $meta = mysqli_stmt_result_metadata($stmt);
    if ($meta) {
        // SELECT-like query
        $result = mysqli_stmt_get_result($stmt);
        if ($result === false) {
            $stmtError = mysqli_stmt_error($stmt);
            $errorMsg = gdrcd_mysql_error($stmtError);
            mysqli_stmt_close($stmt);
            if ($throwOnError) {
                throw new Exception($errorMsg);
            } else {
                die($errorMsg);
            }
        }
        $rows = array();
        while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
            $rows[] = $row;
        }
        $resultArr['data'] = $rows;
        $resultArr['num_rows'] = mysqli_num_rows($result);
        mysqli_free_result($result);
    } else {
        // Non-SELECT query
        $resultArr['affected_rows'] = mysqli_stmt_affected_rows($stmt);
        // Check if it's an INSERT
        if (preg_match('/^\s*INSERT\s/i', $sql)) {
            $resultArr['last_id'] = mysqli_stmt_insert_id($stmt);
        }
    }

    mysqli_stmt_close($stmt);

    return new StmtResult($resultArr['data'], $resultArr['num_rows'], $resultArr['affected_rows'], $resultArr['last_id']);
}


/**
 * Funzione di recupero delle colonne e della loro dichiarazione della tabella specificata.
 * Si usa per la verifica dell'aggiornamento db da vecchie versioni di gdrcd5
 * @param string $table : il nome della tabella da controllare
 * @return array : un oggetto contenente la descrizione della tabella negli attributi
 * @throws Exception
 */
function gdrcd_check_tables($table)
{
    $result = gdrcd_query("SELECT * FROM $table LIMIT 1", 'result');
    $describe = gdrcd_query("SHOW COLUMNS FROM $table", 'result');

    $i = 0;
    $output = array();

    while ($field = gdrcd_query($describe, 'object')) {
        $defInfo = mysqli_fetch_field_direct($result, $i);

        $field->auto_increment = (strpos($field->Extra, 'auto_increment') === false ? 0 : 1);
        $field->definition = $field->Type;

        if ($field->Null == 'NO' && $field->Key != 'PRI') {
            $field->definition .= ' NOT NULL';
        }

        if ($field->Default) {
            $field->definition .= " DEFAULT '" . mysqli_real_escape_string(gdrcd_connect(), $field->Default) . "'";
        }

        if ($field->auto_increment) {
            $field->definition .= ' AUTO_INCREMENT';
        }

        switch ($field->Key) {
            case 'PRI':
                $field->definition .= ' PRIMARY KEY';
                break;
            case 'UNI':
                $field->definition .= ' UNIQUE KEY';
                break;
            case 'MUL':
                $field->definition .= ' KEY';
                break;
        }

        $field->len = $defInfo->length;
        $output[$field->Field] = $field;
        ++$i;

        unset($defInfo);
    }
    gdrcd_query($describe, 'free');

    return $output;
}

/**
 * Gestione degli errori tornati dalle query
 * @param string $details : una descrizione dell'errore avvenuto
 * @return string: una stringa HTML che descrive l'errore riscontrato
 */
function gdrcd_mysql_error($details = false)
{
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 50);
    $history = '';

    foreach ($backtrace as $v) {
        if ($v['function'] == 'gdrcd_query') {
            $base = $v;
        }
        $history .= '<strong>FILE: </strong>: ' . $v['file'] . ' - ';
        $history .= '<strong>LINE: </strong>: ' . $v['line'] . '</br />';
    }
    $error_msg  = '<div class="error mysql">';
    $error_msg .= '<strong>GDRCD MySQLi Error</strong>:</br>';
    if ($details !== false) {
        $error_msg .= '<strong>QUERY: </strong>: ' . $details . '</br>';
    }
    $error_msg .= '<strong>ERROR [' . mysqli_errno(gdrcd_connect()) . ']</strong>: ' . mysqli_error(gdrcd_connect()) . '<br />';
    $error_msg .= '<strong>FILE: </strong>: ' . $base['file'] . ' - ';
    $error_msg .= '<strong>LINE: </strong>: ' . $base['line'] . '<br />';
    $error_msg .= '<details>';
    $error_msg .= '<summary>Dettagli</summary>';
    $error_msg .= $history;
    $error_msg .= '</details>';
    $error_msg .= '</div>';
    return $error_msg;
}

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
    $notAllowed = array(
        "#<script(.*?)>(.*?)</script>#is" => "Script non consentiti",
        "#(<iframe.*?\/?>.*?(<\/iframe>)?)#is" => "Frame non consentiti",
        "#(<object.*?>.*?(<\/object>)?)#is" => "Contenuti multimediali non consentiti",
        "#(<embed.*?\/?>.*?(<\/embed>)?)#is" => "Contenuti multimediali non consentiti",
        "#\bon([a-z]*?)=(['|\"])(.*?)\\2#mi" => " ",
        "#(javascript:[^\s\"']+)#is" => ""
    );

    if ($GLOBALS['PARAMETERS']['settings']['html'] == HTML_FILTER_HIGH) {
        $notAllowed = array_merge($notAllowed, array(
            "#(<img.*?\/?>)#is" => "Immagini non consentite",
            "#(url\(.*?\))#is" => "none",
        ));
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
 * @param string $pg : il nome del pg da ricercare
 * @param bool $return default false. Se posto su true la funzione ritorna il messaggio d'esilio come stringa
 * @return bool|string false se il pg non è stato esiliato. True se return è stato impostato a false, altrimenti una stringa con la motivazione dell'esilio
 */
function gdrcd_controllo_esilio($pg, $return = false)
{
    $exiled = gdrcd_query("SELECT autore_esilio, esilio, motivo_esilio FROM personaggio WHERE nome='" . gdrcd_filter('in', $pg) . "' LIMIT 1");

    if (strtotime($exiled['esilio']) > time()) {

        $message = gdrcd_filter_out($pg)
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

/**
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
function gdrcd_load_modules($page, $params = array(), $throwOnError = false)
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
 * Formatto il nome della pagina per consentire la ricerca
 * @param string $page il nome della pagina
 * @return string
 */
function gdrcd_pages_format($page)
{
    $page = str_replace('\\', DIRECTORY_SEPARATOR, $page);
    //converte la combinaizone di caratteri __ nel separatore di directory
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
    $routes = array(
        '.inc.php',
        DIRECTORY_SEPARATOR . 'index.inc.php'
    );

    // Inizializzo la variabile contenitore dei moduli
    $modules = array();

    // Scorro i percorsi impostati per individuare corrispondenze
    foreach ($routes as $route) {
        $file = implode(DIRECTORY_SEPARATOR, array($pagesPath, $pageFormatted . $route));
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
        '<div class="bb-quote">' . $MESSAGE['interface']['forums']['link']['quote'] . ':<blockquote class="bb-quote-body">',
        '<div class="bb-quote"><div class="bb-quote-name">$1 ha scritto:</div><blockquote class="bb-quote-body">',
        '</blockquote></div>'
    );

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
