<?php

/**
 * Snapshot full della struttura del DB per GDRCD 5.5.1
 */
class GDRCD551 extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');
    
        gdrcd_query("CREATE TABLE abilita (
            id_abilita int(4) NOT NULL auto_increment,
            nome varchar(20) NOT NULL,
            car tinyint(1) NOT NULL default '0',
            descrizione text NOT NULL,
            id_razza int(2) NOT NULL default '0',
            PRIMARY KEY  (id_abilita)
        )ENGINE=MyISAM  DEFAULT CHARSET=utf8");
    
        gdrcd_query("INSERT INTO abilita VALUES (18, 'Resistenza', 1, 'Il personaggio è in grado di sopportare il dolore ed il disagio e sopporta minime dosi di agenti tossici nel proprio organismo. ', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (17, 'Sopravvivenza', 4, 'Il personaggio è in grado di procurarsi cibo e riparo all\'aperto, con mezzi minimi.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (4, 'Atletica', 2, 'Il personaggio è ben allenato ed è in grado di saltare efficacemente, arrampicarsi, nuotare, schivare e compiere, genericamente, movimenti fisicamente impegnativi.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (5, 'Cercare', 5, 'Il personaggio è rapido ed efficace nel perquisire un ambiente in cerca di qualcosa.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (6, 'Conoscenza', 3, 'Il personaggio ha accumulato cultura ed esperienze, e potrebbe avere maggiori informazioni sulla situazione in cui si trova. A fronte di una prova di conoscenza il master dovrebbe fornire informazioni al giocatore via sussurro.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (7, 'Percepire intenzioni', 4, 'Il personaggio è abile nel determinare, durante una conversazione o un interazione, se il suo interlocutore stia mentendo, sia ostile o sia ben disposto.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (8, 'Cavalcare', 2, 'Il personaggio è in grado di cavalcare animali addestrati a tale scopo.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (9, 'Addestrare animali', 4, 'Il personaggio comprende gli atteggiamenti e le reazioni degli animali ed è in grado di interagire con loro, addomesticarli ed addestrarli.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (10, 'Armi bianche', 0, 'Il personaggio è addestrato al combattimento con armi bianche, scudi e protezioni.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (11, 'Armi da tiro', 5, 'Il personaggio è addestrato all\'uso di armi da diro o da lancio.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (12, 'Lotta', 0, 'Il personaggio è addestrato al combattimento senza armi.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (13, 'Competenze tecniche', 3, 'Il personaggio è in grado di realizzare e riparare strumenti tecnologici. Il tipo ed il numero di tecnologie in cui è competente dovrebbe essere specificato nel background e proporzionale al punteggio di intelligenza.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (14, 'Mezzi di trasporto', 5, 'Il personaggio è in grado di governare o pilotare specifici mezzi di trasporto. L\'elenco dei mezzi dovrebbe essere riportato nel background e proporzionale al punteggio di intelligenza.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (15, 'Pronto soccorso', 3, 'Il personaggio è in grado di eseguire interventi d\'emergenza su individui feriti o la cui salute sia in qualche modo minacciata.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (16, 'Furtività', 2, 'Il personaggio è in grado di muoversi ed agire senza dare nell\'occhio, e di scassinare serrature.', -1);");
    
        gdrcd_query("INSERT INTO abilita VALUES (19, 'Volontà', 4, 'Il personaggio è fortemente determinato e difficilmente si lascia persuadere o dissuadere.', -1);");
    
        gdrcd_query("CREATE TABLE ambientazione (
            capitolo int(2) NOT NULL,
            testo text NOT NULL,
            titolo varchar(30) NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE araldo (
            id_araldo int(4) NOT NULL auto_increment,
            tipo int(2) NOT NULL default '0',
            nome char(50) default NULL,
            proprietari int(2) NOT NULL default '0',
            PRIMARY KEY  (id_araldo)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("INSERT INTO araldo VALUES (1, 4, 'Resoconti quest', 0);");
    
        gdrcd_query("INSERT INTO araldo VALUES (2, 0, 'Notizie in gioco', 0);");
    
        gdrcd_query("INSERT INTO araldo VALUES (3, 2, 'Umani', 1000);");
    
        gdrcd_query("INSERT INTO araldo VALUES (4, 3, 'Ordini alla Guardia', 1);");
    
        gdrcd_query("CREATE TABLE araldo_letto (
            id int(20) NOT NULL auto_increment,
            nome char(50) default NULL,
            araldo_id int(7) NOT NULL,
            thread_id int(11) NOT NULL,
            PRIMARY KEY  (id)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("ALTER TABLE  `araldo_letto` ADD INDEX (  `nome` ,  `thread_id` ) ;");
    
        gdrcd_query("CREATE TABLE backmessaggi (
            id bigint(20) NOT NULL auto_increment,
            mittente varchar(20) NOT NULL default '',
            destinatario varchar(20) NOT NULL default '',
            spedito datetime NOT NULL default '0000-00-00 00:00:00',
            letto tinyint(1) default '0',
            testo text,
            PRIMARY KEY  (id)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE blacklist (
            ip char(15) NOT NULL default '',
            nota char(255) default NULL,
            granted tinyint(1) NOT NULL default '0',
            ora datetime default NULL,
            host char(255) NOT NULL default '-',
            PRIMARY KEY  (ip),
            KEY Ora (ora)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE chat (
            id bigint(20) NOT NULL auto_increment,
            stanza int(4) NOT NULL default '0',
            imgs varchar(100) NOT NULL default '',
            mittente varchar(20) NOT NULL default '',
            destinatario varchar(20) default NULL,
            ora datetime NOT NULL default '0000-00-00 00:00:00',
            tipo char(1) default NULL,
            testo text,
            PRIMARY KEY  (id),
            KEY Stanza (stanza)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE clgpersonaggioabilita (
            nome varchar(20) NOT NULL,
            id_abilita int(4) NOT NULL,
            grado int(4) NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE clgpersonaggiomostrine (
            nome char(20) NOT NULL default '',
            id_mostrina char(20) NOT NULL default '',
            PRIMARY KEY  (nome, id_mostrina)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE clgpersonaggiooggetto (
            nome varchar(20) NOT NULL default '',
            id_oggetto int(4) NOT NULL default '0',
            numero int(8) default '1',
            cariche int(4) NOT NULL default '-1',
            commento varchar(255) default NULL,
            posizione int(2) NOT NULL default '0',
            PRIMARY KEY  (nome, id_oggetto)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE clgpersonaggioruolo (
            personaggio varchar(20) NOT NULL,
            id_ruolo int(4) NOT NULL default '0',
            scadenza date NOT NULL default '2010-01-01'
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE codmostrina (
            id_mostrina int(4) NOT NULL auto_increment,
            nome varchar(20) NOT NULL,
            img_url char(50) NOT NULL default 'grigia.gif',
            descrizione char(255) NOT NULL default 'nessuna',
            PRIMARY KEY  (id_mostrina)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE codtipogilda (
            descrizione varchar(50) NOT NULL,
            cod_tipo int(2) NOT NULL auto_increment,
            PRIMARY KEY  (cod_tipo)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("INSERT INTO codtipogilda VALUES ('Positivo', 1);");
    
        gdrcd_query("INSERT INTO codtipogilda VALUES ('Neutrale', 2);");
    
        gdrcd_query("INSERT INTO codtipogilda VALUES ('Negativo', 3);");
    
        gdrcd_query("CREATE TABLE codtipooggetto (
            cod_tipo int(2) NOT NULL auto_increment,
            descrizione char(20) NOT NULL,
            PRIMARY KEY  (cod_tipo)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("INSERT INTO codtipooggetto VALUES (1, 'Animale');");
    
        gdrcd_query("INSERT INTO codtipooggetto VALUES (2, 'Vestito');");
    
        gdrcd_query("INSERT INTO codtipooggetto VALUES (3, 'Fiore - Pianta');");
    
        gdrcd_query("INSERT INTO codtipooggetto VALUES (4, 'Gioiello');");
    
        gdrcd_query("INSERT INTO codtipooggetto VALUES (5, 'Arma');");
    
        gdrcd_query("INSERT INTO codtipooggetto VALUES (6, 'Attrezzo');");
    
        gdrcd_query("INSERT INTO codtipooggetto VALUES (0, 'Vario');");
    
        gdrcd_query("CREATE TABLE gilda (
            id_gilda int(4) NOT NULL auto_increment,
            nome char(50) NOT NULL default '',
            tipo varchar(1) NOT NULL default '0',
            immagine char(255) default NULL,
            url_sito char(255) default NULL,
            statuto text,
            visibile tinyint(1) NOT NULL default '0',
            PRIMARY KEY  (id_gilda)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("INSERT INTO gilda VALUES (1, 'Guardia cittadina', '1', 'standard_gilda.png', '', '', 1);");
    
        gdrcd_query("CREATE TABLE log (
            id int(11) NOT NULL auto_increment,
            nome_interessato varchar(20) NOT NULL default '',
            autore varchar(60) NOT NULL default '',
            data_evento datetime NOT NULL default '0000-00-00 00:00:00',
            codice_evento char(20) NOT NULL default '',
            descrizione_evento char(100) NOT NULL default '',
            PRIMARY KEY  (id)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE IF NOT EXISTS mappa (
            id int(4) NOT NULL AUTO_INCREMENT,
            nome varchar(50) DEFAULT NULL,
            descrizione text,
            stato varchar(50) NOT NULL DEFAULT '',
            pagina varchar(255) DEFAULT 'nulla.php',
            chat tinyint(1) NOT NULL DEFAULT '1',
            immagine varchar(50) DEFAULT 'standard_luogo.png',
            stanza_apparente varchar(50) DEFAULT NULL,
            id_mappa int(4) DEFAULT '0',
            link_immagine varchar(256) NOT NULL,
            link_immagine_hover varchar(256) NOT NULL,
            id_mappa_collegata int(11) NOT NULL DEFAULT '0',
            x_cord int(4) DEFAULT '0',
            y_cord int(4) DEFAULT '0',
            invitati text NOT NULL,
            privata tinyint(1) NOT NULL DEFAULT '0',
            proprietario char(20) DEFAULT NULL,
            ora_prenotazione datetime DEFAULT NULL,
            scadenza datetime DEFAULT NULL,
            costo int(4) DEFAULT '0',
            PRIMARY KEY (id),
            FULLTEXT KEY Invitati (invitati)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("INSERT INTO mappa VALUES (1, 'Strada', 'Via che congiunge la periferia al centro.', 'Nella norma', '', 1, 'standard_luogo.png', '', 1, '', '', 0, 180, 150, '', 0, 'Nessuno', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0);");
    
        gdrcd_query("INSERT INTO mappa VALUES (2, 'Piazza', 'Piccola piazza con panchine ed una fontana al centro.', 'Nella norma', '', 1, 'standard_luogo.png', '', 1, '', '', 0, 80, 150, '', 0, 'Nessuno', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0);");
    
        gdrcd_query("CREATE TABLE mappa_click (
            id_click int(1) NOT NULL auto_increment,
            nome varchar(50) default NULL,
            immagine varchar(50) NOT NULL default 'standard_mappa.png',
            posizione int(2) NOT NULL default '0',
            mobile tinyint(1) NOT NULL default '0',
            meteo varchar(40) NOT NULL default '20°c - sereno',
            larghezza smallint(4) NOT NULL DEFAULT '500',
            altezza smallint(4) NOT NULL DEFAULT '330',
            PRIMARY KEY  (id_click)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("INSERT INTO mappa_click VALUES (1, 'Mappa principale', 'spacer.gif', 2, 0, '20°c - sereno', 500, 330);");
    
        gdrcd_query("INSERT INTO mappa_click VALUES (2, 'Mappa secondaria', 'spacer.gif', 2, 0, '18°c - nuvoloso', 500, 330);");
    
        gdrcd_query("CREATE TABLE mercato (
            id_oggetto int(4) NOT NULL,
            numero int(4) default '0',
            PRIMARY KEY  (id_oggetto)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE messaggi (
            id bigint(20) NOT NULL auto_increment,
            mittente varchar(40) NOT NULL,
            destinatario varchar(20) NOT NULL default 'Nessuno',
            spedito datetime NOT NULL default '0000-00-00 00:00:00',
            letto tinyint(1) default '0',
            mittente_del tinyint(1) default '0',
            destinatario_del tinyint(1) default '0',
            testo text,
            PRIMARY KEY  (id),
            KEY destinatario (destinatario),
            KEY letto (letto)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE messaggioaraldo (
            id_messaggio bigint(20) NOT NULL auto_increment,
            id_messaggio_padre bigint(20) NOT NULL default '0',
            id_araldo int(4) default NULL,
            titolo varchar(255) default NULL,
            messaggio text,
            autore varchar(20) default NULL,
            data_messaggio datetime default NULL,
            data_ultimo_messaggio datetime default NULL,
            importante binary(1) NOT NULL DEFAULT '0',
            chiuso binary(1) NOT NULL DEFAULT '0',
            PRIMARY KEY  (id_messaggio),
            KEY id_araldo (id_araldo),
            KEY id_messaggio_padre (id_messaggio_padre),
            KEY data_messaggio (data_messaggio),
            KEY importante (importante,chiuso)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0;");
    
        gdrcd_query("CREATE TABLE oggetto (
            id_oggetto int(4) NOT NULL auto_increment,
            tipo int(2) NOT NULL default '0',
            nome varchar(50) NOT NULL default 'Sconosciuto',
            creatore varchar(20) NOT NULL default 'System Op',
            data_inserimento datetime NOT NULL default '0000-00-00 00:00:00',
            descrizione varchar(255) NOT NULL default 'Nessuna',
            ubicabile int(2) NOT NULL default '0',
            costo int(11) NOT NULL default '0',
            difesa int(4) NOT NULL default '0',
            attacco int(4) NOT NULL default '0',
            cariche varchar(10) NOT NULL default '0',
            bonus_car0 int(4) NOT NULL default '0',
            bonus_car1 int(4) NOT NULL default '0',
            bonus_car2 int(4) NOT NULL default '0',
            bonus_car3 int(4) NOT NULL default '0',
            bonus_car4 int(4) NOT NULL default '0',
            bonus_car5 int(4) NOT NULL default '0',
            urlimg varchar(50) default NULL,
            PRIMARY KEY  (id_oggetto),
            KEY Tipo (tipo)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
    
        gdrcd_query("INSERT INTO oggetto VALUES (1, 6, 'Scopa', 'Super', '2009-12-20 14:29:33', 'Una comune scopa di saggina.', 0, 10, 0, 0, '0', 0, 0, 0, 0, 0, 0, 'standard_oggetto.png');");
    
        gdrcd_query("CREATE TABLE personaggio (
            nome varchar(20) NOT NULL default '',
            cognome varchar(50) NOT NULL default '-',
            pass varchar(60) NOT NULL default '',
            ultimo_cambiopass datetime DEFAULT NULL,
            data_iscrizione datetime default NULL,
            email varchar(60) default NULL,
            permessi tinyint(1) default '0',
            ultima_mappa int(4) NOT NULL default '1',
            ultimo_luogo int(4) NOT NULL default '-1',
            esilio date default NULL,
            data_esilio date NOT NULL default '2009-07-01',
            motivo_esilio varchar(255) default NULL,
            autore_esilio varchar(20) default NULL,
            sesso char(1) default 'm',
            id_razza int(4) default '1000',
            descrizione text,
            affetti text,
            stato varchar(255) default 'nessuna',
            online_status varchar(100) DEFAULT NULL,
            disponibile tinyint(1) NOT NULL default '1',
            url_img varchar(255) default 'imgs/avatars/empty.png',
            url_img_chat varchar(255) NOT NULL DEFAULT ' ',
            url_media varchar(255) default NULL,
            blocca_media binary(1) NOT NULL DEFAULT '0',
            esperienza decimal(14,5) default '0',
            car0 int(4) NOT NULL default '5',
            car1 int(4) NOT NULL default '5',
            car2 int(4) NOT NULL default '5',
            car3 int(4) NOT NULL default '5',
            car4 int(4) NOT NULL default '5',
            car5 int(4) NOT NULL default '5',
            salute int(4) NOT NULL default '100',
            salute_max int(4) NOT NULL default '100',
            data_ultima_gilda datetime NOT NULL default '2009-07-01 00:00:00',
            soldi int(11) default '50',
            banca int(11) default '0',
            ultimo_stipendio date NOT NULL default '2009-07-01',
            last_ip varchar(60) default NULL,
            is_invisible tinyint(1) NOT NULL default '0',
            ultimo_refresh datetime default NULL,
            ora_entrata datetime default NULL,
            ora_uscita datetime default NULL,
            posizione int(4) NOT NULL default '1',
            ultimo_messaggio bigint(20) NOT NULL default '0',
            PRIMARY KEY  (nome),
            KEY IDRazza (id_razza),
            KEY Esilio (esilio)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        gdrcd_query("INSERT INTO personaggio VALUES ('Super', 'User', '" . gdrcd_encript('super') . "', NULL, now(), '".gdrcd_encript('super@gdrcd.test')."', 4, 1, -1, '2009-07-01', '2009-07-01', '', '', 'm', 1000, '', '', 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', 0, 1000, 7, 8, 6, 5, 6, 5, 100, 100, '2009-07-01 00:00:00', 300, 50000, '2009-07-01', '127.0.0.1', 0, '2009-07-01 00:00:00', '2009-07-01 00:00:00', '2009-07-01 00:00:00', 1, 0);");
    
        gdrcd_query("INSERT INTO personaggio VALUES ('Test', 'Di Funzionalià', '" . gdrcd_encript('test') . "', NULL, now(), '".gdrcd_encript('test@gdrcd.test')."', 0, 1, -1, '2009-07-01', '2009-07-01', '', '', 'm', 1000, '', '', 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', 0, 1000, 7, 8, 6, 5, 6, 5, 100, 100, '2009-07-01 00:00:00', 50, 50, '2009-07-01', '127.0.0.1', 0, '2009-07-01 00:00:00', '2009-07-01 00:00:00', '2009-07-01 00:00:00', 1, 0);");
    
    
        gdrcd_query("CREATE TABLE razza (
            id_razza int(4) NOT NULL auto_increment,
            nome_razza char(50) NOT NULL default '',
            sing_m char(50) NOT NULL default '',
            sing_f char(50) NOT NULL default '',
            descrizione text NOT NULL,
            bonus_car0 int(4) NOT NULL default '0',
            bonus_car1 int(4) NOT NULL default '0',
            bonus_car2 int(4) NOT NULL default '0',
            bonus_car3 int(4) NOT NULL default '0',
            bonus_car4 int(4) NOT NULL default '0',
            bonus_car5 int(4) NOT NULL default '0',
            immagine char(50) NOT NULL default 'standard_razza.png',
            icon varchar(50) NOT NULL default 'standard_razza.png',
            url_site char(255) default NULL,
            iscrizione tinyint(1) NOT NULL default '1',
            visibile tinyint(1) NOT NULL default '1',
            PRIMARY KEY  (id_razza)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("INSERT INTO razza VALUES (1000, 'Umani', 'Umano', 'Umana', '', 0, 0, 0, 0, 0, 0, 'standard_razza.png', 'standard_razza.png', '', 1, 1);");
    
        gdrcd_query("CREATE TABLE regolamento (
            articolo int(2) NOT NULL,
            titolo varchar(30) NOT NULL,
            testo text NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
        gdrcd_query("CREATE TABLE ruolo (
            id_ruolo int(4) NOT NULL auto_increment,
            gilda int(4) NOT NULL default '-1',
            nome_ruolo char(50) NOT NULL,
            immagine varchar(256) NOT NULL,
            stipendio int(4) NOT NULL default '0',
            capo int(1) NOT NULL default '0',
            PRIMARY KEY  (id_ruolo)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    
        gdrcd_query("INSERT INTO ruolo VALUES (1, 1, 'Capitano della guardia', 'standard_gilda.png', 100, 1);");
    
        gdrcd_query("INSERT INTO ruolo VALUES (2, 1, 'Ufficiale della guardia', 'standard_gilda.png', 70, 0);");
    
        gdrcd_query("INSERT INTO ruolo VALUES (5, -1, 'Lavoratore', 'standard_gilda.png', 5, 0);");
    
        gdrcd_query("INSERT INTO ruolo VALUES (3, 1, 'Soldato della guardia', 'standard_gilda.png', 40, 0);");
    
        gdrcd_query("INSERT INTO ruolo VALUES (4, 1, 'Recluta della guardia', 'standard_gilda.png', 15, 0);");
    }
    
    /**
     * @inheritDoc
     */
    public function down()
    {
    
    }
}
