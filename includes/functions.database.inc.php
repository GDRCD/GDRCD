<?php

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
            gdrcd_database_error_format($db_error);
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
    if (is_resource($db) && get_resource_type($db) === 'mysql link') {
        mysqli_close($db);
    }
}

/**
 * Esegue una query SQL o gestisce un risultato esistente.
 *
 * Questa funzione serve come un'interfaccia di astrazione per le operazioni sul database.
 * Mantiene la retrocompatibilità con i risultati di mysqli_query e aggiunge il supporto
 * per la gestione dei risultati restituiti da gdrcd_stmt.
 *
 * @param mysqli_result|array|string $sql La query SQL (stringa) o il risultato (mysqli_result o gdrcd_stmt) da gestire.
 * @param string $mode La modalità di operazione.
 * Modalità accettate:
 *  - query: esegue la query e ritorna come risultato la prima riga del resultset
 *  - result: esegue la query e ritorna la risorsa mysqli_result associata al risultato
 *  - num_rows: accetta come parametro una risorsa mysqli_result o il risultato di gdrcd_stmt e ritorna il numero di righe
 *  - fetch: accetta come parametro una risorsa mysqli_result o il risultato di gdrcd_stmt e ritorna il successivo risultato dal resultset come array
 *  - object: uguale a fetch, eccetto che ritorna un oggetto al posto di un array
 *  - free: libera la memoria occupata dalla risorsa mysqli_result o del risultato di gdrcd_stmt passato in $sql
 *  - last_id: ritorna l'id del record generato dall'ultima query di INSERT
 *  - affected: ritorna il numero di record toccati dall'ultima query (INSERT, UPDATE, DELETE o SELECT)
 * @return mixed un booleano in caso di esecuzione di query non SELECT e modalità 'query'. Altrimenti ritorna come specificato nella descrizione di $mode
 * @param bool $throwOnError Se true, solleva un'eccezione in caso di errore. Altrimenti, termina lo script.
 * @return mixed Il risultato dell'operazione.
 */
function gdrcd_query($sql, $mode = 'query', $throwOnError = false)
{
    $db_link = gdrcd_connect();
    $getMysqliError = fn() => '[' . mysqli_errno($db_link) . '] ' . mysqli_error($db_link);

    // Rileva se l'input è già un risultato (object o array) o una query string
    $isResultObject = is_object($sql) && ($sql instanceof mysqli_result);
    $isStmtResult = (is_array($sql) && $sql['data'] instanceof StmtResultData) || (is_array($sql) && isset($sql['affected']));

    // vecchio metodo non modificato, mantiene la retrocompatibilità e gestisce le query normali
    switch (strtolower(trim($mode))) {
        case 'query':
            if ($isStmtResult) {
                // Se è un array risultato da gdrcd_stmt, restituisci il primo elemento
                $row = isset($sql['data'][0]) ? $sql['data'][0] : null;
                $sql['data']->free();
                return $row;
            }

            switch (strtoupper(substr(trim($sql), 0, 6))) {
                case 'SELECT':
                    $result = mysqli_query($db_link, $sql);

                    if ($result === false) {
                        gdrcd_database_error_handle($getMysqliError(), $sql, $throwOnError);
                    }

                    $row = mysqli_fetch_array($result, MYSQLI_BOTH);
                    mysqli_free_result($result);

                    return $row;
                default:
                    $result = mysqli_query($db_link, $sql);

                    if ($result === false) {
                        gdrcd_database_error_handle($getMysqliError(), $sql, $throwOnError);
                    }

                    return $result;
            }
            break;


        case 'result':
            if ($isStmtResult) {
                // Restituisce l'intero array di dati per i prepared statements
                return $sql['data'];
            }
            $result = mysqli_query($db_link, $sql);

            if ($result === false) {
                gdrcd_database_error_handle($getMysqliError(), $sql, $throwOnError);
            }

            return $result;
            break;

        case 'num_rows':
            if ($isStmtResult) {
                return $sql['num_rows'];
            }
            return (int)mysqli_num_rows($sql);
            break;

            //aggiunto il supporto per i risultati di gdrcd_stmt per queste casistiche è necessario mantenere
            //lo stato del "puntatore" all'interno dell'array
        case 'fetch':
            if ($isStmtResult) {
                $current = $sql['data']->current();
                $sql['data']->next();
                return $current;
            }
            if ($isResultObject) {
                return mysqli_fetch_array($sql);
            }
            break;
        case 'assoc':
            if ($isStmtResult) {
                return $sql['data']->fetchAssoc();
            }
            if ($isResultObject) {
                return mysqli_fetch_array($sql, MYSQLI_ASSOC);
            }
            break;
        case 'object':
            if ($isStmtResult) {
                $row = $sql['data']->current();
                $sql['data']->next();
                return is_array($row) ? (object)$row : null;
            }
            // Logica per i risultati mysqli_result standard, stessa logica di prima
            if ($isResultObject) {
                return mysqli_fetch_object($sql);
            }
            break;

        case 'free':
            if ($isStmtResult) {
                // Per i risultati di gdrcd_stmt, non c'è nulla da liberare
                $sql['data']->free();
                return true;
            }
            mysqli_free_result($sql);
            return true;
            break;

        case 'last_id':
            if ($isStmtResult) {
                return $sql['last_id'];
            }
            return mysqli_insert_id($db_link);
            break;

        case 'affected':
            if ($isStmtResult) {
                return $sql['affected'];
            }
            return (int)mysqli_affected_rows($db_link);
            break;

        default:
            gdrcd_database_error_handle('Impossibile determinare l\'operazione da eseguire sul database.', $sql, $throwOnError);
    }
}

