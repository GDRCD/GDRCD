<?php

/**
 * Implementa le funzioni di migrazione automatica dello schema del database da una versione all'altra
 */
class DbMigrationEngine extends BaseClass
{
    const MIGRATIONS_FOLDER = __DIR__ . '/../../../db_versions/';
    const SQL_FILE = ROOT . '/gdrcd_db.sql';

    /**
     * @fn migrateDb
     * @note Funzione di migrazione INIZIALE del db partendo da file SQL
     * @return void
     * @throws Throwable
     */
    public static function migrateDb(): void
    {

        // GET SQL FILE CONTENT
        $sql_file = trim(file_get_contents(self::SQL_FILE));

        // AUTO CREATE - AUTO INSERT, FROM SQL FILE
        self::recursiveCreate($sql_file);

        // CREATE STARTING DB MIGRATION ID
        self::trackAppliedMigration('000000_start_db');

        // EXECUTE MIGRATIONS
        if ( self::dbNeedsUpdate() ) {
            self::updateDbSchema();
        }
    }

    /**
     * @fn recursiveCreate
     * @note Funzione recursive di creazione tabelle
     * @param $sql
     * @return void
     * @throws Throwable
     */
    public static function recursiveCreate($sql): void
    {
        $new_val = substr($sql, strpos($sql, 'CREATE TABLE'));
        if ( !empty($new_val) ) {
            $string = substr($new_val, 0, strpos($new_val, ';') + 1);
            DB::queryStmt($string, []);

            $new_sql = substr($new_val, strpos($new_val, ';') + 1);
            self::recursiveCreate($new_sql);
        }
    }

    /**
     * @fn resetDb
     * @note Funzione di reset del database
     * @return void
     * @throws ReflectionException
     * @throws Throwable
     */
    public static function resetDB(): void
    {
        $db = DB::getDbName();
        DB::queryStmt("SET FOREIGN_KEY_CHECKS = 0;", []);
        self::deleteDb($db);
        self::migrateDb();
    }

    /**
     * @fn deleteDb
     * @note Funzione recursive di eliminazione tabelle da db
     * @param $db
     * @return void
     * @throws Throwable
     */
    public static function deleteDb($db): void
    {
        $tables = DB::queryStmt("SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = :db  ORDER BY table_name ASC",
            [
                'db' => $db,
            ]);

        if ( $tables->getNumRows() > 0 ) {
            DB::queryStmt("DROP TABLE {$tables['TABLE_NAME']}", []);
            self::deleteDb($db);
        }

    }

    /**
     * @fn updateDbSchema
     * @note Esegue le modifiche allo schema del DB portandolo alla versione specificata
     * @param int|null $migration_id Versione a cui portare il DB, corrispondente all'identificativo di una
     * Migrazione specifica. Se vuoto porta il DB all'ultima versione disponibile
     * @return int Il numero di migrazioni applicate
     * @throws Throwable
     */
    public static function updateDbSchema(int $migration_id = null): int
    {
        $migrations = self::loadMigrationClasses();
        self::createVersioningTable();//Per sicurezza cerchiamo di crearla sempre
        $lastApplied = self::getLastAppliedMigration()->getData()[0];

        if ( empty($lastApplied) ) {
            self::performDbSetup($migrations, $lastApplied);
        }

        $directionUp = true;
        $migrationsToApply = self::getMigrationsToApply($migrations, $lastApplied, $migration_id, $directionUp);

        $applied = 0;
        DB::errorMode(DB::ERROR_EXCEPTION);

        foreach ( $migrationsToApply as $m ) {
            try {
                DB::beginTransaction();
                if ( $directionUp ) {
                    $m->up();
                    self::trackAppliedMigration($m->getMigrationId());
                } else {
                    $m->down();
                    self::untrackAppliedMigration($m->getMigrationId());
                }
                DB::commit();
                $applied++;
            } catch ( Throwable $e ) {
                //Attenzione questa è una misura di sicurezza debole: le DDL (CREATE TABLE, ALTER TABLE...) provocano
                // dei commit automatici, a questo punto in realtà non è già più possibile fare rollback
                DB::rollback();
                throw new Exception("Aggiornamento del database fallito: " . $e->getMessage(), 0, $e);
            }
        }

        DB::errorMode(DB::ERROR_STANDARD);
        return $applied;
    }

