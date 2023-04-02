<?php

/**
 * Snapshot full della struttura del DB per GDRCD 6.x
 */
class GDRCD6 extends DbMigration
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function up()
    {
        DB::query("
            INSERT INTO `abilita` (`nome`, `statistica`, `descrizione`, `razza`) VALUES
                ('Resistenza', 1, 'Il personaggio è in grado di sopportare il dolore ed il disagio e sopporta minime dosi di agenti tossici nel proprio organismo. ', -1),
                ('Sopravvivenza', 1, 'Il personaggio è in grado di procurarsi cibo e riparo all''aperto, con mezzi minimi.', -1);
        ");


        DB::query("
            INSERT INTO calendario_tipi(`nome`,`colore_bg`,`colore_testo`,`permessi`,`pubblico`)  VALUES  
                    ('Personale', '#ff0000', '#ffffff', NULL,'0'),
                    ('Quest', '#00ff00', '#000000', 'CALENDAR_ADD_QUEST','1'),
                    ('Ambient', '#0000ff', '#ffffff', 'CALENDAR_ADD_AMBIENT','1');
            ");

        // Argon2 potrebbe non essere sempre disponibile. Nel qual caso usiamo Blowfish come default
        $defaultPasswordCrypter = defined('PASSWORD_ARGON2ID') ?
            'CrypterPasswordArgon2,argon2id'
            : 'CrypterPaswordBlowfish,2y';

        DB::query("
            INSERT INTO `config` (`const_name`,`val`,`section`,`label`,`description`,`type`,`editable`,`options`) VALUES
                ('DEVELOPING',1,'Engine','Gdr in sviluppo?','','bool',1,NULL),
                ('STANDARD_ENGINE','default','Engine','Engine utilizzato. Non modificare se non necessario.','','string',1,NULL),
                ('TEMPLATE_ENGINE','TemplateSmarty','Template','Template utilizzato. Non modificare se non necessario.','','select',1,'Template'),
                ('SECURITY_PASSWORD_CRYPTER','$defaultPasswordCrypter','Sicurezza','Algoritmo di Hashing delle Password. Non modificare se non necessario.','','select',1,'PasswordHash'),
                ('INLINE_CRONJOB',1,'Engine','Cronjob inline','Cronjob inline nell header?','bool',1,NULL),
                ('LOGIN_BACK_LOCATION',0,'Login','Login in locazione','Login in vecchia location?','bool',1,NULL),
                ('ABI_LEVEL_CAP',5,'Abilita','Level cap Abilità','Livello massimo abilità','int',1,NULL),
                ('DEFAULT_PX_PER_LVL',10,'Abilita','Costo default Abilità','Moltiplicatore costo abilità, se non specificato','int',1,NULL),
                ('ABI_REQUIREMENT',1,'Abilita','Requisiti Abilità','Abilitare requisiti abilità?','bool',1,NULL),
                ('REQUISITO_ABI',1,'Abilita','','Requisito di tipo abilità','int',0,NULL),
                ('REQUISITO_STAT',2,'Abilita','','Requisito di tipo statistica','int',0,NULL),
                ('ABI_EXTRA',1,'Abilita','Dati abilita extra','Abilitare i dati abilita extra?','int',0,NULL),
                ('CHAT_TIME',2,'Chat','Ore storico chat','Ore di caricamento nella chat','int',1,NULL),
                ('CHAT_EXP',1,'Chat Exp','Exp in chat','Esperienza in chat, attiva?','bool',1,NULL),
                ('CHAT_PVT_EXP',0,'Chat Exp','Exp in chat pvt','Esperienza in chat pvt, attiva?','bool',1,NULL),
                ('CHAT_EXP_MASTER',1,'Chat Exp','Exp master','Esperienza per ogni azione master','int',1,NULL),
                ('CHAT_EXP_AZIONE',1,'Chat Exp','Exp azione','Esperienza per ogni azione normale','int',1,NULL),
                ('CHAT_EXP_MIN',500,'Chat Exp','Minimo caratteri','Minimo di caratteri per esperienza','int',1,NULL),
                ('CHAT_ICONE',1,'Chat','Icone in chat','Icone attive in chat?','bool',1,NULL),
                ('CHAT_AVATAR',1,'Chat','Avatar in chat','Avatar attivo in chat?','bool',1,NULL),
                ('CHAT_NOTIFY',1,'Chat','Notifica in chat','Notifiche in chat per nuove azioni?','bool',1,NULL),
                ('CHAT_DICE',1,'Chat Dadi','Dadi in chat','Dadi attivi in chat?','bool',1,NULL),
                ('CHAT_DICE_BASE',20,'Chat Dadi','Tipo dado in chat','Numero massimo dado in chat','int',1,NULL),
                ('CHAT_SKILL_BUYED',0,'Chat Dadi','Solo abilità acquistate','Solo skill acquistate nel lancio in chat','bool',1,NULL),
                ('CHAT_EQUIP_BONUS',0,'Chat Dadi','Bonus equipaggiamento','Bonus equipaggiamento ai dadi in chat?','bool',1,NULL),
                ('CHAT_EQUIP_EQUIPPED',1,'Chat Dadi','Solo equipaggiamento','Solo oggetti equipaggiati in chat?','bool',1,NULL),
                ('CHAT_SAVE',1,'Chat Salvataggio','Salva chat','Salva chat attivo?','bool',1,NULL),
                ('CHAT_PVT_SAVE',1,'Chat Salvataggio','Salva chat pvt','Salva chat attivo in pvt?','bool',1,NULL),
                ('CHAT_SAVE_LINK',1,'Chat Salvataggio','Salva chat in link','Salva chat in modalità link?','bool',1,NULL),
                ('CHAT_SAVE_DOWNLOAD',1,'Chat Salvataggio','Salva chat download','Salva chat con download?','bool',1,NULL),
                ('WEATHER_MOON',1,'Meteo','Luna','Abilita o disabilita le fasi lunari','bool',1,NULL),
                ('WEATHER_SEASON',1,'Meteo','Stagionale','Meteo stagioni o meteo Web api','bool',1,NULL),
                ('WEATHER_WIND',1,'Meteo','Vento','Abilita o disabilita il vento','bool',1,NULL),
                ('WEATHER_WEBAPI',0,'Meteo','Web Api','Abilita Web Api di OpenWeather','bool',1,NULL),
                ('WEATHER_WEBAPIKEY','fake_key','Meteo','Web Api Key','Web Api di OpenWeather','String',1,NULL),
                ('WEATHER_WEBAPI_CITY','Rome','Meteo','Web Api Citta','Città di cui avere il meteo','String',1,NULL),
                ('WEATHER_WEBAPI_ICON',0,'Meteo','Web Api icone','Icone di default o personalizzate','bool',1,NULL),
                ('WEATHER_LAST_DATE',0,'Meteo','Meteo Impostazioni','Data ultimo aggiornamento meteo','String',0,NULL),
                ('WEATHER_WEBAPI_FORMAT','png','Meteo','Icone estensione','Estensione delle icone','select',1,'ImageExtensions'),
                ('WEATHER_LAST_CONDITION',0,'Meteo','Meteo Impostazioni','Ultime condizioni meteo registrato','String',0,NULL),
                ('WEATHER_LAST_TEMP',0,'Meteo','Meteo Impostazioni','Ultima temperatura registrata','String',0,NULL),
                ('WEATHER_LAST_IMG',0,'Meteo','Meteo Impostazioni','Ultima immagine registrata','String',0,NULL),
                ('WEATHER_LAST_WIND',0,'Meteo','Meteo Impostazioni','Ultimo vento registrato','String',0,NULL),
                ('WEATHER_UPDATE',4,'Meteo','Tempo di aggiornamento','Dopo quante ore si prevede il cambio meteo con le stagioni','int',1,NULL),
                ('ESITI_ENABLE',1,'Esiti','Attiva esiti','Abilitare la funzione esiti?','bool',1,NULL),
                ('ESITI_CHAT',1,'Esiti','Attiva esiti in chat','Abilitare la funzione di lancio degli esiti in chat?','bool',1,NULL),
                ('ESITI_TIRI',1,'Esiti','Lancio di dadi negli esiti','Abilitare la possibilità di lanciare dadi all''interno del pannello esiti?','bool',1,NULL),
                ('ESITI_FROM_PLAYER',1,'Esiti','Esiti dai player','Abilitare richiesta esiti da parte dei player?','bool',1,NULL),
                ('DIARY_ENABLED',1,'Diario','Diari attivi','Abilitare diari in scheda pg?','bool',1,NULL),
                ('QUEST_ENABLED',1,'Quest','Attivazione Quest migliorate','Gestione quest migliorata, attiva?','bool',1,NULL),
                ('QUEST_VIEW',2,'Quest','Permessi visual quest','Permesso minimo per visualizzazione delle quest','permission',1,NULL),
                ('QUEST_SUPER_PERMISSION',3,'Quest','Permessi speciali','Permesso minimo per modificare qualsiasi parte del pacchetto','int',1,NULL),
                ('QUEST_NOTIFY',0,'Quest','Notifiche quest','Definisce la possibilità di inviare messaggi automatici di avviso agli utenti che partecipano ad una quest','bool',1,NULL),
                ('TRAME_ENABLED',1,'Trame','Attivazione trame','Sistema trame attivo?','bool',1,NULL),
                ('QUEST_RESULTS_FOR_PAGE',15,'Quest','Risultati per pagina','Numero risultati per pagina nella gestione delle quest.','int',1,NULL),
                ('ONLINE_STATUS_ENABLED',1,'Online Status','Stato online avanzato','Stato online avanzato,attivo?','bool',1,NULL),
                ('ONLINE_STATUS_LOGIN_REFRESH',1,'Online Status','Reselect al login','Login oscura ultima scelta dai presenti?','bool',1,NULL),
                ('SCHEDA_OBJECTS_PUBLIC',1,'Scheda Oggetti','Scheda Oggetti pubblica','Pagina inventario pubblica?','bool',1,NULL),
                ('SCHEDA_STATS_PUBLIC',1,'Scheda Oggetti','Scheda Statistiche pubblica','Pagina statistica pubblica?','bool',1,NULL),
                ('SCHEDA_ABI_PUBLIC',1,'Scheda Abilita','Scheda Abilita pubblica','Pagina abilita pubblica?','bool',1,NULL),
                ('GROUPS_ACTIVE',1,'Gruppi','Gruppi attivi','Gruppi attivi?','bool',1,NULL),
                ('GROUPS_MAX_ROLES',3,'Gruppi','Massimo ruoli','Numero massimo di ruoli','int',1,NULL),
                ('GROUPS_PAY_FROM_MONEY',1,'Gruppi','Stipendi da possedimento gruppi','Stipendi da possedimento gruppi','bool',1,NULL),
                ('GROUPS_FOUNDS',1,'Gruppi','Fondi Gruppo','Introiti automatizzati per gruppi','bool',1,NULL),
                ('GROUPS_EXTRA_EARNS',1,'Gruppi','Stipendi extra Gruppo','Introiti extra per personaggi da gruppi','bool',1,NULL),
                ('GROUPS_ICON_CHAT',1,'Gruppi','Icone gruppo in chat','Icone gruppi in chat','bool',1,NULL),
                ('GROUPS_ICON_PRESENT',1,'Gruppi','Icone gruppo nei presenti','Icone gruppi in chat','bool',1,NULL),
                ('GROUPS_STORAGE',1,'Gruppi','Magazzini gruppi attivi','Magazzini gruppi attivi','bool',1,NULL),
                ('WORKS_ACTIVE',1,'Gruppi','Lavori attivi','Lavori attivi?','bool',1,NULL),
                ('WORKS_DIMISSIONS_DAYS',1,'Gruppi','Giorni per dimissioni','Giorni per dimissioni','bool',1,NULL),
                ('WORKS_MAX',3,'Gruppi','Massimo lavori liberi','Numero massimo di lavori liberi','int',1,NULL),
                ('CONTACTS_ENABLED', 1, 'Contatti','Abilita/Disabilita la sezione contatti',  'Abilita/disabilita i contatti', 'bool', 1,NULL),
                ('CONTACT_PUBLIC', 1, 'Contatti','Abilita/Disabilita la visualizzazione pubblica dei contatti',  'Abilita/Disabilita la visualizzazione pubblica dei contatti', 'bool', 1,NULL),
                ('CONTACT_SECRETS', 1, 'Contatti','Abilita/Disabilita la scelta di nascondere le note',  'Abilita/Disabilita la scelta di nascondere le note', 'bool', 1,NULL),
                ('CONTACT_CATEGORIES', 1, 'Contatti','Abilita/Disabilita le categorie',  'Abilita/Disabilita le categorie', 'bool', 1,NULL),
                ('CONTACT_CATEGORIES_PUBLIC', 1, 'Contatti','Se abilitato, tutti vedono le categorie',  'Se abilitato, tutti vedono le categorie', 'bool', 1,NULL),
                ('CONTACT_CATEGORIES_STAFF_ONLY', 0, 'Contatti', 'Se abilitato, solo lo staff può assegnare le categorie di contatto', 'Se abilitato, solo lo staff può assegnare le categorie di contatto', 'bool', 1,NULL),
                ('RACES_ACTIVE', 1, 'Razze', 'Se abilitato, le razze sono abilitate nel sito', 'Se abilitato, le razze sono abilitate nel sito', 'bool', 1,NULL),
                ('REGISTRAZIONI_ENABLED',1,'Registrazioni','Registrazioni attive','Registrazioni attive?','bool',1,NULL),
                ('FORUM_ACTIVE',1,'Forum','Forum attivo','Forum attivo?','bool',1,NULL),
                ('FORUM_POSTS_FOR_PAGE',10,'Forum','Posts per pagina','Numero posts per pagina','int',1,NULL),
                ('FORUM_COMMENTS_FOR_PAGE',10,'Forum','Commenti per pagina','Numero commenti post per pagina','int',1,NULL),
                ('FORUM_POST_HISTORY',1,'Forum','Storico delle modifica ad un post o commento','Storico delle modifica ad un posto commento','bool',1,NULL),
                ('NEWS_ENABLED',1,'News','News attive','News attive?','bool',1,NULL),
                ('CONVERSATIONS_ENABLED',1,'Conversazioni','Conversazioni attive','Conversazioni attive?','bool',1,NULL),
                ('CALENDAR_ENABLED',1,'Calendario','Calendario attivo','Calendario attivo?','bool',1,NULL),
                ('CALENDAR_ONLY_FUTURE_SELECTABLE',1,'Calendario','Solo date future selezionabili per nuovi eventi','Solo date future selezionabili per nuovi eventi?','bool',1,NULL)
        ;");

        DB::query("
            INSERT INTO config_options(`section`,`label`,`value`) VALUES 
                ('Template','Smarty','TemplateSmarty'),
                ('PasswordHash','Argon2','CrypterPasswordArgon2,argon2id'),
                ('PasswordHash','Blowfish','CrypterPasswordBlowfish,2y'),
                ('PasswordHash','SHA-512','CrypterPasswordSha512,sha512'),
                ('PasswordHash','SHA-256','CrypterPasswordSha256,sha256'),
                ('ImageExtensions','.png','png'),
                ('ImageExtensions','.jpg','jpg'),
                ('ImageExtensions','.gif','gif');
        ");

        DB::query("
            INSERT INTO `chat_opzioni` (`nome`,`titolo`,`descrizione`,`tipo`,`creato_da`) VALUES
                ('action_size','Dimensione Azione','','int','1'),
                ('action_color_talk','Colore Parlato Azione','','string','1'),
                ('action_color_descr','Colore Descrizione Azione','','string','1'),
                ('master_size','Dimensione MasterScreen','','int','1'),
                ('master_color_talk','Colore Parlato MasterScreen','','string','1'),
                ('master_color_descr','Colore Descrizione MasterScreen','','string','1'),
                ('sussurro_size','Dimensione Sussurro','','int','1'),
                ('sussurro_color','Colore Sussurro','','string','1'),
                ('sussurro_globale_size','Dimensione Sussurro Globale','','int','1'),
                ('sussurro_globale_color','Colore Sussurro Globale','','string','1'),
                ('png_size','Dimensione azione PNG','','int','1'),
                ('png_color_talk','Colore Parlato PNG','','string','1'),
                ('png_color_descr','Colore Descrizione PNG','','string','1'),
                ('mod_size','Dimensione Moderazione','','int','1'),
                ('mod_color','Colore Moderazione','','string','1'),
                ('other_size','Dimensione Dadi/Oggetti/Altro','','string','1'),
                ('other_color','Colore Dadi/Oggetti/Altro','','string','1');"
        );

        DB::query("
            INSERT INTO `cronjob` (`name`,`last_exec`,`in_exec`,`interval`,`interval_type`,`class`,`function`) VALUES
                ('meteo_update',NULL,false,'60','minutes','Meteo','generateGlobalWeather'),
                ('fondi_assign',NULL,false,'1','days','GruppiFondi','assignFounds'),
                ('stipendi_assign',NULL,false,'1','days','Gruppi','cronSalaries');"
        );

        DB::query("
            INSERT INTO disponibilita(`nome`,`immagine`) VALUES
            ('Disponibile','availability/disponibile.png'),
            ('Non Disponibile','availability/non_disponibile.png'),
            ('Occupato','availability/in_lavorazione.png');"
        );

        DB::query("
            INSERT INTO `forum` (`tipo`, `nome`) VALUES
                (1, 'Notizie in gioco'),
                (2, 'Ordini alla Guardia'),
                (3, 'Umani'),
                (4, 'Resoconti quest');"
        );

        DB::query("
            INSERT INTO `forum_tipo` (`nome`,`pubblico`) VALUES
                ('Pubblico',1),
                ('Gruppi',0),
                ('Razze',0),
                ('Privato',0);"
        );

        DB::query("
            INSERT INTO `gruppi` (`nome`, `tipo`, `immagine`, `url`, `statuto`, `visibile`) VALUES
                ('Guardia cittadina', '1', 'groups/standard_gilda.png', 'test', 'Statuto fasullo', 1);"
        );

        DB::query("
            INSERT INTO `gruppi_ruoli` (`gruppo`, `nome`, `immagine`, `stipendio`, `poteri`) VALUES
                (1, 'Capitano della guardia', 'groups/standard_gilda.png', 100, 1),
                (1, 'Ufficiale della guardia', 'groups/standard_gilda.png', 70, 0),
                (1, 'Soldato della guardia', 'groups/standard_gilda.png', 40, 0),
                (1, 'Recluta della guardia', 'groups/standard_gilda.png', 15, 0);"
        );

        DB::query("
            INSERT INTO `gruppi_tipo` (`nome`, `descrizione`) VALUES
                ('Positivo', 'prova'),
                ('Neutrale', 'prova'),
                ('Negativo', 'prova');"
        );

        DB::query("
            INSERT INTO `menu` (`menu_name`, `section`, `name`, `page`, `permission`) VALUES
              ('Gestione', 'Log', 'Log Chat', 'log_chat', 'LOG_CHAT'),
              ('Gestione', 'Log', 'Log Eventi', 'log_eventi', 'LOG_EVENTI'),
              ('Gestione', 'Log', 'Log Messaggi', 'log_messaggi', 'LOG_MESSAGGI'),
              ('Gestione', 'Abilità', 'Gestione Abilità', 'gestione/abilita/abilita/gestione_abilita', 'MANAGE_ABILITY'),
              ('Gestione', 'Abilità', 'Gestione Abilità Extra', 'gestione/abilita/extra/gestione_abilita_extra', 'MANAGE_ABILITY_EXTRA'),
              ('Gestione', 'Abilità', 'Gestione Abilità Requisiti', 'gestione/abilita/requisiti/gestione_requisiti', 'MANAGE_ABILITY_REQUIREMENT'),
              ('Gestione', 'Locations', 'Gestione Luoghi', 'gestione_luoghi', 'MANAGE_LOCATIONS'),
              ('Gestione', 'Locations', 'Gestione Mappe', 'gestione_mappe', 'MANAGE_MAPS'),
              ('Gestione', 'Documentazioni', 'Gestione Ambientazione', 'gestione_ambientazione', 'MANAGE_AMBIENT'),
              ('Gestione', 'Documentazioni', 'Gestione Regolamento', 'gestione_regolamento', 'MANAGE_RULES'),
              ('Gestione', 'Razze', 'Gestione Razze', 'gestione_razze', 'MANAGE_RACES'),
              ('Gestione', 'Bacheche', 'Gestione Bacheche', 'gestione_bacheche', 'MANAGE_FORUMS'),
              ('Gestione', 'Gruppi', 'Gestione Gruppi', 'gestione/gruppi/gruppi/gestione_gruppi', 'MANAGE_GROUPS'),
              ('Gestione', 'Gruppi', 'Gestione Gruppi Ruoli', 'gestione/gruppi/ruoli/gestione_ruoli', 'MANAGE_GROUPS'),
              ('Gestione', 'Gruppi', 'Gestione Gruppi Tipi', 'gestione/gruppi/tipi/gestione_tipi', 'MANAGE_GROUPS'),
              ('Gestione', 'Gruppi', 'Gestione Lavori', 'gestione/gruppi/lavori/gestione_lavori', 'MANAGE_WORKS'),
              ('Gestione', 'Gruppi', 'Assegna/Rimuovi lavori', 'gestione/gruppi/lavori/assign_lavori', 'MANAGE_WORKS'),
              ('Gestione', 'Gruppi', 'Gestione Gruppi Fondi', 'gestione/gruppi/fondi/gestione_fondi', 'MANAGE_GROUPS_FOUNDS'),
              ('Gestione', 'Gruppi', 'Gestione Stipendi Extra', 'gestione/gruppi/stipendiExtra/gestione_stipendi_extra', 'MANAGE_GROUPS_EXTRA_EARN'),
              ('Gestione', 'Gestione', 'Gestione Costanti', 'gestione/costanti/gestione_costanti_box', 'MANAGE_CONSTANTS'),
              ('Gestione', 'Gestione', 'Gestione Versioni Database', 'gestione_db_migrations', 'MANAGE_DB_MIGRATIONS'),
              ('Gestione', 'Permessi', 'Gestione Permessi', 'gestione_permessi', 'MANAGE_PERMISSIONS'),
              ('Gestione', 'Gestione', 'Manutenzione', 'gestione_manutenzione', 'MANAGE_MANUTENTIONS'),
              ('Gestione', 'Chat', 'Giocate Segnalate', 'gestione/registrazioni/index', 'MANAGE_REPORTS'),            
              ('Gestione', 'Chat', 'Opzioni Chat', 'gestione/chat/opzioni/gestione_chat_opzioni', 'MANAGE_CHAT_OPTIONS'),
              ('Gestione', 'Meteo', 'Gestione condizioni', 'gestione/meteo/condizioni/gestione_condizioni', 'MANAGE_WEATHER_CONDITIONS'),
              ('Gestione', 'Meteo', 'Gestione stagioni', 'gestione/meteo/stagioni/gestione_stagioni_index', 'MANAGE_WEATHER_SEASONS'),
              ('Gestione', 'Meteo', 'Gestione venti', 'gestione/meteo/venti/gestione_venti', 'MANAGE_WEATHER'),
              ('Gestione', 'Quest', 'Gestione Quest', 'gestione/quest/gestione_quest_index', 'MANAGE_QUESTS'),
              ('Gestione', 'Quest', 'Gestione Trame', 'gestione/trame/gestione_trame_index', 'MANAGE_TRAME_VIEW'),
              ('Gestione', 'Esiti', 'Esiti', 'gestione/esiti/esiti_index', 'MANAGE_ESITI'),
              ('Gestione', 'Stato Online', 'Gestione stati', 'gestione/online_status/gestione_status', 'MANAGE_ONLINE_STATUS'),
              ('Gestione', 'Stato Online', 'Gestione tipi stati', 'gestione/online_status/gestione_status_type', 'MANAGE_ONLINE_STATUS'),
              ('Gestione', 'Oggetti', 'Gestione oggetti', 'gestione/oggetti/oggetti/gestione_oggetti', 'MANAGE_OBJECTS'),
              ('Gestione', 'Oggetti', 'Gestione tipi oggetto', 'gestione/oggetti/tipo/gestione_oggetti_tipo', 'MANAGE_OBJECTS_TYPES'),
              ('Gestione', 'Oggetti', 'Gestione posizioni oggetto', 'gestione/oggetti/posizioni/gestione_oggetti_posizioni', 'MANAGE_OBJECTS_POSITIONS'),
              ('Gestione', 'Oggetti', 'Gestione Statistiche oggetto', 'gestione/oggetti/statistiche/gestione_oggetti_statistiche', 'MANAGE_OBJECTS_STATS'),
              ('Gestione', 'Oggetti', 'Assegna oggetto', 'gestione/oggetti/assegna/gestione_oggetti_assegna', 'MANAGE_OBJECTS'),
              ('Gestione', 'Mercato', 'Gestione Oggetti Mercato', 'gestione/mercato/gestione_mercato_oggetti', 'MANAGE_SHOPS_OBJECTS'),
              ('Gestione', 'Mercato', 'Gestione Negozi Mercato', 'gestione/mercato/gestione_mercato_negozi', 'MANAGE_SHOPS'),
              ('Gestione', 'Statistiche', 'Gestione Statistiche', 'gestione/statistiche/gestione_statistiche', 'MANAGE_STATS'),
              ('Gestione', 'Extra', 'Gestione Disponibilita', 'gestione/disponibilita/gestione_disponibilita', 'MANAGE_AVAILABILITIES'),
              ('Gestione', 'Extra', 'Gestione Sessi', 'gestione/sessi/gestione_sessi', 'MANAGE_GENDERS'),
              ('Gestione', 'Extra', 'Gestione Razze', 'gestione/razze/gestione_razze', 'MANAGE_RACES'),
              ('Gestione', 'Contatti', 'Gestione Categorie', 'gestione/contatti/gestione_categorie', 'MANAGE_CONTACTS_CATEGORIES'),
              ('Gestione', 'Forum', 'Gestione Forum', 'gestione/forum/forum/gestione_forum', 'FORUM_ADMIN'),
              ('Gestione', 'Forum', 'Gestione Tipi', 'gestione/forum/tipi/gestione_forum_tipi', 'FORUM_ADMIN'),
              ('Gestione', 'Forum', 'Gestione Permessi', 'gestione/forum/permessi/gestione_forum_permessi', 'FORUM_ADMIN'),
              ('Gestione', 'News', 'Gestione News', 'gestione/news/news/gestione_news', 'MANAGE_NEWS'),
              ('Gestione', 'News', 'Gestione Tipologia News', 'gestione/news/tipo/gestione_news_tipo', 'MANAGE_NEWS_TYPE'),
              ('Servizi', 'Anagrafe', 'Anagrafe', 'servizi/anagrafe/index', NULL),
              ('Servizi', 'Lavoro', 'Amministrazione Gruppi', 'servizi/amministrazioneGilde/index', NULL),
              ('Servizi', 'Lavoro', 'Lavoro', 'servizi/lavori/index', NULL),
              ('Servizi', 'Gruppi', 'Gruppi', 'servizi/gruppi/index', NULL),
              ('Servizi', 'Mercato', 'Mercato', 'servizi/mercato/mercato_index', NULL),
              ('Servizi', 'Esiti', 'Pannello esiti', 'servizi/esiti/esiti_index', NULL),
              ('Servizi', 'Stanze', 'Prenotazione stanze', 'servizi_prenotazioni', NULL),
              ('Servizi', 'Banca', 'Servizi bancari', 'servizi/banca/index', NULL),
              ('Utenti', 'Abilità', 'Abilità', 'utenti/abilita/index', NULL),
              ('Utenti', 'Ambientazione', 'Ambientazione', 'user_ambientazione', NULL),
              ('Utenti', 'Ambientazione', 'Razze', 'user_razze', NULL),
              ('Utenti', 'Regolamento', 'Regolamento', 'user_regolamento', NULL),
              ('Utenti', 'Gestione Utente', 'Cambio nome', 'user_cambio_nome', 'MANAGE_NAMES'),
              ('Utenti', 'Gestione Utente', 'Cambio password', 'user_cambio_pass', NULL),
              ('Utenti', 'Gestione Utente', 'Cancella account', 'user_cancella_pg', NULL),
              ('Utenti', 'Statistiche', 'Statistiche Sito', 'user_stats', NULL),
              ('Rapido', 'Scheda', 'Scheda', 'scheda/index', NULL),
              ('Rapido', 'Gestione', 'Gestione', 'gestione', 'MANAGEMENT_MENU'),
              ('Rapido', 'Servizi', 'Servizi', 'servizi/index', NULL),
              ('Rapido', 'Calendario', 'Calendario', 'calendario/index', NULL),
              ('Rapido', 'Menu utente', 'Pannello Utenti', 'utenti/index', NULL);"
        );

        DB::query("
            INSERT INTO `mappa` (`nome`, `descrizione`, `stato`, `pagina`, `chat`,`meteo_city`,`meteo_fisso`, `immagine`, `stanza_apparente`, `id_mappa`, `link_immagine`, `link_immagine_hover`, `id_mappa_collegata`, `x_cord`, `y_cord`, `invitati`, `privata`, `proprietario`, `ora_prenotazione`, `scadenza`, `costo`) VALUES
                ('Strada', 'Via che congiunge la periferia al centro.', 'Nella norma', '', 1,'New York',0, 'standard_luogo.png', '', 1, '', '', 0, 180, 150, '', 0, 'Nessuno', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 0),
                ('Piazza', 'Piccola piazza con panchine ed una fontana al centro.', 'Nella norma', '', 1,'Miami',0, 'standard_luogo.png', '', 1, '', '', 0, 80, 150, '', 0, 'Nessuno', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 0);"
        );

        DB::query("
            INSERT INTO `mappa_click` (`id_click`, `nome`, `immagine`, `posizione`, `mobile`, `meteo`, `larghezza`, `altezza`) VALUES
                (1, 'Mappa principale', 'spacer.gif', 2, 0, '20Â°c - sereno', 500, 330),
                (2, 'Mappa secondaria', 'spacer.gif', 2, 0, '18Â°c - nuvoloso', 500, 330);"
        );

        DB::query("
            INSERT INTO `meteo_condizioni` (`nome`, `img`, `vento`) VALUES
                ('Sereno', 'imgs/meteo/sereno.gif', '1,2,3'),
                ('Temporale', 'imgs/meteo/temporale.gif', '2,3'),
                ('Neve', 'imgs/meteo/neve.gif', '1,4'),
                ('Pioggia', 'imgs/meteo/pioggia.gif', '1,3');"
        );

        DB::query("
            INSERT INTO `meteo_stagioni` (`nome`, `minima`,`massima`, `data_inizio`,`data_fine`, `alba`, `tramonto`) VALUES
                ('Autunno', '8', '18', '2000-01-01', '2120-12-22','06:45:00', '19:00:00');"
        );

        DB::query("
            INSERT INTO `meteo_stagioni_condizioni` (`stagione`, `condizione`,`percentuale`) VALUES
                (1,1,25),
                (1,2,25),
                (1,3,25),
                (1,4,25);"
        );

        DB::query("
            INSERT INTO `meteo_venti` (`nome`) VALUES
                ('Brezza'),
                ('Brezza Leggera'),
                ('Vento forte'),
                ('Burrasca');"
        );

        DB::query("
            INSERT INTO `news_tipo` (`nome`,`descrizione`) VALUES
                ('On','Notizie On'),
                ('Off','Notizie Off');"
        );

        DB::query("
            INSERT INTO `oggetto_tipo` (`nome`) VALUES
                ('Animale'),
                ('Vestito'),
                ('Piante'),
                ('Gioiello'),
                ('Arma'),
                ('Attrezzo'),
                ('Vario');"
        );

        DB::query("
            INSERT INTO `online_status_type`(`label`,`request`) VALUES
                ('Tempo Login','Tempo online?'),
                ('Tempo Azione','Tempo azione medio?');"
        );

        DB::query("
            INSERT INTO `permessi_custom`(`permission_name`, `description`) VALUES
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
                ('MANAGE_CONSTANTS', 'Permesso per l\editing delle costanti'),
                ('MANAGE_RACES', 'Permesso per la gestione delle razze'),
                ('MANAGE_FORUMS', 'Permesso per la gestione delle bacheche'),
                ('MANAGE_GROUPS', 'Permesso per la gestione dei gruppi'),
                ('MANAGE_GROUPS_FOUNDS', 'Permesso per la gestione degli introiti dei gruppi'),
                ('MANAGE_GROUPS_EXTRA_EARN', 'Permesso per la gestione degli introiti dei gruppi'),
                ('MANAGE_GROUPS_STORAGES', 'Permesso per la gestione dei magazzini dei gruppi'),
                ('MANAGE_WORKS', 'Permesso per la gestione dei lavori liberi'),
                ('MANAGE_PERMISSIONS', 'Permesso per la gestione dei permessi'),
                ('MANAGE_MANUTENTIONS', 'Permesso per la gestione della manutenzione del db'),
                ('MANAGE_REPORTS', 'Permesso per la gestione delle giocate segnalate'),
                ('MANAGE_WEATHER', 'Permesso per la gestione meteo'),
                ('MANAGE_WEATHER_SEASONS', 'Permesso per la gestione delle stagioni meteo'),
                ('MANAGE_WEATHER_CONDITIONS', 'Permesso per la gestione delle condizioni meteo'),
                ('MANAGE_ESITI', 'Permesso per la gestione base degli esiti'),
                ('MANAGE_ALL_ESITI', 'Permesso per la visione/modifica di qualsiasi tipo di esito'),
                ('MANAGE_OUTCOMES', 'Permesso per la gestione degli esiti in chat'),
                ('MANAGE_QUESTS', 'Permesso per la gestione delle quest'),
                ('MANAGE_QUESTS_OTHER', 'Permesso per la gestione delle quest altrui'),
                ('MANAGE_TRAME_VIEW','Permesso per la visualizzazione delle trame'),
                ('MANAGE_TRAME','Permesso per la modifica delle trame'),
                ('MANAGE_TRAME_OTHER','Permesso per la modifica delle trame degli altri'),
                ('SCHEDA_EXP_VIEW','Permesso per la visualizzazione della pagina esperienza in scheda'),
                ('SCHEDA_EXP_MANAGE','Permesso per la visualizzazione della pagina esperienza in scheda'),
                ('SCHEDA_VIEW_LOGS','Permesso per la visione dei logs'),
                ('SCHEDA_VIEW_TRANSACTIONS','Permesso per la visione delle transazioni altrui'),
                ('SCHEDA_DIARY_VIEW','Permesso per la visione dei diari privati altrui'),
                ('SCHEDA_DIARY_EDIT','Permesso per la modifica dei diari altrui'),
                ('SCHEDA_UPDATE','Permesso per la modifica dei dati personaggio altrui'),
                ('SCHEDA_STATUS_MANAGE','Permesso per la modifica dei dati status altrui'),
                ('SCHEDA_BAN','Permesso per il ban di un personaggio dalla scheda'),
                ('SCHEDA_VIEW_RECORDS','Permesso per visualizzazione giocate registrate dei pg altrui'),
                ('SCHEDA_UPDATE_RECORDS','Permesso per modificare giocate registrate dei pg altrui'),
                ('SCHEDA_ADMINISTRATION_MANAGE','Permesso per la pagina amministrazione della scheda'),
                ('MANAGE_OBJECTS','Permesso per la gestione degli oggetti'),
                ('MANAGE_OBJECTS_TYPES','Permesso per la gestione delle tipologie di oggetti'),
                ('MANAGE_OBJECTS_STATS','Permesso per la gestione delle statistiche di oggetti'),
                ('MANAGE_OBJECTS_POSITIONS','Permesso per la gestione delle posizioni oggetti'),
                ('MANAGE_ONLINE_STATUS','Permesso per la gestione degli status online'),
                ('MANAGE_SHOPS','Permesso per la gestione degli status online'),
                ('MANAGE_SHOPS_OBJECTS','Permesso per la gestione degli status online'),
                ('VIEW_SCHEDA_OBJECTS','Permesso per la visualizzazione oggetti in schede altrui'),
                ('EQUIP_SCHEDA_OBJECTS','Permesso per l equipaggiamento oggetti in schede altrui'),
                ('REMOVE_SCHEDA_OBJECTS','Permesso per la rimozione oggetti in schede altrui'),
                ('MANAGE_STATS','Permesso per la gestione statistiche'),
                ('VIEW_SCHEDA_STATS','Permesso per la visualizzazione statistiche in schede altrui'),
                ('UPGRADE_SCHEDA_STATS','Permesso per laumento statistiche in schede altrui'),
                ('DOWNGRADE_SCHEDA_STATS','Permesso per la riduzione statistiche in schede altrui'),
                ('UPGRADE_SCHEDA_ABI','Permesso per aumento abilita in schede altrui'),
                ('DOWNGRADE_SCHEDA_ABI','Permesso per la diminuzione abilita in schede altrui'),
                ('VIEW_SCHEDA_ABI','Permesso per la visualizzazione abilita in schede altrui'),
                ('VIEW_CONTACTS','Permesso per la visualizzazione dei contatti in schede altrui'),
                ('UPDATE_CONTACTS','Permesso per la modifica dei contatti in schede altrui'),
                ('DELETE_CONTACTS','Permesso per la eliminazione dei contatti in schede altrui'),
                ('VIEW_CONTACTS_CATEGORIES','Permesso per la visualizzazione delle categorie contatti in schede altrui'),
                ('MANAGE_CONTACTS_CATEGORIES','Permesso per la gestione delle categorie contatti'),
                ('MANAGE_CHAT_OPTIONS','Permesso per la gestione delle opzioni personalizzabili in chat'),
                ('MANAGE_AVAILABILITIES','Permesso per la gestione delle disponibilita'),
                ('MANAGE_RACES','Permesso per la gestione delle razze'),
                ('MANAGE_GENDERS','Permesso per la gestione dei sessi'),
                ('MANAGE_NAMES','Permesso per la gestione dei nomi dei personaggi'),
                ('MANAGE_BANK','Permesso per la gestione della banca altrui'),
                ('MANAGEMENT_MENU','Permesso per la pagina gestione'),
                ('FORUM_VIEW_ALL','Permesso per la visione di tutti i forum esistenti'),
                ('FORUM_EDIT_ALL','Permesso per la modifica di tutti i forum visibili'),
                ('FORUM_ADMIN','Permesso per la modifica di tutti i forum visibili'),
                ('MANAGE_NEWS','Permesso per la gestione delle news'),
                ('MANAGE_NEWS_TYPE','Permesso per la gestione delle news'),
                ('CHAT_MASTER','Azioni master in chat'),
                ('CHAT_MODERATOR','Azioni moderatore in chat'),
                ('CALENDAR_ADD_QUEST','Aggiungi quest al calendario'),
                ('CALENDAR_ADD_AMBIENT','Aggiungi quest al calendario')
               ;"
        );

        DB::query("
            INSERT INTO `permessi_group` (`group_name`, `description`, `superuser`) VALUES
                ('SUPERUSER', 'Gruppo Permessi superuser', 1),
                ('MASTER', 'Gruppo permessi master', 0),
                ('MODERATOR', 'Gruppo permessi moderatore', 0),
                ('USER', 'Permessi gruppo user', 0);"
        );

        DB::query("
            INSERT INTO `permessi_group_personaggio` (`group_id`, `personaggio`) VALUES
                (1, 1);"
        );

        DB::queryStmt("
            INSERT INTO `personaggio` (`nome`, `cognome`, `pass`, `ultimo_cambiopass`, `data_iscrizione`, `email`, `permessi`, `ultima_mappa`, `ultimo_luogo`, `esilio`, `data_esilio`, `motivo_esilio`, `autore_esilio`, `sesso`, `razza`, `descrizione`, `affetti`, `stato`, `online_status`, `disponibile`, `url_img`, `url_img_chat`, `url_media`, `blocca_media`, `esperienza`, `salute`, `salute_max`, `soldi`, `banca`, `last_ip`, `is_invisible`, `ultimo_refresh`, `ora_entrata`, `ora_uscita`, `posizione`) VALUES
                ('Super', 'User', :superpassword, NULL, '2011-06-04 00:47:48', '\$P\$BNZYtz9JOQE.O4Tv7qZyl3SzIoZzzR.', 5, 1, -1, '2009-01-01', '2009-01-01', '', '', 1, 1, '', '', 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', '0', '1000.0000', 100, 100, 300, 50000,  '127.0.0.1', 0, '2021-10-08 00:28:13', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 1),
                ('Test', 'Di Funzionalità', :testpassword, NULL, '2011-06-04 00:47:48', '\$P\$Bd1amPCKkOF9GdgYsibZ96U92D5CtR0', 0, 1, -1, '2009-01-01', '2009-01-01', '', '', 1, 1, '', '', 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', '0', '1000.0000',  100, 100, 50, 50, '127.0.0.1', 0, '2009-01-01 00:00:00', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 1);",
            [
                'superpassword' => Password::hash('super'),
                'testpassword' => Password::hash('test'),
            ]
        );

        DB::query("
            INSERT INTO `razze` (`nome`, `sing_m`, `sing_f`, `descrizione`, `immagine`, `icon`, `url_site`, `iscrizione`, `visibile`) VALUES
                ('Umani', 'Umano', 'Umana', '', 'races/standard_razza.png', 'races/standard_razza.png', '', 1, 1);"
        );

        DB::query("
            INSERT INTO sessi(`nome`,`immagine`) VALUES
            ('Maschio','gender/m.png'),
            ('Femmina','gender/f.png');"
        );


        DB::query("
            INSERT INTO statistiche(`nome`,`max_val`,`min_val`,`descrizione`,`creato_da`) VALUES
            ('Forza',5,1,'Forza fisica','1'),
            ('Destrezza',5,1,'Destrezza manuale','1'),
            ('Intelligenza',5,1,'Intelligenza','1'),
            ('Carisma',5,1,'Carisma','1'),
            ('Costituzione',5,1,'Costituzione','1'),
            ('Saggezza',5,1,'Saggezza','1');"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {

    }
}
