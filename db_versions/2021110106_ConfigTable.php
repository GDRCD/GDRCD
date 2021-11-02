<?php

class ConfigTable extends DbMigration
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
        DB::query("CREATE TABLE IF NOT EXISTS `config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `const_name` varchar(255) NOT NULL,
  `val` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `section` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(255) DEFAULT 'String',
  `editable` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        
        DB::query("INSERT INTO `config` (`const_name`,`val`,`section`,`label`,`description`,`type`,`editable`) VALUES
    ('ABI_PUBLIC',1,'Abilita','Abilità pubbliche','Le abilità sono pubbliche?','bool',1),
    ('ABI_LEVEL_CAP',5,'Abilita','Level cap Abilità','Livello massimo abilità','int',1),
    ('DEFAULT_PX_PER_LVL',10,'Abilita','Costo default Abilità','Moltiplicatore costo abilità, se non specificato','int',1),
    ('ABI_REQUIREMENT',1,'Abilita','Requisiti Abilità','Abilitare requisiti abilità?','bool',1),
    ('REQUISITO_ABI',1,'Abilita','','Requisito di tipo abilità','int',0),
    ('REQUISITO_STAT',2,'Abilita','','Requisito di tipo statistica','int',0),
    ('ABI_EXTRA',1,'Abilita','Dati abilita extra','Abilitare i dati abilita extra?','int',0),
    ('CHAT_TIME',2,'Chat','Ore storico chat','Ore di caricamento nella chat','int',1),
    ('CHAT_EXP',1,'Chat Exp','Exp in chat','Esperienza in chat, attiva?','bool',1),
    ('CHAT_PVT_EXP',0,'Chat Exp','Exp in chat pvt','Esperienza in chat pvt, attiva?','bool',1),
    ('CHAT_EXP_MASTER',1,'Chat Exp','Exp master','Esperienza per ogni azione master','int',1),
    ('CHAT_EXP_AZIONE',1,'Chat Exp','Exp azione','Esperienza per ogni azione normale','int',1),
    ('CHAT_EXP_MIN',500,'Chat Exp','Minimo caratteri','Minimo di caratteri per esperienza','int',1),
    ('CHAT_ICONE',1,'Chat','Icone in chat','Icone attive in chat?','bool',1),
    ('CHAT_AVATAR',1,'Chat','Avatar in chat','Avatar attivo in chat?','bool',1),
    ('CHAT_NOTIFY',1,'Chat','Notifica in chat','Notifiche in chat per nuove azioni?','bool',1),
    ('CHAT_DICE',1,'Chat Dadi','Dadi in chat','Dadi attivi in chat?','bool',1),
    ('CHAT_DICE_BASE',20,'Chat Dadi','Tipo dado in chat','Numero massimo dado in chat','int',1),
    ('CHAT_SKILL_BUYED',0,'Chat Dadi','Solo abilità acquistate','Solo skill acquistate nel lancio in chat','bool',1),
    ('CHAT_EQUIP_BONUS',0,'Chat Dadi','Bonus equipaggimento','Bonus equipaggiamento ai dadi in chat?','bool',1),
    ('CHAT_EQUIP_EQUIPPED',1,'Chat Dadi','Solo equipaggiamento','Solo oggetti equipaggiati in chat?','bool',1),
    ('CHAT_SAVE',1,'Chat Salvataggio','Salva chat','Salva chat attivo?','bool',1),
    ('CHAT_PVT_SAVE',1,'Chat Salvataggio','Salva chat pvt','Salva chat attivo in pvt?','bool',1),
    ('CHAT_SAVE_LINK',1,'Chat Salvataggio','Salva chat in link','Salva chat in modalità link?','bool',1),
    ('CHAT_SAVE_DOWNLOAD',1,'Chat Salvataggio','Salva chat download','Salva chat con download?','bool',1);");
    }
    
    /**
     * @inheritDoc
     */
    public function down()
    {
        DB::query("DROP TABLE IF EXISTS `config`");
    }
}