    /**
     * @fn dbNeedsInstallation
     * @note Indica se il database di GDRCD 6.0 è stato installato o meno
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public static function dbNeedsInstallation(): bool
    {
        return (self::getTablesCountInDb() <= 1 && !self::dbConfigExist());//Controlliamo sempre uno e non zero, per escludere la tabella delle migration
    }

    /**
     * @fn dbConfigExist
     * @note Controlla se la tabella config esiste
     * @return bool
     * @throws Throwable
     */
    public static function dbConfigExist(): bool
    {
        $db = DB::getDbName();
        $config_table = DB::queryStmt("SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = :db AND table_name='config' LIMIT 1", [
            'db' => $db,
        ]);

        return $config_table->getNumRows() > 0;
    }

    /**
     * @fn dbNeedsUpdate
     * @note Indica se il database non si trova all'ultima versione disponibile e necessita quindi di applicare una
     * migration
     * @return bool
     * @throws Throwable
     */
    public static function dbNeedsUpdate(): bool
    {
        if ( self::dbNeedsInstallation() ) {
            return true;
        }

        $migrations = self::loadMigrationClasses();
        self::createVersioningTable();//Per sicurezza cerchiamo di crearla sempre
        $lastApplied = self::getLastAppliedMigration();

        return empty($lastApplied) or $migrations[count($migrations) - 1]->getMigrationId() != (int)$lastApplied['migration_id'];
    }

    /**
     * @fn getAllAvailableMigration
     * @note Restituisce un array di tutte le migration disponibili a sistema
     * @return DbMigration[]
     * @throws ReflectionException
     */
    public static function getAllAvailableMigrations(): array
    {
        return self::loadMigrationClasses();
    }

    /**
     * @fn loadMigrationClasses
     * @note Carica in memoria le classi di migrazione
     * @return DbMigration[] Un array con un oggetto già instanziato per ogni migrazione, nel corretto ordine di
     * Esecuzione temporale delle migrazioni
     * @throws ReflectionException
     */
    private static function loadMigrationClasses(): array
    {
        /** @var DbMigration[] $migrations */
        $migrations = [];

        foreach ( new DirectoryIterator(self::MIGRATIONS_FOLDER) as $fileInfo ) {
            if ( $fileInfo->isDot() || $fileInfo->isDir() ) {
                continue;
            }
            $filename = basename($fileInfo->getRealPath(), '.php');
            $parts = explode("_", $filename);
            $className = $filename;
            if ( count($parts) > 1 ) {
                $className = $parts[1];
            }

            include_once $fileInfo->getRealPath();
            if ( class_exists($className) ) {
                $reflected = new ReflectionClass($className);
                if ( $reflected->isSubclassOf("DbMigration") ) {
                    $migrations[] = $reflected->newInstance();
                }
            }
        }

        //Ordinamento per id, da l'ordine di esecuzione
        usort($migrations, function ($a, $b) {
            $aId = $a->getMigrationId();
            $bId = $b->getMigrationId();

            if ( $aId == $bId ) {
                return 0;
            }
            return $aId < $bId ? -1 : 1;
        });

        return $migrations;
    }

    /**
     * @fn performDbSetup
     * @note Decide se eseguire il setup del sistema o se è già stato eseguito e quindi va solo aggiunta la tabella
     * Delle versioni saltando il setup
     * @param DbMigration[] $migrations
     * @throws Throwable
     */
    private static function performDbSetup(array $migrations, &$lastApplied)
    {
        $tablesCount = self::getTablesCountInDb();

        if ( $tablesCount > 1 ) {//Precedente installazione priva di Db Migrations (pre 5.6)?
            //Eseguiamo solo le migration dalla 5.6 in poi, assumendo il setup della 5.5.1 già fatto
            self::trackAppliedMigration($migrations[0]->getMigrationId());
            $lastApplied = self::getLastAppliedMigration();
        }
    }

