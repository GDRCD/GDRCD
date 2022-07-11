<?php

class GDRCDCalendar extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query("
            CREATE TABLE IF NOT EXISTS `eventi` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title`  INT(11) NULL DEFAULT NULL,
                `start` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `end` DATETIME NULL DEFAULT NULL DEFAULT CURRENT_TIMESTAMP,
                `titolo` VARCHAR(250) DEFAULT NULL,
                `descrizione` TEXT DEFAULT NULL,
                `colore`  INT(11) NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
        );

        gdrcd_query("
            CREATE TABLE IF NOT EXISTS `eventi_colori` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `backgroundColor` VARCHAR(50)DEFAULT NULL,
                `borderColor` VARCHAR(50) DEFAULT NULL,
                `textColor` VARCHAR(50) DEFAULT NULL,
                `colore` VARCHAR(50) DEFAULT NULL,
                PRIMARY KEY (`id`) 
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
        );

        gdrcd_query("
            CREATE TABLE IF NOT EXISTS `eventi_tipo` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(50) DEFAULT NULL,
                `permessi` VARCHAR(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
        );

        gdrcd_query("
            INSERT INTO `eventi_tipo` (`title`, `permessi`) 
            VALUES ('QUEST', 'GAMEMASTER'),
                   ('ROLE', NULL),
                   ('GRUPPO', NULL),
                   ('CORP', 'GUILDMODERATOR'),
                   ('ALTRO', NULL);"
        );

        gdrcd_query("
            INSERT INTO `eventi_colori` (`backgroundColor`, `borderColor`, `textColor`, `colore`) 
            VALUES ('purple', 'purple', 'white', 'Viola'),
                   ('lightblue', 'lightblue', 'black', 'Azzurro'),
                   ('green', 'green', 'white', 'Verde'),
                   ('pink', 'pink', 'black', 'Rosa'),
                   ('gold', 'gold', 'black', 'Giallo'),
                   ('grey', 'grey', 'black', 'Grigio'),
                   ('orange', 'orange', 'black', 'Arancione'),
                   ('red', 'red', 'white', 'Rosso'),
                   ('black', 'black', 'white', 'Nero'),
                   ('white', 'white', 'black', 'Bianco');
        ");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("DROP TABLE IF EXISTS `eventi`");

    }
}
