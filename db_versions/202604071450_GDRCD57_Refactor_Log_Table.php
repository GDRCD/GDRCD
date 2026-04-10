<?php

class GDRCD57_Refactor_Log_Table extends DbMigration
{

    public function up()
    {
        gdrcd_query("DROP TABLE `log`");
        gdrcd_query("
            CREATE TABLE `logs` (
                `id` BINARY(16) NOT NULL,
                `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `descrizione` VARCHAR(255) NOT NULL DEFAULT '',
                `livello_log` VARCHAR(20) NOT NULL DEFAULT 'info',
                `contesto` TEXT NULL,
                `id_personaggio` INT(10) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `id_personaggio` (`id_personaggio`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
    }

    public function down()
    {
        gdrcd_query("DROP TABLE `logs`");
        gdrcd_query("
            CREATE TABLE `log` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `nome_interessato` VARCHAR(20) NOT NULL DEFAULT '',
                `autore` VARCHAR(60) NOT NULL DEFAULT '',
                `data_evento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `codice_evento` CHAR(20) NOT NULL DEFAULT '',
                `descrizione_evento` CHAR(100) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
    }
}