    /**
     * @fn getMigrationsToApply
     * @note Identifica quali migrazioni devono essere applicate in base all'ultima applicata e alla migrazione
     * target richiesta
     * @param array $migrations
     * @param array $lastApplied
     * @param int|null $targetMigrationId
     * @param bool $directionUp
     * @return array
     * @throws Exception
     */
    private static function getMigrationsToApply(array $migrations, array $lastApplied, int|null $targetMigrationId, bool &$directionUp): array
    {
        $directionUp = true;
        if ( empty($targetMigrationId) ) {//Auto migration
            $firstToApply = 0;
            foreach ( $migrations as $k => $m ) {
                if ( (int)$m->getMigrationId() > (int)$lastApplied['migration_id'] ) {
                    $firstToApply = $k;
                    break;
                }
            }
            $migrationsToApply = array_slice($migrations, $firstToApply);
        } else {//migration verso una versione specifica
            $lastAppliedIdx = 0;
            $targetIdx = -1;
            foreach ( $migrations as $k => $m ) {
                if ( (int)$m->getMigrationId() == $lastApplied['migration_id'] ) {
                    $lastAppliedIdx = $k;
                }
                if ( (int)$m->getMigrationId() == $targetMigrationId ) {
                    $targetIdx = $k;
                }
                if ( $targetIdx != -1 && $lastAppliedIdx != 0 ) {
                    break;
                }
            }
            if ( $targetIdx == -1 ) {
                throw new Exception("La Versione del Database specificata non è stata trovata");
            }

            if ( $lastAppliedIdx > $targetIdx ) {
                $directionUp = false;
                $migrationsToApply = array_slice($migrations, $targetIdx + 1, ($lastAppliedIdx - $targetIdx));
            } else {
                $migrationsToApply = array_slice($migrations, $lastAppliedIdx + 1, ($targetIdx - $lastAppliedIdx));
            }
        }

        return $migrationsToApply;
    }

    /**
     * @fn getTablesCountInDb
     * @note Trova il numero di tabelle presenti nel DB
     * @return int
     * @throws Exception
     * @throws Throwable
     */
    private static function getTablesCountInDb(): int
    {
        $db = DB::getDbName();
        $count = DB::queryStmt("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :db", [
            'db' => $db,
        ]);
        return $count->getNumRows();
    }

    /**
     * @fn createVersioningTable
     * @note Crea sul DB la tabella per il tracciamento delle versioni del DB
     * @return void
     * @throws Throwable
     */
    private static function createVersioningTable(): void
    {
        DB::queryStmt("
            CREATE TABLE IF NOT EXISTS _gdrcd_db_versions(
                migration_id varchar(250) NOT NULL,
                applied_on datetime not null,
                constraint _gdrcd_db_versions_pk
                primary key (migration_id)
            );
        ");
    }

    /**
     * @fn trackAppliedMigration
     * @note Aggiunge alla tabella delle migrazioni effettuate la migrazione specificata in $migration_id
     * @param $migration_id
     * @return void
     * @throws Throwable
     */
    private static function trackAppliedMigration($migration_id): void
    {
        DB::queryStmt("INSERT INTO _gdrcd_db_versions (migration_id,applied_on) VALUE (:id, NOW())", [
            'id' => $migration_id,
        ]);
    }

    /**
     * @fn untrackAppliedMigration
     * @note Toglie dalla tabella delle migrazioni effettuate la migrazione specificata in $migration_id
     * @param $migration_id
     * @return void
     * @throws Throwable
     */
    private static function untrackAppliedMigration($migration_id): void
    {
        DB::queryStmt("DELETE FROM _gdrcd_db_versions WHERE migration_id = :id", [
            'id' => $migration_id,
        ]);
    }

    /**
     * @fn isMigrationAlreadyApplied
     * @note Controlla a DB se una migrazione è già stata applicata
     * @param $migration_id
     * @return bool
     * @throws Throwable
     */
    private static function isMigrationAlreadyApplied($migration_id): bool
    {
        $result = DB::queryStmt("SELECT * FROM _gdrcd_db_versions WHERE migration_id = :id", [
            'id' => $migration_id,
        ]);
        return $result->getNumRows() > 0;
    }

    /**
     * @fn getAllAppliedMigrations
     * @note Trova tutte le migrazioni già applicate a DB
     * @return array Elenco di migrazioni applicate, ordinate per id. Chiavi disponibili: migration_id, applied_on
     * @throws Throwable
     */
    private static function getAllAppliedMigrations(): array
    {
        $result = DB::queryStmt("SELECT * FROM _gdrcd_db_versions ORDER BY migration_id", []);
        $all = [];
        while ( $row = DB::query($result, 'assoc') ) {
            $all[] = $row;
        }
        return $all;
    }

    /**
     * @fn getLastAppliedMigration
     * @note Trova l'ultima migrazione applicata dalla tabella su DB
     * @return DBQueryInterface
     * @throws Throwable
     */
    public static function getLastAppliedMigration(): DBQueryInterface
    {
        return DB::queryStmt("SELECT * FROM _gdrcd_db_versions ORDER BY migration_id DESC LIMIT 1", []);
    }

}
