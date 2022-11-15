<?php

/**
 * Classe base per implementare le migration del DB
 * Ogni classe che estende questa classe dovrebbe essere posizionata nella cartella /db_versions
 * Ogni classe figlia deve seguire la naming convention per il file in cui è definita: YYYYMMDDHH_NomeClasse.php
 */
abstract class DbMigration
{
    /**
     * @var int Id della migrazione. È consigliato utilizzare un interno composto dalla data di scrittura della
     * Migrazione nel formato YYYYMMDDHH (esempio: 2021103018)
     */
    protected int $migration_id = 0;

    public function __construct()
    {
        if ( $this->migration_id <= 0 ) {
            $myReflection = new ReflectionClass($this);
            $definitionFile = $myReflection->getFileName();
            $file_name = basename($definitionFile, '.php');
            $parts = explode('_', $file_name);
            if ( count($parts) > 1 ) {//Salviamoci in caso di nomenclatura errata
                $this->migration_id = (int)$parts[0];//l'id migrazione deve essere il primo componente del nome file
            }
        }
    }

    /**
     * @throws Exception
     */
    public function getMigrationId(): int
    {
        if ( $this->migration_id <= 0 ) {
            throw new Exception("Migration ID non configurato per " . get_called_class());
        }

        return $this->migration_id;
    }

    /**
     * Implementazione delle modifiche che questa migration deve eseguire sul DB quando viene applicata
     */
    abstract public function up();

    /**
     * Implementazione delle modifiche che questa migration deve eseguire sul DB quando viene rimossa
     */
    abstract public function down();
}
