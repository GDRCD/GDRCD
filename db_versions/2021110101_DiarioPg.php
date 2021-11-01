<?php

/**
 * Aggiunta della funzione di Diario PG
 */
class DiarioPg extends DbMigration
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
        gdrcd_query("CREATE TABLE IF NOT EXISTS `diario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `personaggio` varchar(20) DEFAULT NULL,
  `data` date NOT NULL,
  `data_inserimento` datetime NOT NULL,
  `data_modifica` datetime DEFAULT NULL,
  `visibile` char(5) NOT NULL,
  `titolo` varchar(50) NOT NULL DEFAULT '',
  `testo` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }
    
    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("DROP TABLE IF EXISTS `diario`");
    }
}
