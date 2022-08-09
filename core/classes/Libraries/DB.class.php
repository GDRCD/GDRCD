<?php
/**
 * Descrive il riferimento di una query generata dalla classe DB
 * Questo riferimento permette di accedere a tutte le informazioni
 * relative alla query sql per cui è stato generato, ma generalmente
 * è pensato per essere passato ai diversi metodi della classe DB
 * di modo da effettuare le varie operazioni richieste.
 */
interface DBQueryInterface {
    /**
     * Ritorna la stringa SQL usata per formulare la query al db
     * @return string
     */
    public function getSQL(): string;

    /**
     * Ritorna il numero di righe coinvolte nella query di INSERT/UPDATE/DELETE eseguita
     * @return int
     */
    public function getAffectedRows(): int;

    /**
     * Ritorna il numero di righe trovate dalla query di SELECT eseguita
     * @return int
     */
    public function getNumRows(): int;

    /**
     * Ritorna l'ultimo id autoincrementante generato per la query di INSERT eseguita
     * @return string|false
     */
    public function getInsertId(): string|false;

    /**
     * Ritorna tutte le righe recuperate dall'ultima query di select come array multidimensionale
     * @return array
     */
    public function getData(): array;

    /**
     * Permette di eseguire la query di riferimento popolando gli eventuali placeholder con i nuovi parametri forniti
     * @param array|null $params
     * @return void
     */
    public function execute(?array $params = null): void;
}

/**
 * Fornisce i metodi necessari per le operazioni col database.
 * Non è necessario istanziare esplicitamente la connessione,
 * se un metodo ha bisogno di usare il database chiederà in
 * autonomia di connettersi.
 *
 * Nota: Per il corretto funzionamento è importante aggiornare
 * i dati di connessione nel file ./core/db_config.php
 */
class DB extends BaseClass
{
    /**
     * @var int Questo flag indica di usare la modalità di segnalazione
     * errori standard di GDRCD. Al primo errore la classe terminerà
     * l'esecuzione inviando in output l'errore formattato in html
     */
    const ERROR_STANDARD = 0;

    /**
     * @var int Questo flag indica di lanciare tutti gli errori del
     * database come eccezioni di modo che possano essere gestite
     * dall'esterno e senza interrompere forzatamente lo script.
     */
    const ERROR_EXCEPTION = 1;

    /**
     * @var PDO|null Istanza della classe che viene utilizzata per le
     * richieste al database.
     */
    private static ?PDO $PDO = null;

    /** @var int Contiene la modalità scelta per il report errori */
    private static int $currentErrorMode = self::ERROR_STANDARD;