/**
 * Esegue una query SQL utilizzando prepared statements tramite MySQLi.
 *
 * Questa funzione permette di eseguire in modo sicuro query SQL, prevenendo SQL injection,
 * tramite l'utilizzo di prepared statements. I parametri della query vengono passati tramite
 * un array.
 *
 * @param string $sql   La query SQL da eseguire, con i segnaposto (?) per i parametri.
 * @param array  $binds Array dei parametri da associare alla query.
 * @param array{throw: bool} array di parametri opzionali
 *  - throw: se valorizzato a true la query lancia un eccezione invece di interrompere l'esecuzione dello script
 *
 * @return StmtResult|false Restituisce il risultato della query (mysqli_result) in caso di SELECT,
 *                             true per query di modifica (INSERT/UPDATE/DELETE), oppure false in caso di errore.
 */
function gdrcd_stmt($sql, $binds = array(), $options = [])
{
    $stmt = gdrcd_stmt_prepare($sql, $options);
    $result = gdrcd_stmt_execute($stmt, $binds);
    gdrcd_stmt_close($stmt);

    return $result;
}

/**
 * Prepara una query SQL per l'esecuzione con prepared statement.
 *
 * Questa funzione è un helper interno che crea una struttura dati contenente
 * lo statement MySQLi preparato e le opzioni di configurazione.
 *
 * @param string $sql La query SQL da preparare, con i segnaposto (?) per i parametri.
 * @param array $options Array di opzioni opzionali.
 *  - throw: se valorizzato a true la query lancia un'eccezione invece di interrompere l'esecuzione dello script
 *
 * @return array Array associativo contenente:
 *  - 'sql': la query SQL originale
 *  - 'stmt': l'oggetto mysqli_stmt preparato
 *  - 'options': le opzioni passate alla funzione
 */
function gdrcd_stmt_prepare($sql, $options = [])
{
    $db_link = gdrcd_connect();
    $mysqliStmt = mysqli_prepare($db_link, $sql);

    if ($mysqliStmt === false) {
        gdrcd_database_error_handle('Failed when creating the statement.', $sql, [], !empty($options['throw']));
    }

    return [
        'sql' => $sql,
        'stmt' => $mysqliStmt,
        'options' => $options,
    ];
}

/**
 * Chiude uno statement preparato e libera le risorse associate.
 *
 * @param array $stmt Array associativo contenente lo statement da chiudere,
 *                    come restituito da gdrcd_stmt_prepare.
 *
 * @return void
 */
function gdrcd_stmt_close($stmt)
{
    $sql = $stmt['sql'] ?? null;
    $mysqliStmt = $stmt['stmt'] ?? null;
    $options = $stmt['options'] ?? [];

    if ($mysqliStmt === false) {
        gdrcd_database_error_handle('Invalid statement.', $sql, [], !empty($options['throw']));
    }

    mysqli_stmt_close($mysqliStmt);
}

/**
 * Esegue uno statement preparato con i parametri specificati.
 *
 * Questa funzione esegue uno statement MySQLi già preparato, eseguendo il binding
 * dei parametri in modo automatico. Gestisce sia query SELECT che query di modifica
 * (INSERT, UPDATE, DELETE), restituendo i risultati in un formato unificato.
 *
 * @param array $stmt Array associativo contenente lo statement preparato,
 *                    come restituito da gdrcd_stmt_prepare.
 * @param array $binds Array dei parametri da associare alla query.
 *                     I tipi vengono determinati automaticamente tramite gdrcd_stmt_bind_type.
 *                     Esempio: ['nome', 42, 3.14]
 *
 * @return array Array associativo contenente:
 *  - 'data': oggetto StmtResultData con i dati della query (per SELECT) o null
 *  - 'num_rows': numero di righe restituite (per SELECT) o null
 *  - 'affected': numero di righe modificate (per INSERT/UPDATE/DELETE) o null
 *  - 'last_id': ID dell'ultimo record inserito (per INSERT) o null
 *
 * @throws Exception Se la preparazione o l'esecuzione dello statement fallisce e l'opzione 'throw' è attiva.
 */
