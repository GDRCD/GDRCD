<?php

/**
 * Implementa le funzioni di migrazione automatica dello schema del database da una versione all'altra
 */
class DbMigrationEngine
{
    const MIGRATIONS_FOLDER = __DIR__ . '/../db_versions/';
    
    /**
     * @fn updateDbSchema
     * @note Esegue le modifiche allo schema del DB portandolo alla versione specificata
     * @param string|null $migration_id versione a cui portare il DB, corrispondende all'identificativo di una
     * migrazione specifica. Se vuoto porta il DB all'ultima versione disponibile
     * @return int Il numero di migrazioni applicate
     * @throws ReflectionException
     */
    public function updateDbSchema($migration_id = null)
    {
        $migrations = $this->loadMigrationClasses();
        $this->createVersioningTable();//Per sicurezza cerchiamo di crearla sempre
        $lastApplied = $this->getLastAppliedMigration();
        
        if(empty($lastApplied)) {
            $this->performDbSetup($migrations, $lastApplied);
        }
    
        $directionUp = true;
        $migrationsToApply = $this->getMigrationsToApply($migrations, $lastApplied, $migration_id, $directionUp);
        
        $applied = 0;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);//Necessario per le transazioni
        $connection = gdrcd_connect();
        foreach ($migrationsToApply as $m) {
            try {
                $connection->begin_transaction();
                if ($directionUp) {
                    $m->up();
                    $this->trackAppliedMigration($m->getMigrationId());
                } else {
                    $m->down();
                    $this->untrackAppliedMigration($m->getMigrationId());
                }
                $connection->commit();
                $applied++;
            }
            catch (Exception $e){
                $connection->rollback();
                throw new Exception("Aggiornamento del database fallito: " . $e->getMessage(), 0, $e);
            }
        }
        
