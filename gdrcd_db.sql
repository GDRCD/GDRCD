-- phpMyAdmin SQL Dump
-- version 4.1.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Ott 08, 2021 alle 00:28
-- Versione del server: 8.0.21
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `my_giovannipaneselling`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `abilita`
--

CREATE TABLE IF NOT EXISTS `abilita` (
  `id_abilita` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `car` tinyint(1) NOT NULL DEFAULT '0',
  `descrizione` text NOT NULL,
  `id_razza` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_abilita`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `abilita`
--

INSERT INTO `abilita` (`id_abilita`, `nome`, `car`, `descrizione`, `id_razza`) VALUES
(18, 'Resistenza', 1, 'Il personaggio Ã¨ in grado di sopportare il dolore ed il disagio e sopporta minime dosi di agenti tossici nel proprio organismo. ', -1),
(17, 'Sopravvivenza', 4, 'Il personaggio Ã¨ in grado di procurarsi cibo e riparo all''aperto, con mezzi minimi.', -1),
(4, 'Atletica', 2, 'Il personaggio Ã¨ ben allenato ed Ã¨ in grado di saltare efficacemente, arrampicarsi, nuotare, schivare e compiere, genericamente, movimenti fisicamente impegnativi.', -1),
(5, 'Cercare', 5, 'Il personaggio Ã¨ rapido ed efficace nel perquisire un ambiente in cerca di qualcosa.', -1),
(6, 'Conoscenza', 3, 'Il personaggio ha accumulato cultura ed esperienze, e potrebbe avere maggiori informazioni sulla situazione in cui si trova. A fronte di una prova di conoscenza il master dovrebbe fornire informazioni al giocatore via sussurro.', -1),
(7, 'Percepire intenzioni', 4, 'Il personaggio Ã¨ abile nel determinare, durante una conversazione o un interazione, se il suo interlocutore stia mentendo, sia ostile o sia ben disposto.', -1),
(8, 'Cavalcare', 2, 'Il personaggio Ã¨ in grado di cavalcare animali addestrati a tale scopo.', -1),
(9, 'Addestrare animali', 4, 'Il personaggio comprende gli atteggiamenti e le reazioni degli animali ed Ã¨ in grado di interagire con loro, addomesticarli ed addestrarli.', -1),
(10, 'Armi bianche', 0, 'Il personaggio Ã¨ addestrato al combattimento con armi bianche, scudi e protezioni.', -1),
(11, 'Armi da tiro', 5, 'Il personaggio Ã¨ addestrato all''uso di armi da diro o da lancio.', -1),
(12, 'Lotta', 0, 'Il personaggio Ã¨ addestrato al combattimento senza armi.', -1),
(13, 'Competenze tecniche', 3, 'Il personaggio Ã¨ in grado di realizzare e riparare strumenti tecnologici. Il tipo ed il numero di tecnologie in cui Ã¨ competente dovrebbe essere specificato nel background e proporzionale al punteggio di intelligenza.', -1),
(14, 'Mezzi di trasporto', 5, 'Il personaggio Ã¨ in grado di governare o pilotare specifici mezzi di trasporto. L''elenco dei mezzi dovrebbe essere riportato nel background e proporzionale al punteggio di intelligenza.', -1),
(15, 'Pronto soccorso', 3, 'Il personaggio Ã¨ in grado di eseguire interventi d''emergenza su individui feriti o la cui salute sia in qualche modo minacciata.', -1),
(16, 'FurtivitÃ ', 2, 'Il personaggio Ã¨ in grado di muoversi ed agire senza dare nell''occhio, e di scassinare serrature.', -1),
(19, 'VolontÃ ', 4, 'Il personaggio Ã¨ fortemente determinato e difficilmente si lascia persuadere o dissuadere.', -1);

-- --------------------------------------------------------

--
-- Struttura della tabella `abilita_extra`
--

CREATE TABLE IF NOT EXISTS `abilita_extra` (
  `id` int NOT NULL AUTO_INCREMENT,
  `abilita` int NOT NULL,
  `grado` int NOT NULL,
  `descrizione` text NOT NULL,
  `costo` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `abilita_requisiti`
--

CREATE TABLE IF NOT EXISTS `abilita_requisiti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `abilita` int NOT NULL,
  `grado` int NOT NULL,
  `tipo` int NOT NULL,
  `id_riferimento` int NOT NULL,
  `liv_riferimento` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Struttura della tabella `ambientazione`
--

CREATE TABLE IF NOT EXISTS `ambientazione` (
  `capitolo` int NOT NULL,
  `testo` text NOT NULL,
  `titolo` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `araldo`
--

CREATE TABLE IF NOT EXISTS `araldo` (
  `id_araldo` int NOT NULL AUTO_INCREMENT,
  `tipo` int NOT NULL DEFAULT '0',
  `nome` varchar(255) DEFAULT NULL,
  `proprietari` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_araldo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `araldo`
--

INSERT INTO `araldo` (`id_araldo`, `tipo`, `nome`, `proprietari`) VALUES
(1, 4, 'Resoconti quest', 0),
(2, 0, 'Notizie in gioco', 0),
(3, 2, 'Umani', 1000),
(4, 3, 'Ordini alla Guardia', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `araldo_letto`
--

CREATE TABLE IF NOT EXISTS `araldo_letto` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `araldo_id` int NOT NULL,
  `thread_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nome` (`nome`,`thread_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `backmessaggi`
--

CREATE TABLE IF NOT EXISTS `backmessaggi` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `mittente` varchar(255) NOT NULL DEFAULT '',
  `destinatario` varchar(255) NOT NULL DEFAULT '',
  `spedito` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `letto` tinyint(1) DEFAULT '0',
  `tipo` int NOT NULL DEFAULT '0',
  `oggetto` text,
  `testo` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `blacklist`
--

CREATE TABLE IF NOT EXISTS `blacklist` (
  `ip` varchar(255) NOT NULL DEFAULT '',
  `nota` varchar(255) DEFAULT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT '0',
  `ora` datetime DEFAULT NULL,
  `host` varchar(255) NOT NULL DEFAULT '-',
  PRIMARY KEY (`ip`),
  KEY `Ora` (`ora`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `chat`
--

CREATE TABLE IF NOT EXISTS `chat` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `stanza` int NOT NULL DEFAULT '0',
  `imgs` varchar(255) NOT NULL DEFAULT '',
  `mittente` varchar(255) NOT NULL DEFAULT '',
  `destinatario` varchar(255) DEFAULT NULL,
  `ora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo` varchar(255) DEFAULT NULL,
  `testo` text,
  PRIMARY KEY (`id`),
  KEY `Stanza` (`stanza`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `clgpersonaggioabilita`
--

CREATE TABLE IF NOT EXISTS `clgpersonaggioabilita` (
  `nome` varchar(255) NOT NULL,
  `id_abilita` int NOT NULL,
  `grado` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `clgpersonaggiomostrine`
--

CREATE TABLE IF NOT EXISTS `clgpersonaggiomostrine` (
  `id_mostrina` varchar(255) NOT NULL DEFAULT '',
  `nome` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_mostrina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `clgpersonaggiooggetto`
--

CREATE TABLE IF NOT EXISTS `clgpersonaggiooggetto` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL DEFAULT '',
  `id_oggetto` int NOT NULL DEFAULT '0',
  `numero` int DEFAULT '1',
  `cariche` int NOT NULL DEFAULT '-1',
  `commento` varchar(255) DEFAULT NULL,
  `posizione` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `clgpersonaggioruolo`
--

CREATE TABLE IF NOT EXISTS `clgpersonaggioruolo` (
  `personaggio` varchar(255) NOT NULL,
  `id_ruolo` int NOT NULL DEFAULT '0',
  `scadenza` date NOT NULL DEFAULT '2010-01-01'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `codmostrina`
--

CREATE TABLE IF NOT EXISTS `codmostrina` (
  `id_mostrina` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `img_url` varchar(255) NOT NULL DEFAULT 'grigia.gif',
  `descrizione` varchar(255) NOT NULL DEFAULT 'nessuna',
  PRIMARY KEY (`id_mostrina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `codtipogilda`
--

CREATE TABLE IF NOT EXISTS `codtipogilda` (
  `descrizione` varchar(255) NOT NULL,
  `cod_tipo` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`cod_tipo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `codtipogilda`
--

INSERT INTO `codtipogilda` (`descrizione`, `cod_tipo`) VALUES
('Positivo', 1),
('Neutrale', 2),
('Negativo', 3);

-- --------------------------------------------------------

--
-- Struttura della tabella `codtipooggetto`
--

CREATE TABLE IF NOT EXISTS `codtipooggetto` (
  `cod_tipo` int NOT NULL AUTO_INCREMENT,
  `descrizione` text NOT NULL,
  PRIMARY KEY (`cod_tipo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `codtipooggetto`
--

INSERT INTO `codtipooggetto` (`cod_tipo`, `descrizione`) VALUES
(1, 'Animale'),
(2, 'Vestito'),
(3, 'Fiore - Pianta'),
(4, 'Gioiello'),
(5, 'Arma'),
(6, 'Attrezzo'),
(0, 'Vario');

-- --------------------------------------------------------

--
-- Struttura della tabella `config`
--
CREATE TABLE IF NOT EXISTS `config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `const_name` varchar(255) NOT NULL,
  `val` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `section` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(255) DEFAULT 'String',
  `editable` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO `config` (`const_name`,`val`,`section`,`label`,`description`,`type`,`editable`) VALUES
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
    ('CHAT_SAVE_DOWNLOAD',1,'Chat Salvataggio','Salva chat download','Salva chat con download?','bool',1),
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
    ('ONLINE_STATUS_ENABLED',1,'Online Status','Stato online avanzato','Stato online avanzato,attivo?','bool',1);

-- --------------------------------------------------------

--
-- Struttura della tabella `diario`
--

CREATE TABLE IF NOT EXISTS `diario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `personaggio` varchar(255) DEFAULT NULL,
  `data` date NOT NULL,
  `data_inserimento` datetime NOT NULL,
  `data_modifica` datetime DEFAULT NULL,
  `visibile` varchar(255) NOT NULL,
  `titolo` varchar(255) NOT NULL DEFAULT '',
  `testo` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `esiti`
--

CREATE TABLE IF NOT EXISTS `esiti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `autore` varchar(255) COLLATE utf8_general_ci NOT NULL,
  `titolo` text COLLATE utf8_general_ci NOT NULL,
  `data` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `master` varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT '0',
  `closed` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Struttura della tabella `esiti_personaggio`
--

CREATE TABLE IF NOT EXISTS `esiti_personaggio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `personaggio` int NOT NULL,
  `esito` int NOT NULL,
  `assegnato_il` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assegnato_da` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `esiti_risposte`
--

CREATE TABLE IF NOT EXISTS `esiti_risposte` (
  `id` int NOT NULL AUTO_INCREMENT,
  `esito` int NOT NULL,
  `autore` varchar(255) NOT NULL,
  `chat` int NOT NULL DEFAULT '0',
  `contenuto` mediumtext NOT NULL,
  `data` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sent` int NOT NULL DEFAULT '0',
  `abilita` int DEFAULT '0',
  `dice_face` int NOT NULL DEFAULT '0',
  `dice_num` int NOT NULL DEFAULT '0',
  `modificatore` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Struttura della tabella `esiti_risposte_cd`
--

CREATE TABLE IF NOT EXISTS `esiti_risposte_cd` (
  `id` int NOT NULL AUTO_INCREMENT,
  `esito` int NOT NULL,
  `cd` int NOT NULL,
  `testo` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `esiti_risposte_letture`
--

CREATE TABLE IF NOT EXISTS `esiti_risposte_letture` (
    `id` int NOT NULL AUTO_INCREMENT,
    `esito` int NOT NULL,
    `personaggio` int NOT NULL,
    `letto_il` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `esiti_risposte_risultati`
--

CREATE TABLE IF NOT EXISTS `esiti_risposte_risultati` (
    `id` int NOT NULL AUTO_INCREMENT,
    `personaggio` int NOT NULL,
    `esito` int NOT NULL,
    `risultato` int NOT NULL,
    `testo` text DEFAULT NULL,
    `lanciato_il` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `gilda`
--

CREATE TABLE IF NOT EXISTS `gilda` (
  `id_gilda` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL DEFAULT '',
  `tipo` varchar(255) NOT NULL DEFAULT '0',
  `immagine` varchar(255) DEFAULT NULL,
  `url_sito` varchar(255) DEFAULT NULL,
  `statuto` text,
  `visibile` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_gilda`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `gilda`
--

INSERT INTO `gilda` (`id_gilda`, `nome`, `tipo`, `immagine`, `url_sito`, `statuto`, `visibile`) VALUES
(1, 'Guardia cittadina', '1', 'standard_gilda.png', '', '', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_interessato` varchar(255) NOT NULL DEFAULT '',
  `autore` varchar(255) NOT NULL DEFAULT '',
  `data_evento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codice_evento` varchar(255) NOT NULL DEFAULT '',
  `descrizione_evento` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `menu`
--

CREATE TABLE `menu` (
    `id` int NOT NULL AUTO_INCREMENT,
    `menu_name` varchar(255) NOT NULL,
    `section` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `page` varchar(255) NOT NULL,
    `permission` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `menu`
--

INSERT INTO `menu` (`menu_name`, `section`, `name`, `page`, `permission`) VALUES
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
  ('Gestione', 'Chat', 'Esiti in chat', 'gestione_esiti', 'MANAGE_OUTCOMES'),
  ('Gestione', 'Quest', 'Gestione Quest', 'gestione_quest', 'MANAGE_QUESTS'),
  ('Gestione', 'Quest', 'Gestione Trame', 'gestione_trame', 'MANAGE_TRAME_VIEW'),
  ('Gestione', 'Esiti', 'Esiti', 'gestione_esiti', 'MANAGE_ESITI'),
  ('Gestione', 'Stato Online', 'Gestione stati', 'gestione_stato_online', 'MANAGE_ONLINE_STATUS'),
  ('Gestione', 'Stato Online', 'Gestione tipi stati', 'gestione_tipo_stato_online', 'MANAGE_ONLINE_STATUS');

-- --------------------------------------------------------

--
-- Struttura della tabella `mappa`
--

CREATE TABLE IF NOT EXISTS `mappa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `descrizione` text,
  `stato` varchar(255) NOT NULL DEFAULT '',
  `pagina` varchar(255) DEFAULT 'nulla.php',
  `chat` tinyint(1) NOT NULL DEFAULT '1',
  `immagine` varchar(255) DEFAULT 'standard_luogo.png',
  `stanza_apparente` varchar(255) DEFAULT NULL,
  `id_mappa` int DEFAULT '0',
  `link_immagine` varchar(255) NOT NULL,
  `link_immagine_hover` varchar(255) NOT NULL,
  `id_mappa_collegata` int NOT NULL DEFAULT '0',
  `x_cord` int DEFAULT '0',
  `y_cord` int DEFAULT '0',
  `invitati` text NOT NULL,
  `privata` tinyint(1) NOT NULL DEFAULT '0',
  `proprietario` varchar(255) DEFAULT NULL,
  `ora_prenotazione` datetime DEFAULT NULL,
  `scadenza` datetime DEFAULT NULL,
  `costo` int DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `Invitati` (`invitati`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `mappa`
--

INSERT INTO `mappa` (`id`, `nome`, `descrizione`, `stato`, `pagina`, `chat`, `immagine`, `stanza_apparente`, `id_mappa`, `link_immagine`, `link_immagine_hover`, `id_mappa_collegata`, `x_cord`, `y_cord`, `invitati`, `privata`, `proprietario`, `ora_prenotazione`, `scadenza`, `costo`) VALUES
(1, 'Strada', 'Via che congiunge la periferia al centro.', 'Nella norma', '', 1, 'standard_luogo.png', '', 1, '', '', 0, 180, 150, '', 0, 'Nessuno', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 0),
(2, 'Piazza', 'Piccola piazza con panchine ed una fontana al centro.', 'Nella norma', '', 1, 'standard_luogo.png', '', 1, '', '', 0, 80, 150, '', 0, 'Nessuno', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `mappa_click`
--

CREATE TABLE IF NOT EXISTS `mappa_click` (
  `id_click` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `immagine` varchar(255) NOT NULL DEFAULT 'standard_mappa.png',
  `posizione` int NOT NULL DEFAULT '0',
  `mobile` tinyint(1) NOT NULL DEFAULT '0',
  `meteo` varchar(255) NOT NULL DEFAULT '20Â°c - sereno',
  `larghezza` smallint NOT NULL DEFAULT '500',
  `altezza` smallint NOT NULL DEFAULT '330',
  PRIMARY KEY (`id_click`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `mappa_click`
--

INSERT INTO `mappa_click` (`id_click`, `nome`, `immagine`, `posizione`, `mobile`, `meteo`, `larghezza`, `altezza`) VALUES
(1, 'Mappa principale', 'spacer.gif', 2, 0, '20Â°c - sereno', 500, 330),
(2, 'Mappa secondaria', 'spacer.gif', 2, 0, '18Â°c - nuvoloso', 500, 330);

-- --------------------------------------------------------

--
-- Struttura della tabella `mercato`
--

CREATE TABLE IF NOT EXISTS `mercato` (
  `id_oggetto` int NOT NULL,
  `numero` int DEFAULT '0',
  PRIMARY KEY (`id_oggetto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `messaggi`
--

CREATE TABLE IF NOT EXISTS `messaggi` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `mittente` varchar(255) NOT NULL,
  `destinatario` varchar(255) NOT NULL DEFAULT 'Nessuno',
  `spedito` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `letto` tinyint(1) DEFAULT '0',
  `mittente_del` tinyint(1) DEFAULT '0',
  `destinatario_del` tinyint(1) DEFAULT '0',
  `tipo` int NOT NULL DEFAULT '0',
  `oggetto` text,
  `testo` text,
  PRIMARY KEY (`id`),
  KEY `destinatario` (`destinatario`),
  KEY `letto` (`letto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `messaggioaraldo`
--

CREATE TABLE IF NOT EXISTS `messaggioaraldo` (
  `id_messaggio` bigint NOT NULL AUTO_INCREMENT,
  `id_messaggio_padre` bigint NOT NULL DEFAULT '0',
  `id_araldo` int DEFAULT NULL,
  `titolo` varchar(255) DEFAULT NULL,
  `messaggio` text,
  `autore` varchar(255) DEFAULT NULL,
  `data_messaggio` datetime DEFAULT NULL,
  `data_ultimo_messaggio` datetime DEFAULT NULL,
  `importante` binary(1) NOT NULL DEFAULT '0',
  `chiuso` binary(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_messaggio`),
  KEY `id_araldo` (`id_araldo`),
  KEY `id_messaggio_padre` (`id_messaggio_padre`),
  KEY `data_messaggio` (`data_messaggio`),
  KEY `importante` (`importante`,`chiuso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Struttura della tabella `oggetto`
--

CREATE TABLE IF NOT EXISTS `oggetto` (
  `id_oggetto` int NOT NULL AUTO_INCREMENT,
  `tipo` int NOT NULL DEFAULT '0',
  `nome` varchar(255) NOT NULL DEFAULT 'Sconosciuto',
  `creatore` varchar(255) NOT NULL DEFAULT 'System Op',
  `data_inserimento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `descrizione` varchar(255) NOT NULL DEFAULT 'Nessuna',
  `ubicabile` int NOT NULL DEFAULT '0',
  `costo` int NOT NULL DEFAULT '0',
  `difesa` int NOT NULL DEFAULT '0',
  `attacco` int NOT NULL DEFAULT '0',
  `cariche` varchar(255) NOT NULL DEFAULT '0',
  `bonus_car0` int NOT NULL DEFAULT '0',
  `bonus_car1` int NOT NULL DEFAULT '0',
  `bonus_car2` int NOT NULL DEFAULT '0',
  `bonus_car3` int NOT NULL DEFAULT '0',
  `bonus_car4` int NOT NULL DEFAULT '0',
  `bonus_car5` int NOT NULL DEFAULT '0',
  `urlimg` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_oggetto`),
  KEY `Tipo` (`tipo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `oggetto`
--

INSERT INTO `oggetto` (`id_oggetto`, `tipo`, `nome`, `creatore`, `data_inserimento`, `descrizione`, `ubicabile`, `costo`, `difesa`, `attacco`, `cariche`, `bonus_car0`, `bonus_car1`, `bonus_car2`, `bonus_car3`, `bonus_car4`, `bonus_car5`, `urlimg`) VALUES
(1, 6, 'Scopa', 'Super', '2009-12-20 14:29:33', 'Una comune scopa di saggina.', 0, 10, 0, 0, '0', 0, 0, 0, 0, 0, 0, 'standard_oggetto.png');

-- --------------------------------------------------------

--
-- Struttura della tabella `online_status`
--

CREATE TABLE `online_status` (
    `id` int NOT NULL AUTO_INCREMENT,
    `type` varchar(255) NOT NULL,
    `text` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `online_status_type`
--

CREATE TABLE `online_status_type` (
    `id` int NOT NULL AUTO_INCREMENT,
    `label` varchar(255) NOT NULL,
    `request` varchar(255) NOT NULL,
    `active` tinyint(1) DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `oggetto`
--

INSERT INTO `online_status_type`(`label`,`request`) VALUES
('Tempo Login','Tempo online?'),
('Tempo Azione','Tempo azione medio?');

-- --------------------------------------------------------

--
-- Struttura della tabella `permessi_custom`
--

CREATE TABLE `permessi_custom` (
    `id` int NOT NULL AUTO_INCREMENT,
    `permission_name` varchar(255) NOT NULL,
    `description` text NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `permessi_custom`
--

INSERT INTO `permessi_custom` (`permission_name`, `description`) VALUES
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
    ('MANAGE_ONLINE_STATUS','Permesso per la gestione degli status online');



-- --------------------------------------------------------

--
-- Struttura della tabella `permessi_group`
--

CREATE TABLE `permessi_group` (
    `id` int NOT NULL AUTO_INCREMENT,
    `group_name` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `superuser` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `permessi_group`
--

INSERT INTO `permessi_group` (`id`, `group_name`, `description`, `superuser`) VALUES
    (1, 'MASTER', 'Gruppo permessi master', 0),
    (2, 'MODERATOR', 'Gruppo permessi moderatore', 0),
    (3, 'SUPERUSER', 'Gruppo Permessi superuser', 1),
    (4, 'USER', 'Permessi gruppo user', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `permessi_group_assignment`
--

CREATE TABLE `permessi_group_assignment` (
    `id` int NOT NULL AUTO_INCREMENT,
    `group_id` int NOT NULL,
    `permission` int NOT NULL,
    `assigned_by` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `permessi_group_personaggio`
--

CREATE TABLE `permessi_group_personaggio` (
    `id` int NOT NULL AUTO_INCREMENT,
    `group_id` int NOT NULL,
    `personaggio` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `permessi_group_personaggio`
--

INSERT INTO `permessi_group_personaggio` (`id`, `group_id`, `personaggio`) VALUES
    (1, 3, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `permessi_personaggio`
--

CREATE TABLE `permessi_personaggio` (
    `id` int NOT NULL AUTO_INCREMENT,
    `personaggio` varchar(255) NOT NULL,
    `permission` int NOT NULL,
    `assigned_by` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio`
--

CREATE TABLE IF NOT EXISTS `personaggio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL DEFAULT '',
  `cognome` varchar(255) NOT NULL DEFAULT '-',
  `pass` varchar(255) NOT NULL DEFAULT '',
  `ultimo_cambiopass` datetime DEFAULT NULL,
  `data_iscrizione` datetime DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `permessi` tinyint(1) DEFAULT '0',
  `ultima_mappa` int NOT NULL DEFAULT '1',
  `ultimo_luogo` int NOT NULL DEFAULT '-1',
  `esilio` date NOT NULL DEFAULT '2009-07-01',
  `data_esilio` date NOT NULL DEFAULT '2009-07-01',
  `motivo_esilio` varchar(255) DEFAULT NULL,
  `autore_esilio` varchar(255) DEFAULT NULL,
  `sesso` varchar(255) DEFAULT 'm',
  `id_razza` int DEFAULT '1000',
  `descrizione` text,
  `affetti` text,
  `storia` text,
  `stato` varchar(255) DEFAULT 'nessuna',
  `online_status` varchar(255) DEFAULT NULL,
  `online_last_refresh` DATETIME DEFAULT NULL,
  `disponibile` tinyint(1) NOT NULL DEFAULT '1',
  `url_img` varchar(255) DEFAULT 'imgs/avatars/empty.png',
  `url_img_chat` varchar(255) NOT NULL DEFAULT ' ',
  `url_media` varchar(255) DEFAULT NULL,
  `blocca_media` binary(1) NOT NULL DEFAULT '0',
  `esperienza` decimal(12,4) DEFAULT '0.0000',
  `car0` int NOT NULL DEFAULT '5',
  `car1` int NOT NULL DEFAULT '5',
  `car2` int NOT NULL DEFAULT '5',
  `car3` int NOT NULL DEFAULT '5',
  `car4` int NOT NULL DEFAULT '5',
  `car5` int NOT NULL DEFAULT '5',
  `salute` int NOT NULL DEFAULT '100',
  `salute_max` int NOT NULL DEFAULT '100',
  `data_ultima_gilda` datetime NOT NULL DEFAULT '2009-07-01 00:00:00',
  `soldi` int DEFAULT '50',
  `banca` int DEFAULT '0',
  `ultimo_stipendio` date NOT NULL DEFAULT '2009-07-01',
  `last_ip` varchar(255) DEFAULT NULL,
  `is_invisible` tinyint(1) NOT NULL DEFAULT '0',
  `ultimo_refresh` datetime NOT NULL DEFAULT '2009-07-01 00:00:00',
  `ora_entrata` datetime NOT NULL DEFAULT '2009-07-01 00:00:00',
  `ora_uscita` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `posizione` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `IDRazza` (`id_razza`),
  KEY `Esilio` (`esilio`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `personaggio`
--


INSERT INTO `personaggio` (`id`,`nome`, `cognome`, `pass`, `ultimo_cambiopass`, `data_iscrizione`, `email`, `permessi`, `ultima_mappa`, `ultimo_luogo`, `esilio`, `data_esilio`, `motivo_esilio`, `autore_esilio`, `sesso`, `id_razza`, `descrizione`, `affetti`, `stato`, `online_status`, `disponibile`, `url_img`, `url_img_chat`, `url_media`, `blocca_media`, `esperienza`, `car0`, `car1`, `car2`, `car3`, `car4`, `car5`, `salute`, `salute_max`, `data_ultima_gilda`, `soldi`, `banca`, `ultimo_stipendio`, `last_ip`, `is_invisible`, `ultimo_refresh`, `ora_entrata`, `ora_uscita`, `posizione`) VALUES
(1,'Super', 'User', '$P$BcH1cP941XHOf0X61wVWWjzXqcCi2a/', NULL, '2011-06-04 00:47:48', '$P$BNZYtz9JOQE.O4Tv7qZyl3SzIoZzzR.', 5, 1, -1, '2009-01-01', '2009-01-01', '', '', 'm', 1000, '', '', 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', '0', '1000.0000', 7, 8, 6, 5, 6, 5, 100, 100, '2009-01-01 00:00:00', 300, 50000, '2009-01-01', '127.0.0.1', 0, '2021-10-08 00:28:13', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 1),
(2,'Test', 'Di FunzionaliÃ ', '$P$BUoa19QUuXsgIDlhGC3chR/3Q7hoRy0', NULL, '2011-06-04 00:47:48', '$P$Bd1amPCKkOF9GdgYsibZ96U92D5CtR0', 0, 1, -1, '2009-01-01', '2009-01-01', '', '', 'm', 1000, '', '', 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', '0', '1000.0000', 7, 8, 6, 5, 6, 5, 100, 100, '2009-01-01 00:00:00', 50, 50, '2009-01-01', '127.0.0.1', 0, '2009-01-01 00:00:00', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio_quest`
--

CREATE TABLE `personaggio_online_status` (
    `id` int NOT NULL AUTO_INCREMENT,
    `personaggio` varchar(255) NOT NULL,
    `type` int NOT NULL,
    `value` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio_quest`
--

CREATE TABLE personaggio_quest  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_quest` int NOT NULL,
  `data` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `commento` TEXT NOT NULL,
  `personaggio` int NOT NULL,
  `px_assegnati` int NOT NULL,
  `autore` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `quest`
--

CREATE TABLE IF NOT EXISTS  quest  (
  `id` int NOT NULL AUTO_INCREMENT,
  `titolo` text NOT NULL,
  `partecipanti` text NOT NULL,
  `descrizione` text NOT NULL,
  `trama` int NOT NULL DEFAULT '0',
  `data` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `autore` varchar(255) NOT NULL,
  `autore_modifica` varchar(255) DEFAULT NULL,
  `ultima_modifica` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `quest_trama`
--

CREATE TABLE IF NOT EXISTS  quest_trama  (
  `id` int NOT NULL AUTO_INCREMENT,
  `titolo` varchar(255) NOT NULL,
  `descrizione` text NOT NULL,
  `data` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `autore` varchar(255) NULL,
  `autore_modifica` varchar(255) DEFAULT NULL,
  `ultima_modifica` DATETIME DEFAULT NULL,
  `stato` int NOT NULL DEFAULT '0',
  `quests` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `razza`
--

CREATE TABLE IF NOT EXISTS `razza` (
  `id_razza` int NOT NULL AUTO_INCREMENT,
  `nome_razza` varchar(255) NOT NULL DEFAULT '',
  `sing_m` varchar(255) NOT NULL DEFAULT '',
  `sing_f` varchar(255) NOT NULL DEFAULT '',
  `descrizione` text NOT NULL,
  `bonus_car0` int NOT NULL DEFAULT '0',
  `bonus_car1` int NOT NULL DEFAULT '0',
  `bonus_car2` int NOT NULL DEFAULT '0',
  `bonus_car3` int NOT NULL DEFAULT '0',
  `bonus_car4` int NOT NULL DEFAULT '0',
  `bonus_car5` int NOT NULL DEFAULT '0',
  `immagine` varchar(255) NOT NULL DEFAULT 'standard_razza.png',
  `icon` varchar(255) NOT NULL DEFAULT 'standard_razza.png',
  `url_site` varchar(255) DEFAULT NULL,
  `iscrizione` tinyint(1) NOT NULL DEFAULT '1',
  `visibile` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_razza`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `razza`
--

INSERT INTO `razza` (`id_razza`, `nome_razza`, `sing_m`, `sing_f`, `descrizione`, `bonus_car0`, `bonus_car1`, `bonus_car2`, `bonus_car3`, `bonus_car4`, `bonus_car5`, `immagine`, `icon`, `url_site`, `iscrizione`, `visibile`) VALUES
(1000, 'Umani', 'Umano', 'Umana', '', 0, 0, 0, 0, 0, 0, 'standard_razza.png', 'standard_razza.png', '', 1, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `regolamento`
--

CREATE TABLE IF NOT EXISTS `regolamento` (
  `articolo` int NOT NULL,
  `titolo` varchar(255) NOT NULL,
  `testo` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `ruolo`
--

CREATE TABLE IF NOT EXISTS `ruolo` (
  `id_ruolo` int NOT NULL AUTO_INCREMENT,
  `gilda` int NOT NULL DEFAULT '-1',
  `nome_ruolo` varchar(255) NOT NULL,
  `immagine` varchar(255) NOT NULL,
  `stipendio` int NOT NULL DEFAULT '0',
  `capo` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_ruolo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `ruolo`
--

INSERT INTO `ruolo` (`id_ruolo`, `gilda`, `nome_ruolo`, `immagine`, `stipendio`, `capo`) VALUES
(1, 1, 'Capitano della guardia', 'standard_gilda.png', 100, 1),
(2, 1, 'Ufficiale della guardia', 'standard_gilda.png', 70, 0),
(5, -1, 'Lavoratore', 'standard_gilda.png', 5, 0),
(3, 1, 'Soldato della guardia', 'standard_gilda.png', 40, 0),
(4, 1, 'Recluta della guardia', 'standard_gilda.png', 15, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `segnalazione_role`
--

CREATE TABLE IF NOT EXISTS `segnalazione_role` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `stanza` int NOT NULL,
  `conclusa` int NOT NULL DEFAULT '0',
  `partecipanti` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `mittente` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `data_inizio` datetime DEFAULT NULL,
  `data_fine` datetime DEFAULT NULL,
  `tags` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `quest` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `send_GM`
--

CREATE TABLE IF NOT EXISTS `send_GM` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` datetime NOT NULL,
  `autore` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `role_reg` int NOT NULL,
  `note` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `_gdrcd_db_versions`
--

CREATE TABLE IF NOT EXISTS _gdrcd_db_versions (
  `migration_id` varchar(255) NOT NULL,
  `applied_on` DATETIME NOT NULL ,
  PRIMARY KEY (`migration_id`)
);

INSERT INTO _gdrcd_db_versions (migration_id,applied_on) VALUES
  ('2020072500', NOW()),
  ('2021103018',NOW()),
  ('2021110101',NOW()),
  ('2021110102',NOW()),
  ('2021110103',NOW()),
  ('2021110104',NOW()),
  ('2021110105',NOW()),
  ('2021110106',NOW()),
  ('2021110107',NOW()),
  ('2021110108',NOW()),
  ('2021110109',NOW());

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
