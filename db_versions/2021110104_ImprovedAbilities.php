<?php

class ImprovedAbilities extends DbMigration
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function up()
    {
        DB::query("CREATE TABLE IF NOT EXISTS `abilita_extra` (
  `id` int NOT NULL AUTO_INCREMENT,
  `abilita` int NOT NULL,
  `grado` int NOT NULL,
  `descrizione` text NOT NULL,
  `costo` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
        
        DB::query("CREATE TABLE IF NOT EXISTS `abilita_requisiti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `abilita` int NOT NULL,
  `grado` int NOT NULL,
  `tipo` int NOT NULL,
  `id_riferimento` int NOT NULL,
  `liv_riferimento` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    }
    
    /**
     * @inheritDoc
     */
    public function down()
    {
        DB::query("DROP TABLE abilita_extra");
        
        DB::query("DROP TABLE abilita_requisiti");
    }
}