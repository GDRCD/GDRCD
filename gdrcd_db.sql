/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.11-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: gdrcd
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `_gdrcd_db_versions`
--

DROP TABLE IF EXISTS `_gdrcd_db_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `_gdrcd_db_versions` (
  `migration_id` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applied_on` datetime NOT NULL,
  PRIMARY KEY (`migration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_gdrcd_db_versions`
--

LOCK TABLES `_gdrcd_db_versions` WRITE;
/*!40000 ALTER TABLE `_gdrcd_db_versions` DISABLE KEYS */;
INSERT INTO `_gdrcd_db_versions` VALUES
('2020072500','2025-07-07 23:13:27');
/*!40000 ALTER TABLE `_gdrcd_db_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `abilita`
--

DROP TABLE IF EXISTS `abilita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `abilita` (
  `id_abilita` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(20) NOT NULL,
  `car` tinyint(1) NOT NULL DEFAULT '0',
  `descrizione` text NOT NULL,
  `id_razza` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_abilita`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `abilita`
--

LOCK TABLES `abilita` WRITE;
/*!40000 ALTER TABLE `abilita` DISABLE KEYS */;
INSERT INTO `abilita` VALUES
(18,'Resistenza',1,'Il personaggio è in grado di sopportare il dolore ed il disagio e sopporta minime dosi di agenti tossici nel proprio organismo. ',-1),
(17,'Sopravvivenza',4,'Il personaggio è in grado di procurarsi cibo e riparo all\'aperto, con mezzi minimi.',-1),
(4,'Atletica',2,'Il personaggio è ben allenato ed è in grado di saltare efficacemente, arrampicarsi, nuotare, schivare e compiere, genericamente, movimenti fisicamente impegnativi.',-1),
(5,'Cercare',5,'Il personaggio è rapido ed efficace nel perquisire un ambiente in cerca di qualcosa.',-1),
(6,'Conoscenza',3,'Il personaggio ha accumulato cultura ed esperienze, e potrebbe avere maggiori informazioni sulla situazione in cui si trova. A fronte di una prova di conoscenza il master dovrebbe fornire informazioni al giocatore via sussurro.',-1),
(7,'Percepire intenzioni',4,'Il personaggio è abile nel determinare, durante una conversazione o un interazione, se il suo interlocutore stia mentendo, sia ostile o sia ben disposto.',-1),
(8,'Cavalcare',2,'Il personaggio è in grado di cavalcare animali addestrati a tale scopo.',-1),
(9,'Addestrare animali',4,'Il personaggio comprende gli atteggiamenti e le reazioni degli animali ed è in grado di interagire con loro, addomesticarli ed addestrarli.',-1),
(10,'Armi bianche',0,'Il personaggio è addestrato al combattimento con armi bianche, scudi e protezioni.',-1),
(11,'Armi da tiro',5,'Il personaggio è addestrato all\'uso di armi da diro o da lancio.',-1),
(12,'Lotta',0,'Il personaggio è addestrato al combattimento senza armi.',-1),
(13,'Competenze tecniche',3,'Il personaggio è in grado di realizzare e riparare strumenti tecnologici. Il tipo ed il numero di tecnologie in cui è competente dovrebbe essere specificato nel background e proporzionale al punteggio di intelligenza.',-1),
(14,'Mezzi di trasporto',5,'Il personaggio è in grado di governare o pilotare specifici mezzi di trasporto. L\'elenco dei mezzi dovrebbe essere riportato nel background e proporzionale al punteggio di intelligenza.',-1),
(15,'Pronto soccorso',3,'Il personaggio è in grado di eseguire interventi d\'emergenza su individui feriti o la cui salute sia in qualche modo minacciata.',-1),
(16,'Furtività',2,'Il personaggio è in grado di muoversi ed agire senza dare nell\'occhio, e di scassinare serrature.',-1),
(19,'Volontà',4,'Il personaggio è fortemente determinato e difficilmente si lascia persuadere o dissuadere.',-1);
/*!40000 ALTER TABLE `abilita` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ambientazione`
--

DROP TABLE IF EXISTS `ambientazione`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ambientazione` (
  `capitolo` int NOT NULL,
  `testo` text NOT NULL,
  `titolo` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ambientazione`
--

LOCK TABLES `ambientazione` WRITE;
/*!40000 ALTER TABLE `ambientazione` DISABLE KEYS */;
/*!40000 ALTER TABLE `ambientazione` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `araldo`
--

DROP TABLE IF EXISTS `araldo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `araldo` (
  `id_araldo` int NOT NULL AUTO_INCREMENT,
  `tipo` int NOT NULL DEFAULT '0',
  `nome` char(50) DEFAULT NULL,
  `proprietari` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_araldo`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `araldo`
--

LOCK TABLES `araldo` WRITE;
/*!40000 ALTER TABLE `araldo` DISABLE KEYS */;
INSERT INTO `araldo` VALUES
(1,4,'Resoconti quest',0),
(2,0,'Notizie in gioco',0),
(3,2,'Umani',1000),
(4,3,'Ordini alla Guardia',1);
/*!40000 ALTER TABLE `araldo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `araldo_letto`
--

DROP TABLE IF EXISTS `araldo_letto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `araldo_letto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` char(50) DEFAULT NULL,
  `araldo_id` int NOT NULL,
  `thread_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nome` (`nome`,`thread_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `araldo_letto`
--

LOCK TABLES `araldo_letto` WRITE;
/*!40000 ALTER TABLE `araldo_letto` DISABLE KEYS */;
/*!40000 ALTER TABLE `araldo_letto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backmessaggi`
--

DROP TABLE IF EXISTS `backmessaggi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `backmessaggi` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `mittente` varchar(20) NOT NULL DEFAULT '',
  `destinatario` varchar(20) NOT NULL DEFAULT '',
  `spedito` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `letto` tinyint(1) DEFAULT '0',
  `testo` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backmessaggi`
--

LOCK TABLES `backmessaggi` WRITE;
/*!40000 ALTER TABLE `backmessaggi` DISABLE KEYS */;
/*!40000 ALTER TABLE `backmessaggi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blacklist`
--

DROP TABLE IF EXISTS `blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `blacklist` (
  `ip` char(15) NOT NULL DEFAULT '',
  `nota` char(255) DEFAULT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT '0',
  `ora` datetime DEFAULT NULL,
  `host` char(255) NOT NULL DEFAULT '-',
  PRIMARY KEY (`ip`),
  KEY `Ora` (`ora`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blacklist`
--

LOCK TABLES `blacklist` WRITE;
/*!40000 ALTER TABLE `blacklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `stanza` int NOT NULL DEFAULT '0',
  `imgs` varchar(100) NOT NULL DEFAULT '',
  `mittente` varchar(20) NOT NULL DEFAULT '',
  `destinatario` varchar(20) DEFAULT NULL,
  `ora` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `tipo` char(1) DEFAULT NULL,
  `testo` text,
  PRIMARY KEY (`id`),
  KEY `Stanza` (`stanza`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat`
--

LOCK TABLES `chat` WRITE;
/*!40000 ALTER TABLE `chat` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clgpersonaggioabilita`
--

DROP TABLE IF EXISTS `clgpersonaggioabilita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clgpersonaggioabilita` (
  `nome` varchar(20) NOT NULL,
  `id_abilita` int NOT NULL,
  `grado` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clgpersonaggioabilita`
--

LOCK TABLES `clgpersonaggioabilita` WRITE;
/*!40000 ALTER TABLE `clgpersonaggioabilita` DISABLE KEYS */;
/*!40000 ALTER TABLE `clgpersonaggioabilita` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clgpersonaggiomostrine`
--

DROP TABLE IF EXISTS `clgpersonaggiomostrine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clgpersonaggiomostrine` (
  `nome` char(20) NOT NULL DEFAULT '',
  `id_mostrina` char(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`nome`,`id_mostrina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clgpersonaggiomostrine`
--

LOCK TABLES `clgpersonaggiomostrine` WRITE;
/*!40000 ALTER TABLE `clgpersonaggiomostrine` DISABLE KEYS */;
/*!40000 ALTER TABLE `clgpersonaggiomostrine` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clgpersonaggiooggetto`
--

DROP TABLE IF EXISTS `clgpersonaggiooggetto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clgpersonaggiooggetto` (
  `nome` varchar(20) NOT NULL DEFAULT '',
  `id_oggetto` int NOT NULL DEFAULT '0',
  `numero` int DEFAULT '1',
  `cariche` int NOT NULL DEFAULT '-1',
  `commento` varchar(255) DEFAULT NULL,
  `posizione` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`nome`,`id_oggetto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clgpersonaggiooggetto`
--

LOCK TABLES `clgpersonaggiooggetto` WRITE;
/*!40000 ALTER TABLE `clgpersonaggiooggetto` DISABLE KEYS */;
/*!40000 ALTER TABLE `clgpersonaggiooggetto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clgpersonaggioruolo`
--

DROP TABLE IF EXISTS `clgpersonaggioruolo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clgpersonaggioruolo` (
  `personaggio` varchar(20) NOT NULL,
  `id_ruolo` int NOT NULL DEFAULT '0',
  `scadenza` date NOT NULL DEFAULT '2010-01-01'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clgpersonaggioruolo`
--

LOCK TABLES `clgpersonaggioruolo` WRITE;
/*!40000 ALTER TABLE `clgpersonaggioruolo` DISABLE KEYS */;
/*!40000 ALTER TABLE `clgpersonaggioruolo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `codmostrina`
--

DROP TABLE IF EXISTS `codmostrina`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `codmostrina` (
  `id_mostrina` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(20) NOT NULL,
  `img_url` char(50) NOT NULL DEFAULT 'grigia.gif',
  `descrizione` char(255) NOT NULL DEFAULT 'nessuna',
  PRIMARY KEY (`id_mostrina`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `codmostrina`
--

LOCK TABLES `codmostrina` WRITE;
/*!40000 ALTER TABLE `codmostrina` DISABLE KEYS */;
/*!40000 ALTER TABLE `codmostrina` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `codtipogilda`
--

DROP TABLE IF EXISTS `codtipogilda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `codtipogilda` (
  `descrizione` varchar(50) NOT NULL,
  `cod_tipo` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`cod_tipo`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `codtipogilda`
--

LOCK TABLES `codtipogilda` WRITE;
/*!40000 ALTER TABLE `codtipogilda` DISABLE KEYS */;
INSERT INTO `codtipogilda` VALUES
('Positivo',1),
('Neutrale',2),
('Negativo',3);
/*!40000 ALTER TABLE `codtipogilda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `codtipooggetto`
--

DROP TABLE IF EXISTS `codtipooggetto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `codtipooggetto` (
  `cod_tipo` int NOT NULL AUTO_INCREMENT,
  `descrizione` char(20) NOT NULL,
  PRIMARY KEY (`cod_tipo`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `codtipooggetto`
--

LOCK TABLES `codtipooggetto` WRITE;
/*!40000 ALTER TABLE `codtipooggetto` DISABLE KEYS */;
INSERT INTO `codtipooggetto` VALUES
(1,'Animale'),
(2,'Vestito'),
(3,'Fiore - Pianta'),
(4,'Gioiello'),
(5,'Arma'),
(6,'Attrezzo'),
(0,'Vario');
/*!40000 ALTER TABLE `codtipooggetto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gilda`
--

DROP TABLE IF EXISTS `gilda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gilda` (
  `id_gilda` int NOT NULL AUTO_INCREMENT,
  `nome` char(50) NOT NULL DEFAULT '',
  `tipo` varchar(1) NOT NULL DEFAULT '0',
  `immagine` char(255) DEFAULT NULL,
  `url_sito` char(255) DEFAULT NULL,
  `statuto` text,
  `visibile` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_gilda`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gilda`
--

LOCK TABLES `gilda` WRITE;
/*!40000 ALTER TABLE `gilda` DISABLE KEYS */;
INSERT INTO `gilda` VALUES
(1,'Guardia cittadina','1','standard_gilda.png','','',1);
/*!40000 ALTER TABLE `gilda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_interessato` varchar(20) NOT NULL DEFAULT '',
  `autore` varchar(60) NOT NULL DEFAULT '',
  `data_evento` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `codice_evento` char(20) NOT NULL DEFAULT '',
  `descrizione_evento` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mappa`
--

DROP TABLE IF EXISTS `mappa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mappa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) DEFAULT NULL,
  `descrizione` text,
  `stato` varchar(50) NOT NULL DEFAULT '',
  `pagina` varchar(255) DEFAULT 'nulla.php',
  `chat` tinyint(1) NOT NULL DEFAULT '1',
  `immagine` varchar(50) DEFAULT 'standard_luogo.png',
  `stanza_apparente` varchar(50) DEFAULT NULL,
  `id_mappa` int DEFAULT '0',
  `link_immagine` varchar(256) NOT NULL,
  `link_immagine_hover` varchar(256) NOT NULL,
  `id_mappa_collegata` int NOT NULL DEFAULT '0',
  `x_cord` int DEFAULT '0',
  `y_cord` int DEFAULT '0',
  `invitati` text NOT NULL,
  `privata` tinyint(1) NOT NULL DEFAULT '0',
  `proprietario` char(20) DEFAULT NULL,
  `ora_prenotazione` datetime DEFAULT NULL,
  `scadenza` datetime DEFAULT NULL,
  `costo` int DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `Invitati` (`invitati`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mappa`
--

LOCK TABLES `mappa` WRITE;
/*!40000 ALTER TABLE `mappa` DISABLE KEYS */;
INSERT INTO `mappa` VALUES
(1,'Strada','Via che congiunge la periferia al centro.','Nella norma','',1,'standard_luogo.png','',1,'','',0,180,150,'',0,'Nessuno','0000-00-00 00:00:00','0000-00-00 00:00:00',0),
(2,'Piazza','Piccola piazza con panchine ed una fontana al centro.','Nella norma','',1,'standard_luogo.png','',1,'','',0,80,150,'',0,'Nessuno','0000-00-00 00:00:00','0000-00-00 00:00:00',0);
/*!40000 ALTER TABLE `mappa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mappa_click`
--

DROP TABLE IF EXISTS `mappa_click`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mappa_click` (
  `id_click` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) DEFAULT NULL,
  `immagine` varchar(50) NOT NULL DEFAULT 'standard_mappa.png',
  `posizione` int NOT NULL DEFAULT '0',
  `mobile` tinyint(1) NOT NULL DEFAULT '0',
  `meteo` varchar(40) NOT NULL DEFAULT '20°c - sereno',
  `larghezza` smallint NOT NULL DEFAULT '500',
  `altezza` smallint NOT NULL DEFAULT '330',
  PRIMARY KEY (`id_click`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mappa_click`
--

LOCK TABLES `mappa_click` WRITE;
/*!40000 ALTER TABLE `mappa_click` DISABLE KEYS */;
INSERT INTO `mappa_click` VALUES
(1,'Mappa principale','spacer.gif',2,0,'20°c - sereno',500,330),
(2,'Mappa secondaria','spacer.gif',2,0,'18°c - nuvoloso',500,330);
/*!40000 ALTER TABLE `mappa_click` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mercato`
--

DROP TABLE IF EXISTS `mercato`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mercato` (
  `id_oggetto` int NOT NULL,
  `numero` int DEFAULT '0',
  PRIMARY KEY (`id_oggetto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mercato`
--

LOCK TABLES `mercato` WRITE;
/*!40000 ALTER TABLE `mercato` DISABLE KEYS */;
/*!40000 ALTER TABLE `mercato` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messaggi`
--

DROP TABLE IF EXISTS `messaggi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `messaggi` (
  `id` bigint NOT NULL AUTO_INCREMENT,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messaggi`
--

LOCK TABLES `messaggi` WRITE;
/*!40000 ALTER TABLE `messaggi` DISABLE KEYS */;
/*!40000 ALTER TABLE `messaggi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messaggioaraldo`
--

DROP TABLE IF EXISTS `messaggioaraldo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `messaggioaraldo` (
  `id_messaggio` bigint NOT NULL AUTO_INCREMENT,
  `id_messaggio_padre` bigint NOT NULL DEFAULT '0',
  `id_araldo` int DEFAULT NULL,
  `titolo` varchar(255) DEFAULT NULL,
  `messaggio` text,
  `autore` varchar(20) DEFAULT NULL,
  `data_messaggio` datetime DEFAULT NULL,
  `data_ultimo_messaggio` datetime DEFAULT NULL,
  `importante` binary(1) NOT NULL DEFAULT '0',
  `chiuso` binary(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_messaggio`),
  KEY `id_araldo` (`id_araldo`),
  KEY `id_messaggio_padre` (`id_messaggio_padre`),
  KEY `data_messaggio` (`data_messaggio`),
  KEY `importante` (`importante`,`chiuso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messaggioaraldo`
--

LOCK TABLES `messaggioaraldo` WRITE;
/*!40000 ALTER TABLE `messaggioaraldo` DISABLE KEYS */;
/*!40000 ALTER TABLE `messaggioaraldo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oggetto`
--

DROP TABLE IF EXISTS `oggetto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oggetto` (
  `id_oggetto` int NOT NULL AUTO_INCREMENT,
  `tipo` int NOT NULL DEFAULT '0',
  `nome` varchar(50) NOT NULL DEFAULT 'Sconosciuto',
  `creatore` varchar(20) NOT NULL DEFAULT 'System Op',
  `data_inserimento` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `descrizione` varchar(255) NOT NULL DEFAULT 'Nessuna',
  `ubicabile` int NOT NULL DEFAULT '0',
  `costo` int NOT NULL DEFAULT '0',
  `difesa` int NOT NULL DEFAULT '0',
  `attacco` int NOT NULL DEFAULT '0',
  `cariche` varchar(10) NOT NULL DEFAULT '0',
  `bonus_car0` int NOT NULL DEFAULT '0',
  `bonus_car1` int NOT NULL DEFAULT '0',
  `bonus_car2` int NOT NULL DEFAULT '0',
  `bonus_car3` int NOT NULL DEFAULT '0',
  `bonus_car4` int NOT NULL DEFAULT '0',
  `bonus_car5` int NOT NULL DEFAULT '0',
  `urlimg` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_oggetto`),
  KEY `Tipo` (`tipo`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oggetto`
--

LOCK TABLES `oggetto` WRITE;
/*!40000 ALTER TABLE `oggetto` DISABLE KEYS */;
INSERT INTO `oggetto` VALUES
(1,6,'Scopa','Super','2009-12-20 14:29:33','Una comune scopa di saggina.',0,10,0,0,'0',0,0,0,0,0,0,'standard_oggetto.png');
/*!40000 ALTER TABLE `oggetto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personaggio`
--

DROP TABLE IF EXISTS `personaggio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personaggio` (
  `nome` varchar(20) NOT NULL DEFAULT '',
  `cognome` varchar(50) NOT NULL DEFAULT '-',
  `pass` varchar(60) NOT NULL DEFAULT '',
  `ultimo_cambiopass` datetime DEFAULT NULL,
  `data_iscrizione` datetime DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `permessi` tinyint(1) DEFAULT '0',
  `ultima_mappa` int NOT NULL DEFAULT '1',
  `ultimo_luogo` int NOT NULL DEFAULT '-1',
  `esilio` date DEFAULT NULL,
  `data_esilio` date NOT NULL DEFAULT '2009-07-01',
  `motivo_esilio` varchar(255) DEFAULT NULL,
  `autore_esilio` varchar(20) DEFAULT NULL,
  `sesso` char(1) DEFAULT 'm',
  `id_razza` int DEFAULT '1000',
  `descrizione` text,
  `affetti` text,
  `stato` varchar(255) DEFAULT 'nessuna',
  `online_status` varchar(100) DEFAULT NULL,
  `disponibile` tinyint(1) NOT NULL DEFAULT '1',
  `url_img` varchar(255) DEFAULT 'imgs/avatars/empty.png',
  `url_img_chat` varchar(255) NOT NULL DEFAULT ' ',
  `url_media` varchar(255) DEFAULT NULL,
  `blocca_media` binary(1) NOT NULL DEFAULT '0',
  `esperienza` decimal(14,5) DEFAULT '0.00000',
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
  `last_ip` varchar(60) DEFAULT NULL,
  `is_invisible` tinyint(1) NOT NULL DEFAULT '0',
  `ultimo_refresh` datetime DEFAULT NULL,
  `ora_entrata` datetime DEFAULT NULL,
  `ora_uscita` datetime DEFAULT NULL,
  `posizione` int NOT NULL DEFAULT '1',
  `ultimo_messaggio` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`nome`),
  KEY `IDRazza` (`id_razza`),
  KEY `Esilio` (`esilio`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personaggio`
--

LOCK TABLES `personaggio` WRITE;
/*!40000 ALTER TABLE `personaggio` DISABLE KEYS */;
INSERT INTO `personaggio` VALUES
('Super','User','$P$B326uMxMwK4jQepdYQbpXfKXKO/PBD1',NULL,'2025-07-07 23:13:27','$P$BwJo98FwWiQfNSpSPAQfaM8SkJbVuy.',4,1,-1,'2009-07-01','2009-07-01','','','m',1000,'','','Nella norma','',1,'imgs/avatars/empty.png','','','0',1000.00000,7,8,6,5,6,5,100,100,'2009-07-01 00:00:00',300,50000,'2009-07-01','127.0.0.1',0,'2009-07-01 00:00:00','2009-07-01 00:00:00','2009-07-01 00:00:00',1,0),
('Test','Di Funzionalià','$P$Ba0Q5iR.i2y1yrK856QSiYpgfeths41',NULL,'2025-07-07 23:13:27','$P$B4TcA6FTIF4b7Z8wCiOyoUAufpsEsM/',0,1,-1,'2009-07-01','2009-07-01','','','m',1000,'','','Nella norma','',1,'imgs/avatars/empty.png','','','0',1000.00000,7,8,6,5,6,5,100,100,'2009-07-01 00:00:00',50,50,'2009-07-01','127.0.0.1',0,'2009-07-01 00:00:00','2009-07-01 00:00:00','2009-07-01 00:00:00',1,0);
/*!40000 ALTER TABLE `personaggio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `razza`
--

DROP TABLE IF EXISTS `razza`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `razza` (
  `id_razza` int NOT NULL AUTO_INCREMENT,
  `nome_razza` char(50) NOT NULL DEFAULT '',
  `sing_m` char(50) NOT NULL DEFAULT '',
  `sing_f` char(50) NOT NULL DEFAULT '',
  `descrizione` text NOT NULL,
  `bonus_car0` int NOT NULL DEFAULT '0',
  `bonus_car1` int NOT NULL DEFAULT '0',
  `bonus_car2` int NOT NULL DEFAULT '0',
  `bonus_car3` int NOT NULL DEFAULT '0',
  `bonus_car4` int NOT NULL DEFAULT '0',
  `bonus_car5` int NOT NULL DEFAULT '0',
  `immagine` char(50) NOT NULL DEFAULT 'standard_razza.png',
  `icon` varchar(50) NOT NULL DEFAULT 'standard_razza.png',
  `url_site` char(255) DEFAULT NULL,
  `iscrizione` tinyint(1) NOT NULL DEFAULT '1',
  `visibile` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_razza`)
) ENGINE=MyISAM AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `razza`
--

LOCK TABLES `razza` WRITE;
/*!40000 ALTER TABLE `razza` DISABLE KEYS */;
INSERT INTO `razza` VALUES
(1000,'Umani','Umano','Umana','',0,0,0,0,0,0,'standard_razza.png','standard_razza.png','',1,1);
/*!40000 ALTER TABLE `razza` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regolamento`
--

DROP TABLE IF EXISTS `regolamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `regolamento` (
  `articolo` int NOT NULL,
  `titolo` varchar(30) NOT NULL,
  `testo` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regolamento`
--

LOCK TABLES `regolamento` WRITE;
/*!40000 ALTER TABLE `regolamento` DISABLE KEYS */;
/*!40000 ALTER TABLE `regolamento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ruolo`
--

DROP TABLE IF EXISTS `ruolo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ruolo` (
  `id_ruolo` int NOT NULL AUTO_INCREMENT,
  `gilda` int NOT NULL DEFAULT '-1',
  `nome_ruolo` char(50) NOT NULL,
  `immagine` varchar(256) NOT NULL,
  `stipendio` int NOT NULL DEFAULT '0',
  `capo` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_ruolo`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ruolo`
--

LOCK TABLES `ruolo` WRITE;
/*!40000 ALTER TABLE `ruolo` DISABLE KEYS */;
INSERT INTO `ruolo` VALUES
(1,1,'Capitano della guardia','standard_gilda.png',100,1),
(2,1,'Ufficiale della guardia','standard_gilda.png',70,0),
(5,-1,'Lavoratore','standard_gilda.png',5,0),
(3,1,'Soldato della guardia','standard_gilda.png',40,0),
(4,1,'Recluta della guardia','standard_gilda.png',15,0);
/*!40000 ALTER TABLE `ruolo` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-07 23:16:34
