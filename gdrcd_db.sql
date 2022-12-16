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

--
-- Database: `gdrcd`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `abilita`
--

CREATE TABLE IF NOT EXISTS `abilita` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `descrizione` text NOT NULL,
  `statistica` tinyint(1) NOT NULL DEFAULT '0',
  `razza` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `ambientazione`
--

CREATE TABLE IF NOT EXISTS `ambientazione` (
  `capitolo` int NOT NULL,
  `testo` text NOT NULL,
  `titolo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `contatti`
--

CREATE TABLE `contatti` (
   `id` INT(11) NOT NULL AUTO_INCREMENT,
   `personaggio` INT(11) NOT NULL DEFAULT '0',
   `contatto` BIGINT(20) NOT NULL DEFAULT '0',
   `categoria` VARCHAR(255) NOT NULL,
   `creato_il` DATE NOT NULL,
   `creato_da` VARCHAR(255) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `contatti_nota`
--

CREATE TABLE `contatti_nota` (
   `id` INT(11) NOT NULL AUTO_INCREMENT,
   `id_contatto` INT(11) NOT NULL DEFAULT '0',
   `titolo` VARCHAR(250) NULL DEFAULT NULL,
   `nota` TEXT NOT NULL,
   `pubblica` VARCHAR(50) NULL DEFAULT NULL,
   `eliminato` INT(11) NOT NULL DEFAULT '0',
   `creato_il` DATETIME NOT NULL,
   `creato_da` VARCHAR(255) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `contatti_nota`
--

CREATE TABLE `contatti_categorie` (
   `id` INT(11) NOT NULL AUTO_INCREMENT,
   `nome` VARCHAR(255) NOT NULL DEFAULT '0',
   `creato_il` DATETIME NOT NULL,
   `creato_da` VARCHAR(255) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `chat_opzioni`
--

CREATE TABLE IF NOT EXISTS `chat_opzioni` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NOT NULL,
    `titolo` varchar(255) NOT NULL,
    `descrizione` text DEFAULT NULL,
    `tipo` varchar(255) DEFAULT 'String',
    `creato_da` int DEFAULT NULL,
    `creato_il` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `options` varchar(255) DEFAULT NULL,
  `editable` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `const_name` (`const_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `config`
--

CREATE TABLE IF NOT EXISTS `config_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `cronjob`
--

CREATE TABLE IF NOT EXISTS `cronjob` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL DEFAULT '0',
    `last_exec` datetime DEFAULT NULL,
    `in_exec` varchar(255) NOT NULL DEFAULT '',
    `interval` int NOT NULL DEFAULT 60,
    `interval_type` varchar(255) NOT NULL DEFAULT 'minutes',
    `class` varchar(255) NOT NULL,
    `function` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `diario`
--

CREATE TABLE IF NOT EXISTS `diario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `personaggio` varchar(255) DEFAULT NULL,
  `titolo` varchar(255) NOT NULL DEFAULT '',
  `testo` text NOT NULL,
  `data` date NOT NULL,
  `data_inserimento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_modifica` datetime DEFAULT NULL,
  `visibile` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Struttura della tabella `disponibilita`
--

CREATE TABLE IF NOT EXISTS `disponibilita` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `immagine` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `esiti`
--

CREATE TABLE IF NOT EXISTS `esiti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `autore` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `titolo` text COLLATE utf8mb4_general_ci NOT NULL,
  `data` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `master` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `closed` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `esiti_risposte_letture`
--

CREATE TABLE IF NOT EXISTS `esiti_risposte_letture` (
    `id` int NOT NULL AUTO_INCREMENT,
    `esito` int NOT NULL,
    `personaggio` int NOT NULL,
    `letto_il` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `esito_personaggio` (`esito`,`personaggio`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

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
    PRIMARY KEY (`id`),
    UNIQUE KEY `personaggio` (`personaggio`,`esito`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `forum`
--

CREATE TABLE IF NOT EXISTS `forum` (
    `id` int NOT NULL AUTO_INCREMENT,
    `tipo` int NOT NULL DEFAULT '0',
    `nome` varchar(255) DEFAULT NULL,
    `descrizione` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `forum`
--

CREATE TABLE IF NOT EXISTS `forum_permessi` (
    `id` int NOT NULL AUTO_INCREMENT,
    `forum` int NOT NULL,
    `personaggio` int NOT NULL,
    `assegnato_da` int DEFAULT NULL,
    `assegnato_il` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `forum_post`
--

CREATE TABLE IF NOT EXISTS `forum_posts` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_padre` bigint NOT NULL DEFAULT '0',
    `id_forum` int DEFAULT NULL,
    `titolo` varchar(255) DEFAULT NULL,
    `testo` text,
    `autore` INT,
    `data` datetime DEFAULT CURRENT_TIMESTAMP,
    `data_ultimo` datetime DEFAULT CURRENT_TIMESTAMP,
    `importante` tinyint(1) NOT NULL DEFAULT 0,
    `chiuso` tinyint(1) NOT NULL DEFAULT 0,
    `eliminato` tinyint(1) NOT NULL DEFAULT 0,
    `modificato_il` datetime DEFAULT NULL,
    `modificato_da` int DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `forum_letto`
--

CREATE TABLE IF NOT EXISTS `forum_posts_letti` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `personaggio` varchar(255) DEFAULT NULL,
    `post` int NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `personaggio_post` (`personaggio`,`post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `forum_posts_updates`
--

CREATE TABLE IF NOT EXISTS `forum_posts_updates` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `post` int NOT NULL,
    `titolo` varchar(255),
    `testo` text,
    `modificato_il` datetime DEFAULT CURRENT_TIMESTAMP,
    `modificato_da`int DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `forum_tipo`
--

CREATE TABLE IF NOT EXISTS `forum_tipo` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(255),
    `descrizione` varchar(255) DEFAULT NULL,
    `pubblico` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `lavori`
--

CREATE TABLE IF NOT EXISTS `lavori` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NOT NULL,
    `descrizione` text NOT NULL,
    `immagine` varchar(255) NOT NULL,
    `stipendio` int NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `giocate_registrate`
--

CREATE TABLE IF NOT EXISTS `giocate_registrate` (
    `id` int NOT NULL AUTO_INCREMENT,
    `autore` int NOT NULL,
    `chat` int NOT NULL,
    `titolo` varchar(255) NOT NULL,
    `nota` text DEFAULT NULL,
    `quest` int DEFAULT NULL ,
    `inizio` datetime NOT NULL,
    `fine` datetime NOT NULL,
    `bloccata` tinyint(1) DEFAULT 0,
    `controllata` tinyint(1) DEFAULT 0,
    `creato_il` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `gruppi`
--

CREATE TABLE IF NOT EXISTS `gruppi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL DEFAULT '',
  `tipo` varchar(255) NOT NULL DEFAULT '0',
  `immagine` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `statuto` text,
  `denaro` int NOT NULL DEFAULT 0,
  `visibile` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Struttura della tabella `gruppi_fondi`
--

CREATE TABLE IF NOT EXISTS `gruppi_fondi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL DEFAULT '',
  `gruppo` int NOT NULL,
  `denaro` int DEFAULT NULL,
  `interval` int DEFAULT NULL,
  `interval_type` varchar(255) NOT NULL,
  `last_exec` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `gruppi_fondi`
--

CREATE TABLE IF NOT EXISTS `gruppi_oggetto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gruppo` int NOT NULL,
  `oggetto` int DEFAULT NULL,
  `cariche` int DEFAULT 0,
  `commento` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `gruppi_ruoli`
--

CREATE TABLE IF NOT EXISTS `gruppi_ruoli` (
    `id` int NOT NULL AUTO_INCREMENT,
    `gruppo` int NOT NULL DEFAULT '-1',
    `nome` varchar(255) NOT NULL,
    `immagine` varchar(255) NOT NULL,
    `stipendio` int NOT NULL DEFAULT '0',
    `poteri` int NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio_ruolo`
--

CREATE TABLE IF NOT EXISTS `gruppi_stipendi_extra` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NOT NULL,
    `personaggio` varchar(255) NOT NULL,
    `gruppo` int NOT NULL,
    `valore` int NOT NULL,
    `interval` int NOT NULL DEFAULT 60,
    `interval_type` varchar(255) NOT NULL DEFAULT 'minutes',
    `last_exec` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-----------------------------------------------------------

--
-- Struttura della tabella `gruppi_tipo`
--

CREATE TABLE IF NOT EXISTS `gruppi_tipo` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NOT NULL,
    `descrizione` text NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `autore` varchar(255) NOT NULL DEFAULT '',
  `destinatario` varchar(255) NOT NULL DEFAULT '',
  `tipo` varchar(255) NOT NULL DEFAULT '',
  `testo` varchar(255) NOT NULL DEFAULT '',
  `creato_il` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    `permission` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `page` (`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `meteo_city` varchar(255) DEFAULT NULL,
  `meteo_fisso` varchar(255) DEFAULT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `mercato`
--

CREATE TABLE IF NOT EXISTS `mercato` (
    `id` int NOT NULL AUTO_INCREMENT,
    `oggetto` int NOT NULL,
    `negozio` int NOT NULL,
    `costo` int NOT NULL,
    `quantity` int NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `mercato_negozi`
--

CREATE TABLE IF NOT EXISTS `mercato_negozi` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NOT NULL,
    `descrizione` TEXT,
    `immagine` varchar(255),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `meteo_chat`
--

CREATE TABLE IF NOT EXISTS `meteo_chat` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_chat` int(11) NOT NULL,
    `meteo` varchar(255) DEFAULT NULL,
    `vento` varchar(255) DEFAULT NULL,
    `temp` int DEFAULT NULL,
    `img` varchar(255) DEFAULT NULL,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `meteo_mappa`
--

CREATE TABLE IF NOT EXISTS `meteo_mappa` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_mappa` int(11) NOT NULL,
    `meteo` varchar(255) DEFAULT NULL,
    `vento` varchar(255) DEFAULT NULL,
    `temp` int DEFAULT NULL,
    `img` varchar(255) DEFAULT NULL,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `meteo_condizioni`
--

CREATE TABLE IF NOT EXISTS `meteo_condizioni` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) DEFAULT NULL,
    `img` varchar(255) DEFAULT NULL,
    `vento` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `meteo_stagioni`
--

CREATE TABLE `meteo_stagioni` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(255) NULL DEFAULT NULL,
    `minima` INT NULL,
    `massima` INT NULL,
    `data_inizio` DATE NULL DEFAULT NULL,
    `data_fine` DATE NULL DEFAULT NULL,
    `alba` TIME NULL DEFAULT NULL,
    `tramonto` TIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `meteo_stagioni_condizioni`
--

CREATE TABLE `meteo_stagioni_condizioni` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `stagione` INT NULL,
    `condizione` INT NULL,
    `percentuale` INT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `meteo_venti`
--

CREATE TABLE `meteo_venti` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `meteo_venti`
--

CREATE TABLE `news` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `autore` VARCHAR(255) NULL DEFAULT NULL,
    `titolo` varchar(255) NULL,
    `testo` text NULL,
    `tipo` int NOT NULL,
    `attiva` tinyint(1) NOT NULL DEFAULT 1,
    `creata_il` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `creata_da` int DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `meteo_venti`
--

CREATE TABLE `news_lette` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `personaggio` INT NOT NULL ,
    `news` INT NULL,
    `letto_il` datetime DEFAULT CURRENT_TIMESTAMP ,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `pg_letto` (`personaggio`, `news`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `meteo_venti`
--

CREATE TABLE `news_tipo` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NULL,
    `descrizione` text NULL,
    `attiva` tinyint(1) NOT NULL DEFAULT 1,
    `creata_il` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `creata_da` int DEFAULT NULL,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `oggetto`
--

CREATE TABLE IF NOT EXISTS `oggetto` (
    `id` int NOT NULL AUTO_INCREMENT,
    `tipo` int NOT NULL DEFAULT '0',
    `nome` varchar(255) NOT NULL,
    `descrizione` text DEFAULT NULL,
    `immagine` varchar(255) DEFAULT NULL,
    `indossabile` int NOT NULL DEFAULT '0',
    `posizione` int NOT NULL DEFAULT '0',
    `cariche` varchar(255) NOT NULL DEFAULT '0',
    `creato_da` varchar(255) NOT NULL DEFAULT 'System',
    `creato_il` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `oggetto_posizioni`
--

CREATE TABLE IF NOT EXISTS `oggetto_posizioni` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NOT NULL,
    `immagine` text DEFAULT NULL,
    `numero` int DEFAULT '1',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `oggetto_statistiche`
--

CREATE TABLE IF NOT EXISTS `oggetto_statistiche` (
    `id` int NOT NULL AUTO_INCREMENT,
    `oggetto` int NOT NULL,
    `statistica` int NOT NULL,
    `valore` int NOT NULL,
    `creato_da` int DEFAULT NULL,
    `creato_il` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `oggetto_statistica` (`oggetto`, `statistica`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `oggetto_tipo`
--

CREATE TABLE IF NOT EXISTS `oggetto_tipo` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NOT NULL,
    `descrizione` text DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `online_status`
--

CREATE TABLE `online_status` (
    `id` int NOT NULL AUTO_INCREMENT,
    `type` varchar(255) NOT NULL,
    `text` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `pages`
--

CREATE TABLE `pages` (
    `id` int NOT NULL AUTO_INCREMENT,
    `page` varchar(255) NOT NULL,
    `redirect` text NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `page` (`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `pages_alias`
--

CREATE TABLE `pages_alias` (
    `id` int NOT NULL AUTO_INCREMENT,
    `alias` varchar(255) NOT NULL,
    `redirect` text NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `permessi_custom`
--

CREATE TABLE `permessi_custom` (
    `id` int NOT NULL AUTO_INCREMENT,
    `permission_name` varchar(255) NOT NULL,
    `description` text NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `permessi_group_assignment`
--

CREATE TABLE `permessi_group_assignment` (
    `id` int NOT NULL AUTO_INCREMENT,
    `group_id` int NOT NULL,
    `permission` int NOT NULL,
    `assigned_by` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `group_permission` (`group_id`, `permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `permessi_group_personaggio`
--

CREATE TABLE `permessi_group_personaggio` (
    `id` int NOT NULL AUTO_INCREMENT,
    `group_id` int NOT NULL,
    `personaggio` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `group_personaggio` (`group_id`, `personaggio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `permessi_personaggio`
--

CREATE TABLE `permessi_personaggio` (
    `id` int NOT NULL AUTO_INCREMENT,
    `personaggio` varchar(255) NOT NULL,
    `permission` int NOT NULL,
    `assigned_by` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `personaggio_permission` (`personaggio`, `permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio`
--

CREATE TABLE IF NOT EXISTS `personaggio` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NOT NULL DEFAULT '',
    `cognome` varchar(255) NOT NULL DEFAULT '-',
    `email` varchar(255) DEFAULT NULL,
    `pass` varchar(255) NOT NULL DEFAULT '',
    `permessi` tinyint(1) DEFAULT '0',
    `descrizione` text,
    `affetti` text,
    `storia` text,
    `stato` varchar(255) DEFAULT 'nessuna',
    `url_img` varchar(255) DEFAULT 'imgs/avatars/empty.png',
    `url_img_chat` varchar(255) NOT NULL DEFAULT ' ',
    `url_media` varchar(255) DEFAULT NULL,
    `online_status` varchar(255) DEFAULT NULL,
    `ultima_mappa` int NOT NULL DEFAULT '1',
    `ultimo_luogo` int NOT NULL DEFAULT '-1',
    `posizione` int NOT NULL DEFAULT '1',
    `sesso` varchar(255) DEFAULT 'm',
    `razza` int DEFAULT '1000',
    `soldi` int DEFAULT '50',
    `banca` int DEFAULT '0',
    `salute` int NOT NULL DEFAULT '100',
    `salute_max` int NOT NULL DEFAULT '100',
    `esperienza` decimal(12,4) DEFAULT '0.0000',
    `blocca_media` binary(1) NOT NULL DEFAULT '0',
    `is_invisible` tinyint(1) NOT NULL DEFAULT '0',
    `disponibile` tinyint(1) NOT NULL DEFAULT '1',
    `ultimo_cambiopass` datetime DEFAULT NULL,
    `ultimo_refresh` datetime NOT NULL DEFAULT '2009-07-01 00:00:00',
    `ora_entrata` datetime NOT NULL DEFAULT '2009-07-01 00:00:00',
    `ora_uscita` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `esilio` date NOT NULL DEFAULT '2009-07-01',
    `data_esilio` date NOT NULL DEFAULT '2009-07-01',
    `motivo_esilio` varchar(255) DEFAULT NULL,
    `autore_esilio` varchar(255) DEFAULT NULL,
    `online_last_refresh` DATETIME DEFAULT NULL,
    `data_iscrizione` datetime DEFAULT NULL,
    `last_ip` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio_abilita`
--

CREATE TABLE IF NOT EXISTS `personaggio_abilita` (
    `id` int NOT NULL AUTO_INCREMENT,
    `personaggio` int NOT NULL,
    `abilita` int NOT NULL,
    `grado` int NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `pg_ability` (`personaggio`, `abilita`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio_chat_opzioni`
--

CREATE TABLE IF NOT EXISTS `personaggio_chat_opzioni` (
    `id` int NOT NULL AUTO_INCREMENT,
    `personaggio` int NOT NULL,
    `opzione` varchar(255) NOT NULL,
    `valore` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `pg_chat_option` (`personaggio`, `opzione`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio_lavoro`
--

CREATE TABLE IF NOT EXISTS `personaggio_lavoro` (
    `id` int NOT NULL AUTO_INCREMENT,
    `personaggio` varchar(255) NOT NULL,
    `lavoro` int NOT NULL,
    `scadenza` date NOT NULL DEFAULT '2010-01-01',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `pg_work` (`personaggio`, `lavoro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio_oggetto`
--

CREATE TABLE `personaggio_oggetto` (
    `id` int NOT NULL AUTO_INCREMENT,
    `personaggio` int NOT NULL,
    `oggetto` int NOT NULL,
    `cariche` int DEFAULT 0,
    `commento` text DEFAULT NULL,
    `indossato` int DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio_online_status`
--

CREATE TABLE `personaggio_online_status` (
    `id` int NOT NULL AUTO_INCREMENT,
    `personaggio` varchar(255) NOT NULL,
    `type` int NOT NULL,
    `value` varchar(255) NOT NULL,
    `last_refresh` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `pg_status_online` (`personaggio`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio_ruolo`
--

CREATE TABLE IF NOT EXISTS `personaggio_ruolo` (
    `id` int NOT NULL AUTO_INCREMENT,
    `personaggio` varchar(255) NOT NULL,
    `ruolo` int NOT NULL,
    `scadenza` date NOT NULL DEFAULT '2010-01-01',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `pg_role` (`personaggio`, `ruolo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------

--
-- Struttura della tabella `personaggio_statistiche`
--

CREATE TABLE `personaggio_statistiche` (
    `id` int NOT NULL AUTO_INCREMENT,
    `personaggio` int NOT NULL,
    `statistica` int NOT NULL,
    `valore` int DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `pg_stat` (`personaggio`, `statistica`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `razza`
--

CREATE TABLE IF NOT EXISTS `razze` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL DEFAULT '',
  `sing_m` varchar(255) NOT NULL DEFAULT '',
  `sing_f` varchar(255) NOT NULL DEFAULT '',
  `descrizione` text NOT NULL,
  `immagine` varchar(255) NOT NULL DEFAULT 'races/standard_razza.png',
  `icon` varchar(255) NOT NULL DEFAULT 'races/standard_razza.png',
  `url_site` varchar(255) DEFAULT NULL,
  `iscrizione` tinyint(1) NOT NULL DEFAULT '1',
  `visibile` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `regolamento`
--

CREATE TABLE IF NOT EXISTS `regolamento` (
  `articolo` int NOT NULL,
  `titolo` varchar(255) NOT NULL,
  `testo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `sessi`
--

CREATE TABLE IF NOT EXISTS `sessi` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `immagine` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `statistiche`
--

CREATE TABLE IF NOT EXISTS `statistiche` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `max_val` int NOT NULL,
  `min_val` int NOT NULL,
  `descrizione` text DEFAULT NULL,
  `iscrizione` bool DEFAULT 0,
  `creato_da` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

--
-- Struttura della tabella `_gdrcd_db_versions`
--

CREATE TABLE IF NOT EXISTS _gdrcd_db_versions (
  `migration_id` varchar(250) NOT NULL,
  `applied_on` DATETIME NOT NULL ,
  PRIMARY KEY (`migration_id`)
);