    /**
     * Apre la connessione al database mysql di GDRCD.
     * Nota: Non è necessario usare questo metodo esplicitamente per
     * creare la connessione. Alla prima query che si cercherà di
     * fare sarà chiamato in maniera automatica.
     * @return PDO istanza della classe PDO utilizzata internamente
     * @throws Throwable in caso la connessione fallisca questo
     * metodo puà produrre un eccezione se la modalità errori è
     * configurata per questo comportamento
     */
    public static function connect(): PDO
    {
        if (is_null(self::$PDO))
        {
            /*
             * Nota: la codifica utf8 di PHP utilizza gruppi di 4 byte, mentre "utf8" di mysql ne utilizza 3.
             * Per essere al riparo in maniera certa da qualsiasi potenziale errore di codifica, quella
             * corretta in mysql dovrebbe essere "utf8mb4", ma al momento le tabelle del db vengono dichiarate
             * in "utf8". Dal momento che avere un set di caratteri differente tra connessione e tabelle è
             * tendenzialmente più problematico, la connessione per il momento verrà istanziata in codifica
             * "utf8" a 3 byte.
             * TODO: le tabelle del db devono essere in utf8mb4 (e preferibilmente innoDB). Anche la connessione dovrà usare lo stesso charset
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
     * Chiude la connessione al database
     * @return void
     */
    public static function disconnect(): void
    {
        self::$PDO = null;
    }

    /**
     * Ritorna il nome del database usato per la connessione
     * @return false|string Se il nome del database non è definito da
     * configurazione, questo metodo può ritornare il booleano FALSE
     */
    public static function getDbName(): false|string
    {
        if (!empty($GLOBALS['PARAMETERS']['database']['database_name'])) {
            return $GLOBALS['PARAMETERS']['database']['database_name'];
        }

        return false;
    }

    /**
     * Crea un nuovo prepared statement.
     * Nella query, gli elementi variabili possono essere indicati con dei placeholder
     * appositi che sono indicati dal simbolo ":" usato come prefisso.
     * @param string $sql la query sql con tanto di placeholder da preparare
     * @return DBQueryInterface Riferimento della query appena preparata
     * @throws Throwable se la gestione errori è configurata per lanciare eccezioni
     * questo metodo può farlo in caso di problemi
     */
    public static function prepare(string $sql): DBQueryInterface
    {
        $stmt = null;

        try {
            $stmt = self::connect()->prepare($sql);
        } catch (PDOException $e) {
            self::error($e);
        }

        return new class($sql, $stmt) implements DBQueryInterface, ArrayAccess, Countable, Iterator {
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

            public function getSQL(): string {
                return $this->sql;
            }

            public function getAffectedRows(): int {
                return $this->affectedRows;
            }

            public function getNumRows(): int {
                return $this->numRows;
            }

            public function getInsertId(): string|false {
                return $this->insertId;
            }

            public function getData(): array {
                return $this->data;
            }

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
             * Questa implementazione rappresenta una piccola furbata per permettere
             * di accedere direttamente ai nodi dati di un recordset multidimensionale
             * permettendo di fatto in casi dove si recupera una singola riga di
             * accedere direttamente alle key della seconda dimensione omettendone
             * l'offset della prima, che viene implicitamente presa dall'indice interno
             * usato per soddisfare l'interfaccia Iterator
             */

            public function offsetExists(mixed $offset): bool {
                return isset($this->data[$this->iteratorIndex][$offset]);
            }

            public function offsetGet(mixed $offset): mixed {
                return $this->data[$this->iteratorIndex][$offset];
            }

            public function offsetSet(mixed $offset, mixed $value): void {
                $this->data[$this->iteratorIndex][$offset] = $value;
            }

            public function offsetUnset(mixed $offset): void {
                unset($this->data[$this->iteratorIndex][$offset]);
            }

            /*
             * Countable Interface
             * Questa implementazione permette di usare la funzione count()
             * per conoscere il numero di records recuperati
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

            public function current(): mixed {
                return $this->data[$this->iteratorIndex]?? null;
            }

            public function next(): void {
                ++$this->iteratorIndex;
            }

            public function key(): mixed {
                return $this->iteratorIndex;
            }

            public function valid(): bool {
                return isset($this->data[$this->iteratorIndex]);
            }

            public function rewind(): void {
                $this->iteratorIndex = 0;
            }
        };
    }

    /**
     * Chiede al database di eseguire lo statement con i parametri indicati e ne ritorna i risultati
     * @param DBQueryInterface $stmt lo statement preparato in precedenza tramite DB::prepare()
     * @param array|null $params se la query dello statement prevede dei parametri questi
     * possono essere specificati qui sotto forma di array associativo.
     * Ad esempio: ['nomeparametro' => $valoreparametro]
     * @return DBQueryInterface ritorna il riferimento dello statement eseguito e permette l'accesso ai dati
     * @throws Throwable se la gestione errori è configurata per lanciare eccezioni
     * questo metodo può farlo in caso di problemi
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
     * Esegue una query al database permettendo di formulare l'istruzione con dei placeholder
     * che verranno poi sostituiti dai valori passati in params.
     * @param string $sql una query sql con placeholder per i parametri variabili. I placeholder
     * sono sempre preceduti dal prefisso : (due punti) e una parola per identificarli. Es:
     * SELECT * FROM table WHERE column = :datoricerca
     * @param array|null $params se la query dello statement prevede dei parametri questi
     * possono essere specificati qui sotto forma di array associativo.
     * Ad esempio: ['datoricerca' => $valoreparametro]
     * @return DBQueryInterface Ritorna il riferimento della query che può essere direttamente
     * iterato per recuperare i dati in caso di una query di SELECT
     * @throws Throwable
     */
    public static function queryStmt(string $sql, ?array $params = null): DBQueryInterface
    {
        $stmt = self::prepare($sql);
        return self::execute($stmt, $params);
    }

    /**
     * Ritorna il numero di righe coinvolte nell'ultima operazione di INSERT/DELETE/UPDATE
     * eseguita dal riferimento passato in input
     * @param DBQueryInterface $stmt
     * @return int
     */
    public static function queryAffectedRows(DBQueryInterface $stmt): int {
        return $stmt->getAffectedRows();
    }

    /**
     * Ritorna l'id autoincrementante generato per l'ultima query di INSERT eseguita
     * @return string|false
     * @throws Throwable
     */
    public static function queryLastId(): string|false {
        return self::connect()->lastInsertId();
    }

    /**
     * Disattiva il commit automatico delle query e permette di realizzare una transazione sql
     * @return void
     * @throws Throwable
     */
    public static function beginTransaction(): void {
        self::connect()->beginTransaction();
    }

    /**
     * Effettua il commit simultaneo delle query accumulate nella transazione sql,
     * convalidando tutte le modifiche
     * @return void
     * @throws Throwable
     */
    public static function commit(): void {
        self::connect()->commit();
    }

    /**
     * Effettua il rollback simultaneo delle query accumulate nella transazione sql,
     * annullando ogni cambiamento
     * @return void
     * @throws Throwable
     */
    public static function rollback(): void {
        self::connect()->rollBack();
    }

    /**
     * Chiede al database di eseguire diverse operazioni a seconda del $mode indicato
     * @param string|DBQueryInterface $sql La query SQL da eseguire o il riferimento di una chiamata
     * a questo metodo col risultato di una query
     * @param string $mode A seconda del valore passato il metodo si comporta in modo differente:
     *
     *  . query: Default. Esegue la query sql fornita come primo parametro e ritorna la prima
     *           riga utile letta dal database. Se la query non è di SELECT, ritorna il riferimento
     *           tramite cui è possibile chiedere 'last_id' (in caso di query INSERT) e 'affected'.
     *
     *  . result: Esegue la query sql fornita come primo parametro e ritorna il riferimento della
     *            richiesta. Tale riferimento puà essere usato per richiedere in loop una quantità
     *            indefinita di righe dal database con i mode 'fetch', 'assoc' e 'object'.
     *
     *  . num_rows: Dato il riferimento di una query di tipo SELECT ritorna il numero di righe
     *              recuperate dal database per quella query.
     *
     *  . fetch: Dato il riferimento di una query di tipo SELECT recupera la prossima riga disponibile
     *           dal database. E' pensato per poter essere iterato in un loop al fine di recuperare
     *           tutte le righe disponibili quando non si conosce arbitrariamente il numero.
     *           Le righe recuperate sono sotto forma di array associativi.
     *
     *  . assoc: Alias di 'fetch'.
     *
     *  . object: Agisce come 'fetch' ma le righe recuperate vengono passate come oggetto
     *
     *  . last_id: Fornito in ingresso il riferimento di una query di tipo INSERT ritorna l'ultimo
     *             id autoincrementante generato dalla query
     *
     *  . affected: Fornito in ingresso il riferimento di una query di tipo INSERT/DELETE/UPDATE
     *              ritorna il numero di righe coinvolte dall'operazione richiesta.
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
     * Ritorna il numero di risultati dell'array estratto dal db
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
     * Creazione di un grafico in array del db
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
     * Cambia il modo in cui la classe DB comunica un errore
     * @see DB::ERROR_STANDARD
     * @see DB::ERROR_EXCEPTION
     * @param int $flag accetta in ingresso una qualsiasi tra le
     * due costanti DB::ERROR_STANDARD e DB::ERROR_EXCEPTION
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
     * Si occupa della gestione degli errori in base alla modalità scelta
     * @param Throwable $e l'istanza di un eccezione rappresentante l'errore
     * @param string|null $details eventuali dettagli aggiuntivi utili al debug
     * @return void
     * @throws Throwable se configurato in proposito, questo metodo rilancia
     * l'eccezione ricevuta in input all'esterno
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
