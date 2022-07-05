<?php

/**
 * Snapshot full della struttura del DB per GDRCD 5.5.1
 */
class GDRCD6 extends DbMigration
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
        DB::query("
            INSERT INTO `abilita` (`nome`, `statistica`, `descrizione`, `razza`) VALUES
                ('Resistenza', 4, 'Il personaggio Ã¨ in grado di sopportare il dolore ed il disagio e sopporta minime dosi di agenti tossici nel proprio organismo. ', -1),
                ('Sopravvivenza', 4, 'Il personaggio Ã¨ in grado di procurarsi cibo e riparo all''aperto, con mezzi minimi.', -1);
        ");

        DB::query("
            INSERT INTO `araldo` (`id_araldo`, `tipo`, `nome`, `proprietari`) VALUES
                (1, 4, 'Resoconti quest', 0),
                (2, 0, 'Notizie in gioco', 0),
                (3, 2, 'Umani', 1000),
                (4, 3, 'Ordini alla Guardia', 1);"
        );

        DB::query("
            INSERT INTO `config` (`const_name`,`val`,`section`,`label`,`description`,`type`,`editable`) VALUES
                ('DEVELOPING',1,'Engine','Gdr in sviluppo?','','bool',1),
                ('STANDARD_ENGINE','default','Engine','Engine utilizzato. Non modificare se non necessario.','','string',1),
                ('TEMPLATE_ENGINE','Smarty','Template','Template utilizzato. Non modificare se non necessario.','','string',1),
                ('INLINE_CRONJOB',1,'Engine','Cronjob inline','Cronjob inline nell header?','bool',1),
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
                ('CHAT_SAVE_DOWNLOAD',1,'Chat Salvataggio','Salva chat download','Salva chat con download?','bool',1),
                ('WEATHER_MOON',1,'Meteo','Luna','Abilita o disabilita le fasi lunari','bool',1),
                ('WEATHER_SEASON',1,'Meteo','Stagionale','Meteo stagioni o meteo Web api','bool',1),
                ('WEATHER_WIND',1,'Meteo','Vento','Abilita o disabilita il vento','bool',1),
                ('WEATHER_WEBAPI',0,'Meteo','Web Api','Abilita Web Api di OpenWeather','bool',1),
                ('WEATHER_WEBAPIKEY','fake_key','Meteo','Web Api Key','Web Api di OpenWeather','String',1),
                ('WEATHER_WEBAPI_CITY','Rome','Meteo','Web Api Citta','Città di cui avere il meteo','String',1),
                ('WEATHER_WEBAPI_ICON',0,'Meteo','Web Api icone','Icone di default o personalizzate','bool',1),
                ('WEATHER_LAST_DATE',0,'Meteo','Meteo Impostazioni','Data ultimo aggiornamento meteo','String',0),
                ('WEATHER_WEBAPI_FORMAT','png','Meteo','Icone estensione','Estensione delle icone','String',1),
                ('WEATHER_LAST_CONDITION',0,'Meteo','Meteo Impostazioni','Ultime condizioni meteo registrato','String',0),
                ('WEATHER_LAST_TEMP',0,'Meteo','Meteo Impostazioni','Ultima temperatura registrata','String',0),
                ('WEATHER_LAST_IMG',0,'Meteo','Meteo Impostazioni','Ultima immagine registrata','String',0),
                ('WEATHER_LAST_WIND',0,'Meteo','Meteo Impostazioni','Ultimo vento registrato','String',0),
                ('WEATHER_UPDATE',4,'Meteo','Tempo di aggiornamento','Dopo quante ore si prevede il cambio meteo con le stagioni','int',1),
                ('ESITI_ENABLE',1,'Esiti','Attiva esiti','Abilitare la funzione esiti?','bool',1),
                ('ESITI_CHAT',1,'Esiti','Attiva esiti in chat','Abilitare la funzione di lancio degli esiti in chat?','bool',1),
                ('ESITI_TIRI',1,'Esiti','Lancio di dadi negli esiti','Abilitare la possibilità di lanciare dadi all''interno del pannello esiti?','bool',1),
                ('ESITI_FROM_PLAYER',1,'Esiti','Esiti dai player','Abilitare richiesta esiti da parte dei player?','bool',1),
                ('QUEST_ENABLED',1,'Quest','Attivazione Quest migliorate','Gestione quest migliorata, attiva?','bool',1),
                ('QUEST_VIEW',2,'Quest','Permessi visual quest','Permesso minimo per visualizzazione delle quest','permission',1),
                ('QUEST_SUPER_PERMISSION',3,'Quest','Permessi speciali','Permesso minimo per modificare qualsiasi parte del pacchetto','int',1),
                ('QUEST_NOTIFY',0,'Quest','Notifiche quest','Definisce la possibilità di inviare messaggi automatici di avviso agli utenti che partecipano ad una quest','bool',1),
                ('TRAME_ENABLED',1,'Trame','Attivazione trame','Sistema trame attivo?','bool',1),
                ('QUEST_RESULTS_FOR_PAGE',15,'Quest','Risultati per pagina','Numero risultati per pagina nella gestione delle quest.','int',1),
                ('ONLINE_STATUS_ENABLED',1,'Online Status','Stato online avanzato','Stato online avanzato,attivo?','bool',1),
                ('ONLINE_STATUS_LOGIN_REFRESH',1,'Online Status','Reselect al login','Login oscura ultima scelta dai presenti?','bool',1),
                ('SCHEDA_OBJECTS_PUBLIC',1,'Scheda Oggetti','Scheda Oggetti pubblica','Pagina inventario pubblica?','bool',1),
                ('SCHEDA_STATS_PUBLIC',1,'Scheda Oggetti','Scheda Statistiche pubblica','Pagina statistica pubblica?','bool',1),
                ('SCHEDA_ABI_PUBLIC',1,'Scheda Abilita','Scheda Abilita pubblica','Pagina abilita pubblica?','bool',1),
                ('GROUPS_ACTIVE',1,'Gruppi','Gruppi attivi','Gruppi attivi?','bool',1),
                ('GROUPS_MAX_ROLES',3,'Gruppi','Massimo ruoli','Numero massimo di ruoli','int',1),
                ('WORKS_ACTIVE',1,'Gruppi','Lavori attivi','Lavori attivi?','bool',1),
                ('WORKS_DIMISSIONS_DAYS',1,'Gruppi','Giorni per dimissioni','Giorni per dimissioni','bool',1),
                ('WORKS_MAX',3,'Gruppi','Massimo lavori liberi','Numero massimo di lavori liberi','int',1),
                ('CONTACTS_ENABLED', 1, 'Contatti','Abilita/Disabilita la sezione contatti',  'Abilita/disabilita i contatti', 'bool', 1),
                ('CONTACT_PUBLIC', 1, 'Contatti','Abilita/Disabilita la visualizzazione pubblica dei contatti',  'Abilita/Disabilita la visualizzazione pubblica dei contatti', 'bool', 1),
                ('CONTACT_SECRETS', 1, 'Contatti','Abilita/Disabilita la scelta di nascondere le note',  'Abilita/Disabilita la scelta di nascondere le note', 'bool', 1),
                ('CONTACT_CATEGORIES', 1, 'Contatti','Abilita/Disabilita le categorie',  'Abilita/Disabilita le categorie', 'bool', 1),
                ('CONTACT_CATEGORIES_PUBLIC', 1, 'Contatti','Se abilitato, tutti vedono le categorie',  'Se abilitato, tutti vedono le categorie', 'bool', 1),
                ('CONTACT_CATEGORIES_STAFF_ONLY', 0, 'Contatti', 'Se abilitato, solo lo staff può assegnare le categorie di contatto', 'Se abilitato, solo lo staff può assegnare le categorie di contatto', 'bool', 1);"
        );


        DB::query("
            INSERT INTO `cronjob` (`name`,`last_exec`,`in_exec`,`interval`,`interval_type`,`class`,`function`) VALUES
                ('meteo_update',NULL,false,'60','minutes','Meteo','generateGlobalWeather'),
                ('stipendi_assign',NULL,false,'1','days','Gruppi','cronSalaries');"
        );

        DB::query("
            INSERT INTO `gruppi` (`nome`, `tipo`, `immagine`, `url`, `statuto`, `visibile`) VALUES
                ('Guardia cittadina', '1', 'standard_gilda.png', 'test', 'Statuto fasullo', 1);"
        );

        DB::query("
            INSERT INTO `gruppi_ruoli` (`gruppo`, `nome`, `immagine`, `stipendio`, `poteri`) VALUES
                (1, 'Capitano della guardia', 'standard_gilda.png', 100, 1),
                (1, 'Ufficiale della guardia', 'standard_gilda.png', 70, 0),
                (1, 'Soldato della guardia', 'standard_gilda.png', 40, 0),
                (1, 'Recluta della guardia', 'standard_gilda.png', 15, 0);"
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
              ('Gestione', 'Abilità', 'Gestione Abilità', 'gestione_abilita', 'MANAGE_ABILITY'),
              ('Gestione', 'Abilità', 'Dati Extra Abilità', 'gestione/abilita/extra/gestione_abilita_extra', 'MANAGE_ABILITY_EXTRA'),
              ('Gestione', 'Abilità', 'Requisiti abilità', 'gestione/abilita/requisiti/gestione_requisiti', 'MANAGE_ABILITY_REQUIREMENT'),
              ('Gestione', 'Locations', 'Gestione Luoghi', 'gestione_luoghi', 'MANAGE_LOCATIONS'),
              ('Gestione', 'Locations', 'Gestione Mappe', 'gestione_mappe', 'MANAGE_MAPS'),
              ('Gestione', 'Documentazioni', 'Gestione Ambientazione', 'gestione_ambientazione', 'MANAGE_AMBIENT'),
              ('Gestione', 'Documentazioni', 'Gestione Regolamento', 'gestione_regolamento', 'MANAGE_RULES'),
              ('Gestione', 'Razze', 'Gestione Razze', 'gestione_razze', 'MANAGE_RACES'),
              ('Gestione', 'Bacheche', 'Gestione Bacheche', 'gestione_bacheche', 'MANAGE_FORUMS'),
              ('Gestione', 'Gruppi', 'Gestione Gruppi', 'gestione/gruppi/gruppi/gestione_gruppi', 'MANAGE_GROUPS'),
              ('Gestione', 'Gruppi', 'Gestione Ruoli', 'gestione/gruppi/ruoli/gestione_ruoli', 'MANAGE_GROUPS'),
              ('Gestione', 'Gruppi', 'Gestione Tipi', 'gestione/gruppi/tipi/gestione_tipi', 'MANAGE_GROUPS'),
              ('Gestione', 'Gruppi', 'Gestione Lavori', 'gestione/gruppi/lavori/gestione_lavori', 'MANAGE_WORKS'),
              ('Gestione', 'Gruppi', 'Assegna/Rimuovi lavori', 'gestione/gruppi/lavori/assign_lavori', 'MANAGE_WORKS'),
              ('Gestione', 'Gestione', 'Gestione Costanti', 'gestione/costanti/gestione_costanti_box', 'MANAGE_CONSTANTS'),
              ('Gestione', 'Gestione', 'Gestione Versioni Database', 'gestione_db_migrations', 'MANAGE_DB_MIGRATIONS'),
              ('Gestione', 'Permessi', 'Gestione Permessi', 'gestione_permessi', 'MANAGE_PERMISSIONS'),
              ('Gestione', 'Gestione', 'Manutenzione', 'gestione_manutenzione', 'MANAGE_MANUTENTIONS'),
              ('Gestione', 'Chat', 'Giocate Segnalate', 'gestione/segnalazioni/esito_index', 'MANAGE_REPORTS'),
              ('Gestione', 'Meteo', 'Gestione condizioni', 'gestione/meteo/condizioni/gestione_condizioni', 'MANAGE_WEATHER_CONDITIONS'),
              ('Gestione', 'Meteo', 'Gestione stagioni', 'gestione/meteo/stagioni/gestione_stagioni_index', 'MANAGE_WEATHER_SEASONS'),
              ('Gestione', 'Meteo', 'Gestione venti', 'gestione/meteo/venti/gestione_venti', 'MANAGE_WEATHER'),
              ('Gestione', 'Quest', 'Gestione Quest', 'gestione/quest/gestione_quest_index', 'MANAGE_QUESTS'),
              ('Gestione', 'Quest', 'Gestione Trame', 'gestione/trame/gestione_trame_index', 'MANAGE_TRAME_VIEW'),
              ('Gestione', 'Esiti', 'Esiti', 'gestione/esiti/esiti_index', 'MANAGE_ESITI'),
              ('Gestione', 'Stato Online', 'Gestione stati', 'gestione/online_status/gestione_status', 'MANAGE_ONLINE_STATUS'),
              ('Gestione', 'Stato Online', 'Gestione tipi stati', 'gestione/online_status/gestione_status_type', 'MANAGE_ONLINE_STATUS'),
              ('Gestione', 'Oggetti', 'Gestione oggetti', 'gestione/oggetti/gestione_oggetti', 'MANAGE_OBJECTS'),
              ('Gestione', 'Oggetti', 'Gestione tipi oggetto', 'gestione/oggetti/gestione_oggetti_tipo', 'MANAGE_OBJECTS_TYPES'),
              ('Gestione', 'Oggetti', 'Gestione posizioni oggetto', 'gestione/oggetti/gestione_oggetti_posizioni', 'MANAGE_OBJECTS_POSITIONS'),
              ('Gestione', 'Mercato', 'Gestione Oggetti Mercato', 'gestione/mercato/gestione_mercato_oggetti', 'MANAGE_SHOPS_OBJECTS'),
              ('Gestione', 'Mercato', 'Gestione Negozi Mercato', 'gestione/mercato/gestione_mercato_negozi', 'MANAGE_SHOPS'),
              ('Gestione', 'Statistiche', 'Gestione Statistiche', 'gestione/statistiche/gestione_statistiche', 'MANAGE_STATS'),
              ('Gestione', 'Contatti', 'Gestione Categorie', 'gestione/contatti/gestione_categorie', 'MANAGE_CONTACTS_CATEGORIES');"
        );

        DB::query("
            INSERT INTO `mappa` (`id`, `nome`, `descrizione`, `stato`, `pagina`, `chat`,`meteo_citta`,`meteo_fisso`, `immagine`, `stanza_apparente`, `id_mappa`, `link_immagine`, `link_immagine_hover`, `id_mappa_collegata`, `x_cord`, `y_cord`, `invitati`, `privata`, `proprietario`, `ora_prenotazione`, `scadenza`, `costo`) VALUES
                (1, 'Strada', 'Via che congiunge la periferia al centro.', 'Nella norma', '', 1,'New York',0, 'standard_luogo.png', '', 1, '', '', 0, 180, 150, '', 0, 'Nessuno', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 0),
                (2, 'Piazza', 'Piccola piazza con panchine ed una fontana al centro.', 'Nella norma', '', 1,'Miami',0, 'standard_luogo.png', '', 1, '', '', 0, 80, 150, '', 0, 'Nessuno', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 0);"
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
                ('MANAGE_WORKS', 'Permesso per la gestione dei lavori liberi'),
                ('MANAGE_PERMISSIONS', 'Permesso per la gestione dei permessi'),
                ('MANAGE_MANUTENTIONS', 'Permesso per la gestione della manutenzione del db'),
                ('MANAGE_REPORTS', 'Permesso per la gestione delle giocate segnalate'),
                ('MANAGE_WEATHER ', 'Permesso per la gestione meteo'),
                ('MANAGE_WEATHER_SEASONS', 'Permesso per la gestione delle stagioni meteo'),
                ('MANAGE_WEATHER_CONDITIONS ', 'Permesso per la gestione delle condizioni meteo'),
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
                ('MANAGE_OBJECTS','Permesso per la gestione degli oggetti'),
                ('MANAGE_OBJECTS_TYPES','Permesso per la gestione delle tipologie di oggetti'),
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
                ('VIEW_CONTACTS_CATEGORIES','Permesso per la visualizzazione delle categorie contatti in schede altrui'),
                ('MANAGE_CONTACTS_CATEGORIES','Permesso per la gestione delle categorie contatti')
                ;"
        );

        DB::query("
            INSERT INTO `permessi_group` (`id`, `group_name`, `description`, `superuser`) VALUES
                (1, 'MASTER', 'Gruppo permessi master', 0),
                (2, 'MODERATOR', 'Gruppo permessi moderatore', 0),
                (3, 'SUPERUSER', 'Gruppo Permessi superuser', 1),
                (4, 'USER', 'Permessi gruppo user', 0);"
        );

        DB::query("
            INSERT INTO `permessi_group_personaggio` (`id`, `group_id`, `personaggio`) VALUES
                (1, 3, 1);"
        );

        DB::query("
            INSERT INTO `personaggio` (`id`,`nome`, `cognome`, `pass`, `ultimo_cambiopass`, `data_iscrizione`, `email`, `permessi`, `ultima_mappa`, `ultimo_luogo`, `esilio`, `data_esilio`, `motivo_esilio`, `autore_esilio`, `sesso`, `id_razza`, `descrizione`, `affetti`, `stato`, `online_status`, `disponibile`, `url_img`, `url_img_chat`, `url_media`, `blocca_media`, `esperienza`, `car0`, `car1`, `car2`, `car3`, `car4`, `car5`, `salute`, `salute_max`, `data_ultima_gilda`, `soldi`, `banca`, `ultimo_stipendio`, `last_ip`, `is_invisible`, `ultimo_refresh`, `ora_entrata`, `ora_uscita`, `posizione`) VALUES
                (1,'Super', 'User', '\$P\$BcH1cP941XHOf0X61wVWWjzXqcCi2a/', NULL, '2011-06-04 00:47:48', '\$P\$BNZYtz9JOQE.O4Tv7qZyl3SzIoZzzR.', 5, 1, -1, '2009-01-01', '2009-01-01', '', '', 'm', 1000, '', '', 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', '0', '1000.0000', 7, 8, 6, 5, 6, 5, 100, 100, '2009-01-01 00:00:00', 300, 50000, '2009-01-01', '127.0.0.1', 0, '2021-10-08 00:28:13', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 1),
                (2,'Test', 'Di FunzionaliÃ ', '\$P\$BUoa19QUuXsgIDlhGC3chR/3Q7hoRy0', NULL, '2011-06-04 00:47:48', '\$P\$Bd1amPCKkOF9GdgYsibZ96U92D5CtR0', 0, 1, -1, '2009-01-01', '2009-01-01', '', '', 'm', 1000, '', '', 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', '0', '1000.0000', 7, 8, 6, 5, 6, 5, 100, 100, '2009-01-01 00:00:00', 50, 50, '2009-01-01', '127.0.0.1', 0, '2009-01-01 00:00:00', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 1);"
        );

        DB::query("
            INSERT INTO `razza` (`id_razza`, `nome_razza`, `sing_m`, `sing_f`, `descrizione`, `bonus_car0`, `bonus_car1`, `bonus_car2`, `bonus_car3`, `bonus_car4`, `bonus_car5`, `immagine`, `icon`, `url_site`, `iscrizione`, `visibile`) VALUES
                (1000, 'Umani', 'Umano', 'Umana', '', 0, 0, 0, 0, 0, 0, 'standard_razza.png', 'standard_razza.png', '', 1, 1);"
        );

    }

    /**
     * @inheritDoc
     */
    public function down()
    {

    }
}
