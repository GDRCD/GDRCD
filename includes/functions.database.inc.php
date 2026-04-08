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

    // Rileva se l'input è già un risultato (object o array) o una query string
    $isResultObject = is_object($sql) && ($sql instanceof mysqli_result);
    $isStmtResult = (is_array($sql) && $sql['data'] instanceof StmtResultData) || (is_array($sql) && isset($sql['affected']));
    //veccio metodo non modificato, mantiene la retrocompatibilità e gestisce le query normali
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
            if ($isStmtResult) {
                // Restituisce l'intero array di dati per i prepared statements
                return $sql['data'];
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

function gdrcd_stmt_prepare($sql, $options = [])
{
    $db_link = gdrcd_connect();

    return [
        'sql' => $sql,
        'stmt' => mysqli_prepare($db_link, $sql),
        'options' => $options,
    ];
}

function gdrcd_stmt_close($stmt)
{
    mysqli_stmt_close($stmt['stmt']);
}

function gdrcd_stmt_execute($stmt, $binds = [])
{
    $sql = $stmt['sql'];
    $mysqliStmt = $stmt['stmt'];
    $options = $stmt['options'];

    $throwOnError = !empty($options['throw']);

    if ($mysqliStmt === false) {
        $errorMsg = gdrcd_mysql_error('Failed when creating the statement.');

        if ($throwOnError) {
            throw new Exception($errorMsg);
        } else {
            die($errorMsg);
        }
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
        $errorMsg = gdrcd_mysql_error($mysqliStmtError);
        mysqli_stmt_close($mysqliStmt);

        if ($throwOnError) {
            throw new Exception($errorMsg);
        } else {
            die($errorMsg);
        }
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
            $errorMsg = gdrcd_mysql_error($mysqliStmtError);
            mysqli_stmt_close($mysqliStmt);
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

function gdrcd_stmt_one($sql, $binds = [], $options = [])
{
    $stmt = gdrcd_stmt($sql, $binds, $options);
    $row = gdrcd_query($stmt, 'fetch');
    gdrcd_query($stmt, 'free');

    return $row;
}

function gdrcd_stmt_all($sql, $binds = [], $options = [])
{
    $stmt = gdrcd_stmt($sql, $binds, $options);

    while ($row = gdrcd_query($stmt, 'fetch')) {
        yield $row;
    }

    gdrcd_query($stmt, 'free');
}

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

function gdrcd_stmt_display($query, $param) {
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
    $error_msg .= '<strong>ERROR [' . mysqli_errno(gdrcd_connect()) . ']</strong>: ' . mysqli_error(gdrcd_connect()) .'<br />';
    $error_msg .= '<strong>FILE: </strong>: ' . $base['file'] . ' - ';
    $error_msg .= '<strong>LINE: </strong>: ' . $base['line'] . '<br />';
    $error_msg .= '<details>';
    $error_msg .= '<summary>Dettagli</summary>';
    $error_msg .= $history;
    $error_msg .= '</details>';
    $error_msg .= '</div>';
    return $error_msg;
}
