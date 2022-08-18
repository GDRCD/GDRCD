<?php
/**
 * @interface DBQueryInterface
 * @note This interface is implemented by all DBQuery classes.
 */
interface DBQueryInterface extends ArrayAccess, Countable, Iterator {

    /**
     * @fn getSQL
     * @note Ritorna la stringa SQL usata per formulare la query al db
     * @return string
     */
    public function getSQL(): string;

    /**
     * @fn getAffectedRows
     * @note Ritorna il numero di righe coinvolte nella query di INSERT/UPDATE/DELETE eseguita
     * @return int
     */
    public function getAffectedRows(): int;

    /**
     * @fn getNumRows
     * @note Ritorna il numero di righe trovate dalla query di SELECT eseguita
     * @return int
     */
    public function getNumRows(): int;

    /**
     * @fn getInsertId
     * @note Ritorna l'ultimo id autoincrementante generato per la query di INSERT eseguita
     * @return string|false
     */
    public function getInsertId(): string|false;

    /**
     * @fn getData
     * @note Ritorna tutte le righe recuperate dall'ultima query di select come array multidimensionale
     * @return array
     */
    public function getData(): array;

    /**
     * @fn execute
     * @note Permette di eseguire la query di riferimento popolando gli eventuali placeholder con i nuovi parametri forniti
     * @param array|null $params
     * @return void
     */
    public function execute(?array $params = null): void;
}

/**
 * @class DB
 * @note Classe che gestisce le connessioni al database
 */
class DB extends BaseClass
{
    /**
     * @var int ERROR_STANDARD
     * @note Questo flag indica di usare la modalità di segnalazione errori standard di GDRCD. Al primo errore la classe terminerà
     * l'esecuzione inviando in uscita l'errore formattato in html
     */
    const ERROR_STANDARD = 0;

    /**
     * @var int ERROR_EXCEPTION
     * @note Questo flag indica di lanciare tutti gli errori del
     * Database come eccezioni di modo che possano essere gestite
     * dall'esterno e senza interrompere forzatamente lo script.
     */
    const ERROR_EXCEPTION = 1;

    /**
     * @var PDO|null
     * @note Istanza della classe che viene utilizzata per le richieste al database.
     */
    private static ?PDO $PDO = null;

    /**
     * @var int
     * @note Contiene la modalità scelta per il report errori
     */
    private static int $currentErrorMode = self::ERROR_STANDARD;