function gdrcd_stmt_execute($stmt, $binds = [])
{
    $sql = $stmt['sql'] ?? null;
    $mysqliStmt = $stmt['stmt'] ?? null;
    $options = $stmt['options'] ?? [];
    $throwOnError = !empty($options['throw']);

    if (!($mysqliStmt instanceof mysqli_stmt)) {
        gdrcd_database_error_handle('Invalid statement.', $sql, $binds, $throwOnError);
    }

    if (!empty($binds)) {
        // Computa i tipi
        $stringTypes = implode('', array_map('gdrcd_stmt_bind_type', $binds));
        array_unshift($binds, $stringTypes);

        // MySQLi requires references for bind_param
        $refs = array();

        foreach ($binds as $k => $v) {
            $refs[$k] = &$binds[$k];
        }

        array_unshift($refs, $mysqliStmt);
        call_user_func_array('mysqli_stmt_bind_param', $refs);
    }

    if (!mysqli_stmt_execute($mysqliStmt)) {
        $mysqliStmtError = mysqli_stmt_error($mysqliStmt);
        $errorMsg = gdrcd_database_error_format($mysqliStmtError);
        mysqli_stmt_close($mysqliStmt);

        gdrcd_database_error_handle($errorMsg, $sql, $binds, $throwOnError);
    }

    $resultArr = array(
        'data' => null,
        'num_rows' => null,
        'affected' => null,
        'last_id' => null,
    );

    $meta = mysqli_stmt_result_metadata($mysqliStmt);

    if ($meta) {

        // SELECT-like query
        $result = mysqli_stmt_get_result($mysqliStmt);
        if ($result === false) {
            $mysqliStmtError = mysqli_stmt_error($mysqliStmt);
            $errorMsg = gdrcd_database_error_format($mysqliStmtError);
            mysqli_stmt_close($mysqliStmt);

            gdrcd_database_error_handle($errorMsg, $sql, $binds, $throwOnError);
        }

        $rows = array();

        while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
            $rows[] = $row;
        }

        $resultArr['data'] = new StmtResultData($rows);
        $resultArr['num_rows'] = mysqli_num_rows($result);
        mysqli_free_result($result);

    } else {

        // Non-SELECT query
        $resultArr['affected'] = mysqli_stmt_affected_rows($mysqliStmt);

        // Check if it's an INSERT
        if (preg_match('/^\s*INSERT\s/i', $sql)) {
            $resultArr['last_id'] = mysqli_stmt_insert_id($mysqliStmt);
        }

    }

    return $resultArr;
}

/**
 * Esegue una query preparata e restituisce una singola riga di risultato.
 *
 * Questa funzione è un helper che esegue una query con prepared statement
 * e restituisce solo la prima riga del risultato. Utile per query che si aspettano
 * un unico risultato (es. SELECT con WHERE su chiave primaria).
 *
 * @param string $sql La query SQL da eseguire, con i segnaposto (?) per i parametri.
 * @param array $binds Array dei parametri da associare alla query.
 *                     I tipi vengono determinati automaticamente.
 *                     Esempio: ['Mario', 25]
 * @param array $options Array di opzioni opzionali.
 *  - throw: se valorizzato a true la query lancia un'eccezione invece di interrompere l'esecuzione dello script
 *
 * @return array|false La prima riga del risultato come array associativo e numerico,
 *                     o false se non ci sono risultati.
 */
function gdrcd_stmt_one($sql, $binds = [], $options = [])
{
    $stmt = gdrcd_stmt($sql, $binds, $options);
    $row = gdrcd_query($stmt, 'fetch');
    gdrcd_query($stmt, 'free');

    return $row;
}

/**
 * Esegue una query preparata e restituisce tutte le righe di risultato tramite un generatore.
 *
 * Questa funzione è un helper che esegue una query con prepared statement
 * e restituisce le righe una alla volta tramite un generatore (yield), ottimizzando
 * l'utilizzo della memoria per query con molti risultati.
 *
 * @param string $sql La query SQL da eseguire, con i segnaposto (?) per i parametri.
 * @param array $binds Array dei parametri da associare alla query.
 *                     I tipi vengono determinati automaticamente.
 *                     Esempio: ['Mario', 25]
 * @param array $options Array di opzioni opzionali.
 *  - throw: se valorizzato a true la query lancia un'eccezione invece di interrompere l'esecuzione dello script
 *
 * @return Generator Generatore che produce le righe del risultato una alla volta,
 *                   ogni riga è un array associativo e numerico.
 */
