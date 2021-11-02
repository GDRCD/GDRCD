<?php

class Permissions extends DbMigration
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
        DB::query("CREATE TABLE `permessi_custom` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `permission_name` varchar(255) NOT NULL,
    `description` text NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        DB::query("INSERT INTO `permessi_custom` (`permission_name`, `description`) VALUES
    ('LOG_CHAT', 'Permesso visualizzazione log chat'),
    ('LOG_EVENTI', 'Permesso visualizzazione log evento'),
    ('LOG_MESSAGGI', 'Permesso visualizzazione log messaggi'),
    ('MANAGE_ABILITY', 'Permesso gestione abilità'),
    ('MANAGE_ABILITY_EXTRA', 'Permesso gestione abilità dati extra'),
    ('MANAGE_ABILITY_REQUIREMENT', 'Permesso gestione requisiti abilità '),
    ('MANAGE_DB_MIGRATIONS', 'Permesso gestione versioni del database'),
    ('MANAGE_LOCATIONS', 'Permesso gestione luoghi'),
    ('MANAGE_MAPS', 'Permesso gestione mappe'),
    ('MANAGE_AMBIENT','Gestione ambientazione'),
    ('MANAGE_RULES','Gestione regolamento'),
    ('MANAGE_CONSTANTS', 'Permesso per l\'editing delle costanti'),
    ('MANAGE_RACES', 'Permesso per la gestione delle razze'),
    ('MANAGE_FORUMS', 'Permesso per la gestione delle bacheche'),
    ('MANAGE_GUILDS', 'Permesso per la gestione delle gilde'),
    ('MANAGE_PERMISSIONS', 'Permesso per la gestione dei permessi'),
    ('MANAGE_MANUTENTIONS', 'Permesso per la gestione della manutenzione del db'),
    ('MANAGE_OBJECTS', 'Permesso per la gestione degli oggetti'),
    ('MANAGE_REPORTS', 'Permesso per la gestione delle giocate segnalate'),
    ('MANAGE_OUTCOMES', 'Permesso per la gestione degli esiti in chat');");
    
        DB::query("CREATE TABLE `permessi_group` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `group_name` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `superuser` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        DB::query("INSERT INTO `permessi_group` (`id`, `group_name`, `description`, `superuser`) VALUES
    (1, 'MASTER', 'Gruppo permessi master', 0),
    (2, 'MODERATOR', 'Gruppo permessi moderatore', 0),
    (3, 'SUPERUSER', 'Gruppo Permessi superuser', 1),
    (4, 'USER', 'Permessi gruppo user', 0);");
    
        DB::query("CREATE TABLE `permessi_group_assignment` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `group_id` int(11) NOT NULL,
    `permission` int(11) NOT NULL,
    `assigned_by` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        
        DB::query("CREATE TABLE `permessi_group_personaggio` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `group_id` int(11) NOT NULL,
    `personaggio` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        DB::query("INSERT INTO `permessi_group_personaggio` (`id`, `group_id`, `personaggio`) VALUES
    (1, 3, 1);");
        
        DB::query("CREATE TABLE `permessi_personaggio` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `personaggio` varchar(255) NOT NULL,
    `permission` int(11) NOT NULL,
    `assigned_by` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }
    
    /**
     * @inheritDoc
     */
    public function down()
    {
        DB::query("DROP TABLE permessi_custom");
        DB::query("DROP TABLE permessi_group");
        DB::query("DROP TABLE permessi_group_assignment");
        DB::query("DROP TABLE permessi_group_personaggio");
        DB::query("DROP TABLE permessi_personaggio");
        
    }
}
