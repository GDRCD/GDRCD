<?php

class GDRCD57_Refactor_Log_Table extends DbMigration
{
    
    public function up()
    {
        gdrcd_query("
            CREATE TABLE `log_new` (
                `id` BINARY(16) NOT NULL,
                `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `descrizione` VARCHAR(255) NOT NULL DEFAULT '',
                `livello_log` VARCHAR(20) NOT NULL DEFAULT 'info',
                `contesto` TEXT NULL,
                `id_personaggio` INT(10) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `id_personaggio` (`id_personaggio`)
            ) EENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
 
        gdrcd_query("
            INSERT INTO `log_new` (`id`, `data`, `descrizione`, `livello_log`, `contesto`, `id_personaggio`)
            SELECT
                UUID(),
                `data_evento`,
                `descrizione_evento`,
                'info',
                CONCAT(
                    '{',
                    '\"nome_interessato\":\"', REPLACE(IFNULL(`nome_interessato`, ''), '\"', '\\\\\"'), '\",',
                    '\"autore\":\"', REPLACE(IFNULL(`autore`, ''), '\"', '\\\\\"'), '\",',
                    '\"codice_evento\":\"', REPLACE(IFNULL(`codice_evento`, ''), '\"', '\\\\\"'), '\"',
                    '}'),
                id_personaggio
            FROM `log`
        ");
       

        gdrcd_query("RENAME TABLE `log` TO `log_old`, `log_new` TO `log`");
        gdrcd_query("DROP TABLE `log_old`");
    }

    public function down()
    {
        gdrcd_query("
            CREATE TABLE `log_old` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `nome_interessato` VARCHAR(20) NOT NULL DEFAULT '',
                `autore` VARCHAR(60) NOT NULL DEFAULT '',
                `data_evento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `codice_evento` CHAR(20) NOT NULL DEFAULT '',
                `descrizione_evento` CHAR(100) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");

        gdrcd_query("
            INSERT INTO `log_old` (
                `nome_interessato`,
                `autore`,
                `data_evento`,
                `codice_evento`,
                `descrizione_evento`
            )
            SELECT
                '',
                '',
                `data`,
                '',
                LEFT(`descrizione`, 100)
            FROM `log`
        ");

        gdrcd_query("RENAME TABLE `log` TO `log_new`, `log_old` TO `log`");
        gdrcd_query("DROP TABLE `log_new`");
    }
} 