        return $applied;
    }
    
    /**
     * @fn dbNeedsInstallation
     * @note Indica se il database di GDRCD è stato installato o meno
     * @return bool
     */
    public function dbNeedsInstallation() {
        return $this->getTablesCountInDb() <= 1;//Controlliamo sempre 1 e non 0, per escludere la tabella delle  migration
    }
    
    /**
     * @fn dbNeedsUpdate
     * @note Indica se il database non si trova all'ultima versione disponibile e necessita quindi di applicare una
     * migration
     * @return bool
     * @throws ReflectionException
     */
    public function dbNeedsUpdate() {
        if($this->dbNeedsInstallation()){
            return true;
        }
    
        $migrations = $this->loadMigrationClasses();
        $this->createVersioningTable();//Per sicurezza cerchiamo di crearla sempre
        $lastApplied = $this->getLastAppliedMigration();
    
        return empty($lastApplied) or $migrations[count($migrations) -1]->getMigrationId() != (int)$lastApplied['migration_id'];
    }
    
    /**
     * @fn loadMigrationClasses
     * @note Carica in memoria le classi di migrazione
     * @return DbMigration[] Un array con un oggetto già istanziato per ogni migrazione, nel corretto ordine di
     * esecuzione temporale delle migrazioni
     * @throws ReflectionException
     */
    private function loadMigrationClasses(){
        /** @var DbMigration[] $migrations */
        $migrations = [];
        
        foreach(new DirectoryIterator(self::MIGRATIONS_FOLDER) as $fileInfo){
            if($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }
            $filename = basename($fileInfo->getRealPath(), '.php');
            $parts = explode("_", $filename);
            $className = $filename;
            if(count($parts) > 1){
                $className = $parts[1];
            }
            
            include_once $fileInfo->getRealPath();
            if(class_exists($className)){
                $reflected = new ReflectionClass($className);
                if($reflected->isSubclassOf("DbMigration")){
                    $migrations[] = $reflected->newInstance();
                }
            }
        }
        
        //Ordinamento per id, da l'ordine di esecuzione
        usort($migrations, function($a, $b){
            $aId = $a->getMigrationId();
            $bId = $b->getMigrationId();
            
            if($aId == $bId){
                return 0;
            }
            return $aId < $bId ? -1 : 1;
        });
        
        return $migrations;
    }
    
    /**
     * @fn performDbSetup
     * @note Decide se eseguire il setup del sistema o se è già stato eseguito e quindi va solo aggiunta la tabella
     * delle versioni saltando il setup
     * @param DbMigration[] $migrations
     * @throws Exception
     */
    private function performDbSetup($migrations, &$lastApplied){
        $tablesCount = $this->getTablesCountInDb();
        
        if($tablesCount > 1){//Precedente installazione priva di Db Migrations (pre 5.6)?
            //Eseguiamo solo le migration dalla 5.6 in poi, assumendo il setup della 5.5.1 già fatto
            $migrations[0]->up();
            $this->trackAppliedMigration($migrations[0]->getMigrationId());
            $lastApplied = $this->getLastAppliedMigration();
        }
    }
    
    /**
     * @fn getMigrationsToApply
     * @note Identifica quali migrazioni devono essere applicate in base all'ultima applicata e alla migrazione
     * target richiesta
     * @param DbMigration[] $migrations
     * @param array $lastApplied
     * @param int $targetMigrationId
     * @param bool $directionUp
     * @return DbMigration[]
     * @throws Exception
     */
    private function getMigrationsToApply($migrations, $lastApplied, $targetMigrationId, &$directionUp) {
        $directionUp = true;
        if(empty($targetMigrationId)) {//Auto migration
            $firstToApply = 0;
            foreach ($migrations as $k => $m) {
                if ((int)$m->getMigrationId() > (int)$lastApplied['migration_id']) {
                    $firstToApply = $k;
                    break;
                }
            }
            $migrationsToApply = array_slice($migrations, $firstToApply);
        }
        else{//migration verso una versione specifica
            $lastAppliedIdx = 0;
            $targetIdx = 0;
            foreach ($migrations as $k => $m){
                if((int)$m->getMigrationId() == (int)$lastApplied['migration_id']){
                    $lastAppliedIdx = $k;
                }
                if((int)$m->getMigrationId() == (int)$targetMigrationId){
                    $targetIdx = $k;
                }
                if($targetIdx != 0 && $lastAppliedIdx != 0){
                    break;
                }
            }
            if($lastAppliedIdx > $targetIdx){
                $directionUp = false;
                $migrationsToApply = array_slice($migrations, $targetIdx, ($lastAppliedIdx - $targetIdx));
            }
            else{
                $migrationsToApply = array_slice($migrations, $lastAppliedIdx, ($targetIdx - $lastAppliedIdx));
            }
        }
        
        return $migrationsToApply;
    }
    
    /**
     * @fn getTablesCountInDb
     * @note Trova il numero di tabelle presenti nel DB
     * @return int
     */
    private function getTablesCountInDb() {
        global $PARAMETERS;
        
        $count = gdrcd_query("SELECT COUNT(*) AS number FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '"
                                    .$PARAMETERS['database']['database_name']."'");;
        
        return $count;
    }
    
    /**
     * @fn createVersioningTable
     * @note Crea sul DB la tabella per il tracciamento delle versioni del DB
     */
    private function createVersioningTable()
    {
        gdrcd_query("
        create table if not exists _gdrcd_db_versions
(
	migration_id varchar(250) null,
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
     */
    private function trackAppliedMigration($migration_id)
    {
        gdrcd_query("INSERT INTO _gdrcd_db_versions (migration_id,applied_on) VALUE ('" . gdrcd_filter_in($migration_id) . "', NOW())");
    }
    
    /**
     * @fn untrackAppliedMigration
     * @note Toglie dalla tabella delle migrazioni effettuate la migrazione specificata in $migration_id
     * @param $migration_id
     */
    private function untrackAppliedMigration($migration_id)
    {
        gdrcd_query("DELETE * FROM _gdrcd_db_versions WHERE migration_id = '" . gdrcd_filter_in($migration_id) ."'");
    }
    
    /**
     * @fn isMigrationAlreadyApplied
     * @note controlla a DB se una migrazione è già stata applicata
     * @param $migration_id
     * @return bool
     */
    private function isMigrationAlreadyApplied($migration_id)
    {
        $result = gdrcd_query("SELECT COUNT() AS N FROM _gdrcd_db_versions WHERE migration_id = '" . gdrcd_filter_in
                     ($migration_id) . "'");
        return $result['N'] > 0;
    }
    
    /**
     * @fn getAllAppliedMigrations
     * @note Trova tutte le migrazioni già applicate a DB
     * @return array elenco di migrazioni applicate, ordinate per id. Chiavi disponibili: migration_id, applied_on
     */
    private function getAllAppliedMigrations()
    {
        $result = gdrcd_query("SELECT * FROM _gdrcd_db_versions ORDER BY migration_id", 'result');
        $all = [];
        while($row = gdrcd_query($result, 'assoc')){
            $all[] = $row;
        }
        
        return $all;
    }
    
    /**
     * @fn getLastAppliedMigration
     * @note Trova l'ultima migrazione applicata dalla tabella su DB
     * @return null|array
     */
    private function getLastAppliedMigration()
    {
        return gdrcd_query("SELECT * FROM _gdrcd_db_versions ORDER BY migration_id DESC LIMIT 1");
    }
    
}
