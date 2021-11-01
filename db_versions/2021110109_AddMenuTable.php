
<?php

class AddMenuTable extends DbMigration
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
        gdrcd_query("CREATE TABLE `menu` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `menu_name` varchar(255) NOT NULL,
    `section` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `page` varchar(255) NOT NULL,
    `permission` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        
        gdrcd_query("INSERT INTO `menu` (`menu_name`, `section`, `name`, `page`, `permission`) VALUES
  ('Gestione', 'Log', 'Log Chat', 'log_chat', 'LOG_CHAT'),
  ('Gestione', 'Log', 'Log Eventi', 'log_eventi', 'LOG_EVENTI'),
  ('Gestione', 'Log', 'Log Messaggi', 'log_messaggi', 'LOG_MESSAGGI'),
  ('Gestione', 'Abilità', 'Gestione Abilità', 'gestione_abilita', 'MANAGE_ABILITY'),
  ('Gestione', 'Abilità', 'Dati Extra Abilità', 'gestione_abilita_extra', 'MANAGE_ABILITY_EXTRA'),
  ('Gestione', 'Abilità', 'Requisiti abilità', 'gestione_abilita_requisiti', 'MANAGE_ABILITY_REQUIREMENT'),
  ('Gestione', 'Locations', 'Gestione Luoghi', 'gestione_luoghi', 'MANAGE_LOCATIONS'),
  ('Gestione', 'Locations', 'Gestione Mappe', 'gestione_mappe', 'MANAGE_MAPS'),
  ('Gestione', 'Documentazioni', 'Gestione Ambientazione', 'gestione_ambientazione', 'MANAGE_AMBIENT'),
  ('Gestione', 'Documentazioni', 'Gestione Regolamento', 'gestione_regolamento', 'MANAGE_RULES'),
  ('Gestione', 'Razze', 'Gestione Razze', 'gestione_razze', 'MANAGE_RACES'),
  ('Gestione', 'Bacheche', 'Gestione Bacheche', 'gestione_bacheche', 'MANAGE_FORUMS'),
  ('Gestione', 'Gilde', 'Gestione Gilde e Ruoli', 'gestione_gilde', 'MANAGE_GUILDS'),
  ('Gestione', 'Gestione', 'Gestione Costanti', 'gestione_costanti', 'MANAGE_CONSTANTS'),
  ('Gestione', 'Gestione', 'Gestione Versioni Database', 'gestione_db_migrations', 'MANAGE_DB_MIGRATIONS'),
  ('Gestione', 'Permessi', 'Gestione Permessi', 'gestione_permessi', 'MANAGE_PERMISSIONS'),
  ('Gestione', 'Gestione', 'Manutenzione', 'gestione_manutenzione', 'MANAGE_MANUTENTIONS'),
  ('Gestione', 'Oggetti', 'Gestione Oggetti', 'gestione_oggetti', 'MANAGE_OBJECTS'),
  ('Gestione', 'Chat', 'Giocate Segnalate', 'gestione_segnalazioni', 'MANAGE_REPORTS'),
  ('Gestione', 'Chat', 'Esiti in chat', 'gestione_esiti', 'MANAGE_OUTCOMES');");
    }
    
    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("DROP TABLE menu");
    }
}
