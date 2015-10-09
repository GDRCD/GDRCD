-- phpMyAdmin SQL Dump
-- version 3.3.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 04 giu, 2011 at 12:48 AM
-- Versione MySQL: 5.1.50
-- Versione PHP: 5.2.14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `new_gdrcd`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `abilita`
--

CREATE TABLE IF NOT EXISTS `abilita` (
  `id_abilita` int(4) NOT NULL AUTO_INCREMENT,
  `nome` varchar(20) NOT NULL,
  `car` tinyint(1) NOT NULL DEFAULT '0',
  `descrizione` text NOT NULL,
  `id_razza` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_abilita`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

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
-- Struttura della tabella `ambientazione`
--

CREATE TABLE IF NOT EXISTS `ambientazione` (
  `capitolo` int(2) NOT NULL,
  `testo` text NOT NULL,
  `titolo` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `ambientazione`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `araldo`
--

CREATE TABLE IF NOT EXISTS `araldo` (
  `id_araldo` int(4) NOT NULL AUTO_INCREMENT,
  `tipo` int(2) NOT NULL DEFAULT '0',
  `nome` varchar(50) DEFAULT NULL,
  `proprietari` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_araldo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

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
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) DEFAULT NULL,
  `araldo_id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE  `araldo_letto` ADD INDEX (  `nome` ,  `thread_id` ) ;


-- --------------------------------------------------------

--
-- Struttura della tabella `backmessaggi`
--

CREATE TABLE IF NOT EXISTS `backmessaggi` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `mittente` varchar(20) NOT NULL DEFAULT '',
  `destinatario` varchar(20) NOT NULL DEFAULT '',
  `spedito` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `letto` tinyint(1) DEFAULT '0',
  `testo` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `backmessaggi`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `blacklist`
--

CREATE TABLE IF NOT EXISTS `blacklist` (
  `ip` char(15) NOT NULL DEFAULT '',
  `nota` char(255) DEFAULT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT '0',
  `ora` datetime DEFAULT NULL,
  `host` char(255) NOT NULL DEFAULT '-',
  PRIMARY KEY (`ip`),
  KEY `Ora` (`ora`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `blacklist`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `chat`
--

CREATE TABLE IF NOT EXISTS `chat` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `stanza` int(4) NOT NULL DEFAULT '0',
  `imgs` varchar(100) NOT NULL DEFAULT '',
  `mittente` varchar(20) NOT NULL DEFAULT '',
  `destinatario` varchar(20) DEFAULT NULL,
  `ora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `tipo` char(1) DEFAULT NULL,
  `testo` text,
  PRIMARY KEY (`id`),
  KEY `Stanza` (`stanza`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `chat`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `clgpersonaggioabilita`
--

CREATE TABLE IF NOT EXISTS `clgpersonaggioabilita` (
  `nome` varchar(20) NOT NULL,
  `id_abilita` int(4) NOT NULL,
  `grado` int(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `clgpersonaggioabilita`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `clgpersonaggiomostrine`
--

CREATE TABLE IF NOT EXISTS `clgpersonaggiomostrine` (
  `nome` char(20) NOT NULL DEFAULT '',
  `id_mostrina` char(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`nome`,`id_mostrina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `clgpersonaggiomostrine`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `clgpersonaggiooggetto`
--

CREATE TABLE IF NOT EXISTS `clgpersonaggiooggetto` (
  `nome` varchar(20) NOT NULL DEFAULT '',
  `id_oggetto` int(4) NOT NULL DEFAULT '0',
  `numero` int(8) DEFAULT '1',
  `cariche` int(4) NOT NULL DEFAULT '-1',
  `commento` varchar(255) DEFAULT NULL,
  `posizione` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nome`,`id_oggetto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `clgpersonaggiooggetto`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `clgpersonaggioruolo`
--

CREATE TABLE IF NOT EXISTS `clgpersonaggioruolo` (
  `personaggio` varchar(20) NOT NULL,
  `id_ruolo` int(4) NOT NULL DEFAULT '0',
  `scadenza` date NOT NULL DEFAULT '2010-01-01'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `clgpersonaggioruolo`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `codmostrina`
--

CREATE TABLE IF NOT EXISTS `codmostrina` (
  `id_mostrina` int(4) NOT NULL AUTO_INCREMENT,
  `nome` varchar(20) NOT NULL,
  `img_url` char(50) NOT NULL DEFAULT 'grigia.gif',
  `descrizione` char(255) NOT NULL DEFAULT 'nessuna',
  PRIMARY KEY (`id_mostrina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `codmostrina`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `codtipogilda`
--

CREATE TABLE IF NOT EXISTS `codtipogilda` (
  `descrizione` varchar(50) NOT NULL,
  `cod_tipo` int(2) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`cod_tipo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

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
  `cod_tipo` int(2) NOT NULL AUTO_INCREMENT,
  `descrizione` char(20) NOT NULL,
  PRIMARY KEY (`cod_tipo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

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
-- Struttura della tabella `gilda`
--

CREATE TABLE IF NOT EXISTS `gilda` (
  `id_gilda` int(4) NOT NULL AUTO_INCREMENT,
  `nome` char(50) NOT NULL DEFAULT '',
  `tipo` varchar(1) NOT NULL DEFAULT '0',
  `immagine` char(255) DEFAULT NULL,
  `url_sito` char(255) DEFAULT NULL,
  `statuto` text,
  `visibile` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_gilda`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

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
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_interessato` char(20) NOT NULL DEFAULT '',
  `autore` char(20) NOT NULL DEFAULT '',
  `data_evento` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `codice_evento` char(20) NOT NULL DEFAULT '',
  `descrizione_evento` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `log`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `mappa`
--

CREATE TABLE IF NOT EXISTS `mappa` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) DEFAULT NULL,
  `descrizione` text,
  `stato` varchar(50) NOT NULL DEFAULT '',
  `pagina` varchar(255) DEFAULT 'nulla.php',
  `chat` tinyint(1) NOT NULL DEFAULT '1',
  `immagine` varchar(50) DEFAULT 'standard_luogo.png',
  `stanza_apparente` varchar(50) DEFAULT NULL,
  `id_mappa` int(4) DEFAULT '0',
  `link_immagine` varchar(256) NOT NULL,
  `link_immagine_hover` varchar(256) NOT NULL,
  `id_mappa_collegata` int(11) NOT NULL DEFAULT '0',
  `x_cord` int(4) DEFAULT '0',
  `y_cord` int(4) DEFAULT '0',
  `invitati` text NOT NULL,
  `privata` tinyint(1) NOT NULL DEFAULT '0',
  `proprietario` char(20) DEFAULT NULL,
  `ora_prenotazione` datetime DEFAULT NULL,
  `scadenza` datetime DEFAULT NULL,
  `costo` int(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `Invitati` (`invitati`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `mappa`
--

INSERT INTO `mappa` (`id`, `nome`, `descrizione`, `stato`, `pagina`, `chat`, `immagine`, `stanza_apparente`, `id_mappa`, `link_immagine`, `link_immagine_hover`, `id_mappa_collegata`, `x_cord`, `y_cord`, `invitati`, `privata`, `proprietario`, `ora_prenotazione`, `scadenza`, `costo`) VALUES
(1, 'Strada', 'Via che congiunge la periferia al centro.', 'Nella norma', '', 1, 'standard_luogo.png', '', 1, '', '', 0, 180, 150, '', 0, 'Nessuno', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0),
(2, 'Piazza', 'Piccola piazza con panchine ed una fontana al centro.', 'Nella norma', '', 1, 'standard_luogo.png', '', 1, '', '', 0, 80, 150, '', 0, 'Nessuno', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `mappa_click`
--

CREATE TABLE IF NOT EXISTS `mappa_click` (
  `id_click` int(1) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) DEFAULT NULL,
  `immagine` varchar(50) NOT NULL DEFAULT 'standard_mappa.png',
  `posizione` int(2) NOT NULL DEFAULT '0',
  `mobile` tinyint(1) NOT NULL DEFAULT '0',
  `meteo` varchar(40) NOT NULL DEFAULT '20Â°c - sereno',
  `larghezza` smallint(4) NOT NULL DEFAULT '500',
  `altezza` smallint(4) NOT NULL DEFAULT '330',
  PRIMARY KEY (`id_click`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

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
  `id_oggetto` int(4) NOT NULL,
  `numero` int(4) DEFAULT '0',
  PRIMARY KEY (`id_oggetto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `mercato`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `messaggi`
--

CREATE TABLE IF NOT EXISTS `messaggi` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `mittente` varchar(40) NOT NULL,
  `destinatario` varchar(20) NOT NULL DEFAULT 'Nessuno',
  `spedito` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `letto` tinyint(1) DEFAULT '0',
  `mittente_del` tinyint(1) DEFAULT '0',
  `destinatario_del` tinyint(1) DEFAULT '0',
  `testo` text,
  PRIMARY KEY (`id`),
  KEY `destinatario` (`destinatario`),
  KEY `letto` (`letto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `messaggi`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `messaggioaraldo`
--

CREATE TABLE IF NOT EXISTS `messaggioaraldo` (
  `id_messaggio` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_messaggio_padre` bigint(20) NOT NULL DEFAULT '0',
  `id_araldo` int(4) DEFAULT NULL,
  `titolo` varchar(255) DEFAULT NULL,
  `messaggio` text,
  `autore` varchar(20) DEFAULT NULL,
  `data_messaggio` datetime DEFAULT NULL,
  `importante` binary(1) NOT NULL DEFAULT '0',
  `chiuso` binary(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_messaggio`),
  KEY `id_araldo` (`id_araldo`),
  KEY `id_messaggio_padre` (`id_messaggio_padre`),
  KEY `data_messaggio` (`data_messaggio`),
  KEY `importante` (`importante`,`chiuso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `messaggioaraldo`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `oggetto`
--

CREATE TABLE IF NOT EXISTS `oggetto` (
  `id_oggetto` int(4) NOT NULL AUTO_INCREMENT,
  `tipo` int(2) NOT NULL DEFAULT '0',
  `nome` varchar(50) NOT NULL DEFAULT 'Sconosciuto',
  `creatore` varchar(20) NOT NULL DEFAULT 'System Op',
  `data_inserimento` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `descrizione` varchar(255) NOT NULL DEFAULT 'Nessuna',
  `ubicabile` int(2) NOT NULL DEFAULT '0',
  `costo` int(11) NOT NULL DEFAULT '0',
  `difesa` int(4) NOT NULL DEFAULT '0',
  `attacco` int(4) NOT NULL DEFAULT '0',
  `cariche` varchar(10) NOT NULL DEFAULT '0',
  `bonus_car0` int(4) NOT NULL DEFAULT '0',
  `bonus_car1` int(4) NOT NULL DEFAULT '0',
  `bonus_car2` int(4) NOT NULL DEFAULT '0',
  `bonus_car3` int(4) NOT NULL DEFAULT '0',
  `bonus_car4` int(4) NOT NULL DEFAULT '0',
  `bonus_car5` int(4) NOT NULL DEFAULT '0',
  `urlimg` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_oggetto`),
  KEY `Tipo` (`tipo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `oggetto`
--

INSERT INTO `oggetto` (`id_oggetto`, `tipo`, `nome`, `creatore`, `data_inserimento`, `descrizione`, `ubicabile`, `costo`, `difesa`, `attacco`, `cariche`, `bonus_car0`, `bonus_car1`, `bonus_car2`, `bonus_car3`, `bonus_car4`, `bonus_car5`, `urlimg`) VALUES
(1, 6, 'Scopa', 'Super', '2009-12-20 14:29:33', 'Una comune scopa di saggina.', 0, 10, 0, 0, '0', 0, 0, 0, 0, 0, 0, 'standard_oggetto.png');

-- --------------------------------------------------------

--
-- Struttura della tabella `personaggio`
--

CREATE TABLE IF NOT EXISTS `personaggio` (
  `nome` varchar(20) NOT NULL DEFAULT '',
  `cognome` varchar(50) NOT NULL DEFAULT '-',
  `pass` varchar(100) NOT NULL DEFAULT '',
  `ultimo_cambiopass` datetime DEFAULT NULL,
  `data_iscrizione` datetime DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `permessi` tinyint(1) DEFAULT '0',
  `ultima_mappa` int(4) NOT NULL DEFAULT '1',
  `ultimo_luogo` int(4) NOT NULL DEFAULT '-1',
  `esilio` date NOT NULL DEFAULT '2009-07-01',
  `data_esilio` date NOT NULL DEFAULT '2009-07-01',
  `motivo_esilio` varchar(255) DEFAULT NULL,
  `autore_esilio` varchar(20) DEFAULT NULL,
  `sesso` char(1) DEFAULT 'm',
  `id_razza` int(4) DEFAULT '1000',
  `descrizione` text,
  `affetti` text,
  `stato` varchar(255) DEFAULT 'nessuna',
  `online_status` varchar(100) DEFAULT NULL,
  `disponibile` tinyint(1) NOT NULL DEFAULT '1',
  `url_img` varchar(255) DEFAULT 'imgs/avatars/empty.png',
  `url_img_chat` varchar(255) NOT NULL DEFAULT ' ',
  `url_media` varchar(255) DEFAULT NULL,
  `blocca_media` binary(1) NOT NULL DEFAULT '0',
  `esperienza` decimal(12,4) DEFAULT '0',
  `car0` int(4) NOT NULL DEFAULT '5',
  `car1` int(4) NOT NULL DEFAULT '5',
  `car2` int(4) NOT NULL DEFAULT '5',
  `car3` int(4) NOT NULL DEFAULT '5',
  `car4` int(4) NOT NULL DEFAULT '5',
  `car5` int(4) NOT NULL DEFAULT '5',
  `salute` int(4) NOT NULL DEFAULT '100',
  `salute_max` int(4) NOT NULL DEFAULT '100',
  `data_ultima_gilda` datetime NOT NULL DEFAULT '2009-07-01 00:00:00',
  `soldi` int(11) DEFAULT '50',
  `banca` int(11) DEFAULT '0',
  `ultimo_stipendio` date NOT NULL DEFAULT '2009-07-01',
  `last_ip` varchar(16) DEFAULT NULL,
  `is_invisible` tinyint(1) NOT NULL DEFAULT '0',
  `ultimo_refresh` datetime NOT NULL,
  `ora_entrata` datetime NOT NULL,
  `ora_uscita` datetime NOT NULL,
  `posizione` int(4) NOT NULL DEFAULT '1',
  `ultimo_messaggio` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nome`),
  KEY `IDRazza` (`id_razza`),
  KEY `Esilio` (`esilio`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `personaggio`
--

INSERT INTO `personaggio` (`nome`, `cognome`, `pass`, `ultimo_cambiopass`, `data_iscrizione`, `email`, `permessi`, `ultima_mappa`, `ultimo_luogo`, `esilio`, `data_esilio`, `motivo_esilio`, `autore_esilio`, `sesso`, `id_razza`, `descrizione`, `affetti`, `stato`, `online_status`, `disponibile`, `url_img`, `url_img_chat`, `url_media`, `blocca_media`, `esperienza`, `car0`, `car1`, `car2`, `car3`, `car4`, `car5`, `salute`, `salute_max`, `data_ultima_gilda`, `soldi`, `banca`, `ultimo_stipendio`, `last_ip`, `is_invisible`, `ultimo_refresh`, `ora_entrata`, `ora_uscita`, `posizione`, `ultimo_messaggio`) VALUES
('Super', 'User', '$P$BcH1cP941XHOf0X61wVWWjzXqcCi2a/', NULL, '2011-06-04 00:47:48', 'email@domain.ext', 4, 1, -1, '0000-00-00', '0000-00-00', '', '', 'm', 1000, '', '', 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', '0', 1000, 7, 8, 6, 5, 6, 5, 100, 100, '0000-00-00 00:00:00', 300, 50000, '0000-00-00', '127.0.0.1', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0),
('Test', 'Di FunzionaliÃ ', '$P$BUoa19QUuXsgIDlhGC3chR/3Q7hoRy0', NULL, '2011-06-04 00:47:48', 'test@domain.ext', 0, 1, -1, '0000-00-00', '0000-00-00', '', '', 'm', 1000, '', '', 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', '0', 1000, 7, 8, 6, 5, 6, 5, 100, 100, '0000-00-00 00:00:00', 50, 50, '0000-00-00', '127.0.0.1', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `razza`
--

CREATE TABLE IF NOT EXISTS `razza` (
  `id_razza` int(4) NOT NULL AUTO_INCREMENT,
  `nome_razza` char(50) NOT NULL DEFAULT '',
  `sing_m` char(50) NOT NULL DEFAULT '',
  `sing_f` char(50) NOT NULL DEFAULT '',
  `descrizione` text NOT NULL,
  `bonus_car0` int(4) NOT NULL DEFAULT '0',
  `bonus_car1` int(4) NOT NULL DEFAULT '0',
  `bonus_car2` int(4) NOT NULL DEFAULT '0',
  `bonus_car3` int(4) NOT NULL DEFAULT '0',
  `bonus_car4` int(4) NOT NULL DEFAULT '0',
  `bonus_car5` int(4) NOT NULL DEFAULT '0',
  `immagine` char(50) NOT NULL DEFAULT 'standard_razza.png',
  `icon` varchar(50) NOT NULL DEFAULT 'standard_razza.png',
  `url_site` char(255) DEFAULT NULL,
  `iscrizione` tinyint(1) NOT NULL DEFAULT '1',
  `visibile` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_razza`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;

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
  `articolo` int(2) NOT NULL,
  `titolo` varchar(30) NOT NULL,
  `testo` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `regolamento`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `ruolo`
--

CREATE TABLE IF NOT EXISTS `ruolo` (
  `id_ruolo` int(4) NOT NULL AUTO_INCREMENT,
  `gilda` int(4) NOT NULL DEFAULT '-1',
  `nome_ruolo` char(50) NOT NULL,
  `immagine` varchar(256) NOT NULL,
  `stipendio` int(4) NOT NULL DEFAULT '0',
  `capo` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_ruolo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dump dei dati per la tabella `ruolo`
--

INSERT INTO `ruolo` (`id_ruolo`, `gilda`, `nome_ruolo`, `immagine`, `stipendio`, `capo`) VALUES
(1, 1, 'Capitano della guardia', 'standard_gilda.png', 100, 1),
(2, 1, 'Ufficiale della guardia', 'standard_gilda.png', 70, 0),
(5, -1, 'Lavoratore', 'standard_gilda.png', 5, 0),
(3, 1, 'Soldato della guardia', 'standard_gilda.png', 40, 0),
(4, 1, 'Recluta della guardia', 'standard_gilda.png', 15, 0);
