<?php

/**
 * Classe base per implementare le migration del DB
 */
abstract class DbMigration
{
    /**
     * @var int id della migrazione. è consigliato utilizzare un interno composto dalla data di scrittura della
     * migrazione nel formato YYYYMMDDHH (esempio: 2021103018)
     */
    protected $migration_id = 0;
    
    public function __construct()
    {
        if($this->migration_id <= 0){
            $myReflection = new ReflectionClass($this);
            $definitionFile = $myReflection->getFileName();
            $filen = basename($definitionFile, '.php');
            $parts = explode('_', $filen);
            if(count($parts) > 1) {//Salviamoci in caso di nomenclatura errata
                $this->migration_id = (int)$parts[0];//l'id migrazione deve essere il primo componente del nome file
            }
        }
    }
    
    public function getMigrationId(){
        if($this->migration_id <= 0){
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
