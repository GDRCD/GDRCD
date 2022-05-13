<?php

class DB extends BaseClass
{
    /**
     * @fn connect
     * @note Funzione di connessione al db
     * @return false|mysqli|null
     */
    public static function connect()
    {
        static $db_link = false;

        if ($db_link === false) {
            $db_user = $GLOBALS['PARAMETERS']['database']['username'];
            $db_pass = $GLOBALS['PARAMETERS']['database']['password'];
            $db_name = $GLOBALS['PARAMETERS']['database']['database_name'];
            $db_host = $GLOBALS['PARAMETERS']['database']['url'];
            $db_error = isset($GLOBALS['MESSAGE']['error']['db_not_found']) ? $GLOBALS['MESSAGE']['error']['db_not_found'] : 'Errore nel database';

            #$db = mysql_connect($db_host, $db_user, $db_pass)or die(gdrcd_mysql_error());
            #mysql_select_db($db_name)or die(gdrcd_mysql_error($db_error));

            $db_link = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

            mysqli_set_charset($db_link, "utf8");

            if (mysqli_connect_errno()) {
                self::error($db_error);
            }
        }
        return $db_link;
    }

    /**
     * @fn disconnect
     * @note Funzione di disconnessione del db
     * @param mysqli $db
     */
    public static function disconnect(mysqli $db)
    {
        mysqli_close($db);
    }

    /**
     * @fn query
     * @note Funzione di esecuzione delle query
     * @param mixed $sql
     * @param string $mode
     * @return mixed
     */
    public static function query($sql, string $mode = 'query')
    {

        $db_link = self::connect();

        switch (strtolower(trim($mode))) {
            case 'query':
                switch (strtoupper(substr(trim($sql), 0, 6))) {
                    case 'SELECT':
                        $result = mysqli_query($db_link, $sql) or die(self::error($sql));
                        $row = mysqli_fetch_array($result, MYSQLI_BOTH);
                        mysqli_free_result($result);

                        return $row;

                    default:
                        return mysqli_query($db_link, $sql) or die(self::error($sql));
                }

            case 'result':
                $result = mysqli_query($db_link, $sql) or die(self::error($sql));

                return $result;

            case 'num_rows':
                return (int)mysqli_num_rows($sql);

            case 'fetch':
                return mysqli_fetch_array($sql);

            case 'assoc':
                return mysqli_fetch_array($sql, MYSQLI_ASSOC);

            case 'object':
                return mysqli_fetch_object($sql);

            case 'free':
                mysqli_free_result($sql);
                break;

            case 'last_id':
                return mysqli_insert_id($db_link);

            case 'affected':
                return (int)mysqli_affected_rows($db_link);
            default:
                return '';
        }
    }

    /**
     * @fn rowsNumber
     * @note Ritorna il numero di risultati dell'array estratto dal db
     * @param array|object $array
     * @return int
     */
    public static function rowsNumber($array): int
    {
        if(gettype($array) == 'object' ){
            return self::query($array,'num_rows');
        }
        else if(gettype($array) == 'array') {
            return count($array);
        }
        else{
            return 0;
        }
    }

    /**
     * @fn statement
     * @note Statement delle query
     * @param $sql
     * @param array $binds
     * @return false|mysqli_result|void
     */
    public static function statement($sql, array $binds = array()){
        $db_link = self::connect();

        if ($stmt = mysqli_prepare($db_link, $sql)) {

            if (!empty($binds)) {

                #> E' necessario referenziare ogni parametro da passare alla query
                #> MySqli è suscettibile in proposito.
                $ref = array();

                foreach ($binds as $k => $v) {
                    if ($k > 0) {
                        $ref[$k] = &$binds[$k];
                    } else {
                        $ref[$k] = $v;
                    }
                }

                array_unshift($ref, $stmt);
                call_user_func_array('mysqli_stmt_bind_param', $ref);
            }

            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $stmtError = mysqli_stmt_error($stmt);

            if (!empty($stmtError))
                die(self::error($stmtError));

            mysqli_stmt_close($stmt);

            return $result;

        } else {
            die(self::error('Failed when creating the statement.'));
        }
    }

    /**
     * @fn checkTable
     * @note Creazione di un grafico in array del db
     * @param string $table
     * @return array
     */
    public static function checkTable(string $table): array
    {
        $result = self::query("SELECT * FROM $table LIMIT 1", 'result');
        $describe = self::query("SHOW COLUMNS FROM $table", 'result');

        $i = 0;
        $output = [];

        while ($field = self::query($describe, 'object')) {
            $defInfo = mysqli_fetch_field_direct($result, $i);

            $field->auto_increment = (strpos($field->Extra, 'auto_increment') === false ? 0 : 1);
            $field->definition = $field->Type;

            if ($field->Null == 'NO' && $field->Key != 'PRI') {
                $field->definition .= ' NOT NULL';
            }

            if ($field->Default) {
                $field->definition .= " DEFAULT '" . mysqli_real_escape_string(self::connect(), $field->Default) . "'";
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
        self::query($describe, 'free');

        return $output;
    }

    /**
     * @fn error
     * @note Visualizzazione intelligente degli errori
     * @param string|false $details
     * @return string
     */
    public static function error($details = false): string
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 50);

        $error_msg = '<strong>GDRCD MySQLi Error</strong> [File: ' . basename($backtrace[1]['file']) . '; Line: ' . $backtrace[1]['line'] . ']<br>' . '<strong>Error Code</strong>: ' . mysqli_errno(self::connect()) . '<br>' . '<strong>Error String</strong>: ' . mysqli_error(self::connect());

        if ($details !== false) {
            $error_msg .= '<br><br><strong>Error Detail</strong>: ' . $details;
        }

        return $error_msg;
    }
}