    /**
     * @fn connect
     * @note Apre la connessione al database mysql di GDRCD.
     * @note Se la connessione non è ancora aperta, la crea.
     * @return PDO Istanza della classe PDO utilizzata internamente
     * @throws Throwable Se la connessione non è possibile
     */
    public static function connect(): PDO
    {
        if (is_null(self::$PDO))
        {
            /**
             * TODO: le tabelle del db devono essere in utf8mb4 (e preferibilmente innoDB). Anche la connessione dovrà usare lo stesso charset
             * @note la codifica utf8 di PHP utilizza gruppi di 4 byte, mentre "utf8" di mysql ne utilizza 3.
             * Per essere al riparo in maniera certa da qualsiasi potenziale errore di codifica, quella
             * corretta in mysql dovrebbe essere "utf8mb4", ma al momento le tabelle del db vengono dichiarate
             * in "utf8". Dal momento che avere un set di caratteri differente tra connessione e tabelle è
             * tendenzialmente più problematico, la connessione per il momento verrà istanziata in codifica
             * "utf8" a 3 byte.
             */
            $db_charset = 'utf8';
            $db_port = 3306;
            $db_user = $GLOBALS['PARAMETERS']['database']['username'];
            $db_pass = $GLOBALS['PARAMETERS']['database']['password'];
            $db_name = $GLOBALS['PARAMETERS']['database']['database_name'];
            $db_host = $GLOBALS['PARAMETERS']['database']['url'];

            if (str_contains($db_host, ':')) {
                [$db_host, $db_port] = explode(':', $db_host, 2);
            }

            try {

                self::$PDO = new PDO(
                    sprintf(
                        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                        $db_host,
                        $db_port,
                        $db_name,
                        $db_charset
                    ),
                    $db_user,
                    $db_pass,
                    array(
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_STRINGIFY_FETCHES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '". date('P') ."'",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    )
                );

            } catch (PDOException $e) {

                self::error(
                    new Exception(
                        $GLOBALS['MESSAGE']['error']['db_not_found']?? 'Impossibile stabilire la connesione al database',
                        0,
                        $e
                    )
                );

            }
        }

        return self::$PDO;
    }

    /**
     * @fn disconnect
     * @note Chiude la connessione al database
     * @return void
     */
    public static function disconnect(): void
    {
        self::$PDO = null;
    }

    /**
     * @fn getDbName
     * @note Ritorna il nome del database usato per la connessione
     * @note Se il nome del database non è definito da configurazione, questo metodo può ritornare false
     * @return false|string
     */
    public static function getDbName(): false|string
    {
        if (!empty($GLOBALS['PARAMETERS']['database']['database_name'])) {
            return $GLOBALS['PARAMETERS']['database']['database_name'];
        }

        return false;
    }

    /**
     * @fn prepare
     * @note Esegue una query preparata al database
     * @note Nella query, gli elementi variabili possono essere indicati con dei placeholder appositi che sono indicati dal simbolo ":" usato come prefisso.
     * @param string $sql la query sql con tanto di placeholder da preparare
     * @return DBQueryInterface Riferimento della query appena preparata
     * @throws Throwable Se la gestione errori è configurata per lanciare eccezioni questo metodo può farlo in caso di problemi
     */
    public static function prepare(string $sql): DBQueryInterface
    {
        $stmt = null;

        try {
            $stmt = self::connect()->prepare($sql);
        } catch (PDOException $e) {
            self::error($e);
        }

        return new class($sql, $stmt) implements DBQueryInterface {
            private int $iteratorIndex = 0;
            private int $numRows = 0;
            private int $affectedRows = 0;
            private string|false $insertId = false;
            private array $data = [];
            private string $sql;
            private PDOStatement $PDOStatement;

            public function __construct(string $sql, PDOStatement $PDOStatement) {
                $this->sql = $sql;
                $this->PDOStatement = $PDOStatement;
            }

            /*
             * DBQueryReference Interface
             */

            /**
             * @fn getSQL
             * @note Ritorna la stringa sql inserita
             * @return string
             */
            public function getSQL(): string {
                return $this->sql;
            }

            /**
             * @fn getAffectedRows
             * @note Ritorna il numero di righe affette dalla query
             * @return int
             */
            public function getAffectedRows(): int {
                return $this->affectedRows;
            }

            /**
             * @fn getNumRows
             * @note Ottieni il numero di righe
             * @return int
             */
            public function getNumRows(): int {
                return $this->numRows;
            }

            /**
             * @fn getInsertId
             * @note Ottieni l'id della riga appena inserita
             * @return string|false
             */
            public function getInsertId(): string|false {
                return $this->insertId;
            }

            /**
             * @fn getData
             * @note Ottieni i dati contenuti nella query
             * @return array
             */
            public function getData(): array {
                return $this->data;
            }

            /**
             * @fn execute
             * @note Esegui la query
             * @param array|null $params
             * @return void
             */
            public function execute(?array $params = null): void {
                $this->PDOStatement->execute($params);
                $this->affectedRows = $this->PDOStatement->rowCount();
                $this->data = $this->PDOStatement->fetchAll()?? [];
                $this->numRows = count($this->data);
                $this->insertId = DB::connect()->lastInsertId();
                $this->PDOStatement->closeCursor();
            }

            /*
             * ArrayAccess Interface
             * Questa implementazione permette
             * di accedere direttamente ai nodi dati di un recordset multidimensionale
             * permettendo di fatto in casi dove si recupera una singola riga di
             * accedere direttamente alle key della seconda dimensione omettendone
             * l'offset della prima, che viene implicitamente presa dall'indice interno
             * usato per soddisfare l'interfaccia Iterator
             */

            /**
             * @fn offsetExists
             * @note Controlla se una chiave esiste
             * @param mixed $offset
             * @return bool
             */
            public function offsetExists(mixed $offset): bool {
                return isset($this->data[$this->iteratorIndex][$offset]);
            }

            /**
             * @fn offsetGet
             * @note Ottieni il valore di una chiave
             * @param mixed $offset
             * @return mixed
             */
            public function offsetGet(mixed $offset): mixed {
                return $this->data[$this->iteratorIndex][$offset];
            }

            /**
             * @fn offsetSet
             * @note Imposta il valore di una chiave
             * @param mixed $offset
             * @param mixed $value
             * @return void
             */
            public function offsetSet(mixed $offset, mixed $value): void {
                $this->data[$this->iteratorIndex][$offset] = $value;
            }

            /**
             * @fn offsetUnset
             * @note Cancella una chiave
             * @param mixed $offset
             * @return void
             */
            public function offsetUnset(mixed $offset): void {
                unset($this->data[$this->iteratorIndex][$offset]);
            }

            /*
             * Countable Interface
             * Questa implementazione permette di usare la funzione count()
             * per conoscere il numero di records recuperati
             */

            /**
             * @fn count
             * @note Ottieni il numero di records recuperati
             * @return int
             */
             public function count(): int {
                 return $this->getNumRows();
             }

            /*
             * Iterator Interface
             * Questa implementazione permette iterare gli statement eseguiti
             * di una query di select come fosse un array pur rimanendo di base
             * un oggetto di tipo DBQueryInterface
             */

            /**
             * @fn current
             * @note Ottieni il valore della riga corrente
             * @return mixed
             */
            public function current(): mixed {
                return $this->data[$this->iteratorIndex]?? null;
            }

            /**
             * @fn next
             * @note Ottieni il valore della riga successiva
             * @return void
             */
            public function next(): void {
                ++$this->iteratorIndex;
            }

            /**
             * @fn key
             * @note Ottieni la chiave della riga corrente
             * @return mixed
             */
            public function key(): mixed {
                return $this->iteratorIndex;
            }

            /**
             * @fn valid
             * @note Controlla se la riga corrente è valida
             * @return bool
             */
            public function valid(): bool {
                return isset($this->data[$this->iteratorIndex]);
            }

            /**
             * @fn rewind
             * @note Riavvia l'iterazione
             * @return void
             */
            public function rewind(): void {
                $this->iteratorIndex = 0;
            }
        };
    }

    /**
     * @fn execute
     * @note Esegui una query con statement
     * @param DBQueryInterface $stmt Lo statement preparato in precedenza tramite DB::prepare()
     * @param array|null $params Parametri da passare alla query
     * @return DBQueryInterface Lo statement eseguito
     * @throws Throwable Se la query non è stata eseguita con successo
     */
    public static function execute(DBQueryInterface $stmt, ?array $params = null): DBQueryInterface
    {
        try {
            $stmt->execute($params);
        } catch (PDOException $e) {
            self::error($e);
        }

        return $stmt;
    }

    /**
     * @fn queryStmt
     * @note Esegue una query di tipo statement
     * @param string $sql
     * @param array|null $params
     * @return DBQueryInterface Lo statement eseguito
     * @throws Throwable Se la query non è stata eseguita con successo
     */
    public static function queryStmt(string $sql, ?array $params = null): DBQueryInterface
    {
        $stmt = self::prepare($sql);
        return self::execute($stmt, $params);
    }

    /**
     * @fn queryAffectedRows
     * @note Ritorna il numero di righe coinvolte nell'ultima operazione
     * @param DBQueryInterface $stmt Lo statement eseguito
     * @return int Il numero di righe coinvolte
     */
    public static function queryAffectedRows(DBQueryInterface $stmt): int {
        return $stmt->getAffectedRows();
    }

    /**
     * @fn queryLastId
     * @note Ritorna l'id dell'ultimo record inserito
     * @return string|false L'id dell'ultimo record inserito
     * @throws Throwable Se la query non è stata eseguita con successo
     */
    public static function queryLastId(): string|false {
        return self::connect()->lastInsertId();
    }

    /**
     * @fn beginTransaction
     * @note Inizia una transazione
     * @return void
     * @throws Throwable
     */
    public static function beginTransaction(): void {
        self::connect()->beginTransaction();
    }

    /**
     * @fn commit
     * @note Effettua il commit della transazione
     * @return void
     * @throws Throwable
     */
    public static function commit(): void {
        self::connect()->commit();
    }

    /**
     * @fn rollback
     * @note Annulla il commit della transazione
     * @return void
     * @throws Throwable
     */
    public static function rollback(): void {
        self::connect()->rollBack();
    }

    /**
     * @fn query
     * @note Chiede al database di eseguire diverse operazioni a seconda del $mode indicato
     * @param string|DBQueryInterface $sql La query da eseguire
     * @param string $mode A seconda del valore passato il metodo si comporta in modo differente:
     *
     *  - query: Default. Esegue la query e ritorna il risultato
     *
     *  - result: Ritorna il risultato della query
     *
     *  - num_rows: Ritorna il numero di righe coinvolte nella query
     *
     *  - fetch: Ritorna il primo record della query
     *
     *  - assoc: Ritorna il primo record della query come array associativo
     *
     *  - object: Ritorna il primo record della query come oggetto
     *
     *  - last_id: Ritorna l'id dell'ultimo record inserito
     *
     *  - affected: Ritorna il numero di righe coinvolte nella query
     *
     * @return mixed
     * @throws Throwable
     */
    public static function query(string|DBQueryInterface $sql, string $mode = 'query'): mixed
    {
        switch (strtolower(trim($mode))) {
            case 'query':
                $stmt = self::queryStmt($sql);
                if (strtoupper(substr(trim($sql), 0, 6)) !== 'SELECT') {
                    return $stmt;
                }
                return $stmt->current();

            case 'result':
                return self::queryStmt($sql);

            case 'num_rows':
                return self::rowsNumber($sql);

            case 'fetch':
            case 'assoc':
                $row = $sql->current();
                $sql->next();
                return $row;

            case 'object':
                $row = $sql->current();
                $sql->next();
                return !is_null($row)? (object)$row : null;

            case 'free':
                /** Totalmente disabilitato, non serve con PDO */
                break;

            case 'last_id':
                return $sql->getInsertId();

            case 'affected':
                return self::queryAffectedRows($sql);
        }

        return '';
    }

    /**
     * @fn rowsNumber
     * @note Ritorna il numero di righe estratte dalla query
     * @param array|DBQueryInterface $array
     * @return int
     */
    public static function rowsNumber(array|DBQueryInterface $array): int
    {
        if ($array instanceof DBQueryInterface) {
            return $array->getNumRows();
        }

        return count($array);
    }

    /**
     * @gn checkTable
     * @note Creazione di un grafico in array del db
     * @param string $table
     * @return array
     * @throws Throwable
     */
    public static function checkTable(string $table): array
    {
        $PDO = self::connect();
        $result = $PDO->query("SELECT * FROM `$table` LIMIT 1");
        $describe = self::query("SHOW COLUMNS FROM `$table`", 'result');

        $i = 0;
        $output = [];
        while ( $field = self::query($describe, 'object') ) {
            $defInfo = $result->getColumnMeta($i);

            $field->auto_increment = (strpos($field->Extra, 'auto_increment') === false ? 0 : 1);
            $field->definition = $field->Type;

            if ( $field->Null == 'NO' && $field->Key != 'PRI' ) {
                $field->definition .= ' NOT NULL';
            }

            if ( $field->Default ) {
                $field->definition .= " DEFAULT '" . Filters::int($field->Default) . "'";
            }

            if ( $field->auto_increment ) {
                $field->definition .= ' AUTO_INCREMENT';
            }

            switch ( $field->Key ) {
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

            $field->len = $defInfo['len'];
            $output[$field->Field] = $field;
            ++$i;

            unset($defInfo);
        }

        return $output;
    }

    /**
     * @fn errorMode
     * @note Cambia il modo in cui la classe DB comunica un errore
     * @see DB::ERROR_STANDARD
     * @see DB::ERROR_EXCEPTION
     * @param int $flag DB::ERROR_STANDARD, DB::ERROR_EXCEPTION
     * @return void
     */
    public static function errorMode(int $flag): void
    {
        self::$currentErrorMode = match($flag) {
            self::ERROR_STANDARD,
            self::ERROR_EXCEPTION => $flag,
            default => throw new InvalidArgumentException('[DB::errorMode] Parametro $flag invalido')
        };
    }

    /**
     * @fn error
     * @note Si occupa della gestione degli errori in base alla modalità scelta
     * @param Throwable $e L'istanza di un eccezione rappresentante l'errore
     * @param string|null $details Eventuali dettagli aggiuntivi utili al debug
     * @return void
     * @throws Throwable
     */
    protected static function error(Throwable $e, ?string $details = null): void
    {
        //> Se il report errori è configurato per lanciare le eccezioni all'esterno è così che faremo
        if (self::$currentErrorMode === self::ERROR_EXCEPTION) {
            throw $e;
        }

        //> In primis, rendiamo partecipe PHP del problema. Che averne traccia su un file di log aiuta non poco
        error_log(
            sprintf(
                'GDRCD Database Error: %s%s',
                $e,
                !is_null($details)? PHP_EOL . 'Context: '. $details : ''
            )
        );

        //> A questo punto formattiamo il messaggio d'errore da mostrare all'esterno
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 50);
        $error_msg = sprintf(
            '<strong>GDRCD Database Error</strong><br>'
            . '<strong>Code</strong>: %s<br>'
            . '<strong>Message</strong>: %s<br>'
            . '<strong>From</strong>: %s:%s<br>',
            $e->getCode(),
            $e->getMessage(),
            $backtrace[1]['file'],
            $backtrace[1]['line']
        );

        if (!is_null($details)) {
            $error_msg .= '<strong>Context</strong>: '. $details .'<br>';
        }

        $error_msg .= '<strong>Trace</strong>: '. $e->getTraceAsString() .'<br>';
        die('<div>'. $error_msg .'</div>');
    }
}