function gdrcd_stmt_all($sql, $binds = [], $options = [])
{
    $stmt = gdrcd_stmt($sql, $binds, $options);

    while ($row = gdrcd_query($stmt, 'fetch')) {
        yield $row;
    }

    gdrcd_query($stmt, 'free');
}

/**
 * Determina il tipo di binding MySQLi per un valore.
 *
 * Questa funzione analizza un valore e restituisce il carattere corrispondente
 * al tipo di parametro MySQLi da utilizzare per il binding:
 *  - 'i' per integer
 *  - 'd' per double/float
 *  - 's' per string (anche per valori null)
 *  - 'b' per blob (dati binari non UTF-8)
 *
 * @param mixed $value Il valore di cui determinare il tipo.
 *
 * @return string Il carattere identificativo del tipo MySQLi ('i', 'd', 's', 'b').
 */
function gdrcd_stmt_bind_type($value)
{
    return match(true) {
    	is_null($value) => 's',
        is_int($value) => 'i',
        is_float($value) => 'd',
        ! mb_check_encoding($value, 'UTF-8') => 'b',
        default => 's'
    };
}

/**
 * Formatta una query preparata sostituendo i segnaposto con i valori dei parametri.
 *
 * Questa funzione è utile per il debug e il logging delle query, in quanto
 * sostituisce i segnaposto (?) nella query con i valori effettivi dei parametri,
 * formattandoli in modo appropriato (NULL, numeri senza apici, stringhe con apici).
 *
 * @param string $query La query SQL con i segnaposto (?).
 * @param array $param Array dei parametri da sostituire ai segnaposto.
 *
 * @return string La query formattata con i valori sostituiti ai segnaposto.
 */
function gdrcd_stmt_display($query, $param = []) {
    if (count($param) === 0) {
        return $query;
    }

    $i = 0;

    $formatted = preg_replace_callback('/\?/', function($match) use (&$i, $param) {
        //se non c'è un parametro corrispondente nell'array dei parametri
        if (!array_key_exists($i, $param)) {
            return '?';
        }

        //recupera il valore del parametro corrispondente
        $v = $param[$i++];

        //se è null lo sostituisce con il NULL di mysql
        if (is_null($v)) {
            return 'NULL';
        }

        //se è un valore numerico lo sostituisce normalmente
        if (is_numeric($v)) {
            return $v;
        }

        //altri casi
        $v = addslashes(str_replace("\\","",$v));

        return "'" . $v . "'";
    }, $query);

    return trim($formatted);
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
    $output = [];

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

function gdrcd_database_last_error_msg()
{
    $db_link = gdrcd_connect();
    return '[' . mysqli_errno($db_link) . '] ' . mysqli_error($db_link);
}

function gdrcd_database_error_handle($error, $sql = null, $binds = [], $throwOnError = false)
{
    if ($throwOnError) {
        throw new Exception($error);
    }

    $errorMsg = gdrcd_database_error_format(
        $error,
        gdrcd_stmt_display($sql, $binds)
    );

    die($errorMsg);
}

/**
 * Gestione degli errori tornati dalle query
 * @param ?string $details : una descrizione dell'errore avvenuto
 * @param ?string $sql : dump sql della query andata in errore
 * @return string: una stringa HTML che descrive l'errore riscontrato
 */
function gdrcd_database_error_format($details = null, $sql = null)
{
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 50);
    $history = '';

    $queryFunctions = [
        'gdrcd_stmt_one',
        'gdrcd_stmt_all',
        'gdrcd_stmt',
        'gdrcd_stmt_prepare',
        'gdrcd_stmt_execute',
        'gdrcd_stmt_close',
        'gdrcd_query',
    ];

    $base = null;

    foreach ($backtrace as $v) {
        if (in_array($v['function'], $queryFunctions)) {
            $base = $v;
        }
        $history .= '<strong>FILE</strong>: ' . $v['file'] . ' - ';
        $history .= '<strong>LINE</strong>: ' . $v['line'] . '</br />';
    }

    $error_msg  = '<div class="error mysql">';
    $error_msg .= '<strong>GDRCD Database Error</strong></br>';

    if ($details) {
        $error_msg .= '<strong>ERROR</strong>: ' . $details . '</br>';
    }

    if ($sql) {
        $error_msg .= '<strong>QUERY</strong>: ' . $sql . '</br>';
    }

    if ($base) {
        $error_msg .= '<strong>FILE</strong>: ' . $base['file'] . ' - ';
        $error_msg .= '<strong>LINE</strong>: ' . $base['line'] . '<br />';
    }

    $error_msg .= '<details>';
    $error_msg .= '<summary>Dettagli</summary>';
    $error_msg .= $history;
    $error_msg .= '</details>';
    $error_msg .= '</div>';
    return $error_msg;
}
