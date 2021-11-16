-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2021 at 10:35 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 7.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gdrcd`
--

-- --------------------------------------------------------

--
-- Table structure for table `abilita`
--

CREATE TABLE `abilita` (
  `id_abilita` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `car` tinyint(1) NOT NULL DEFAULT 0,
  `descrizione` text NOT NULL,
  `id_razza` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `abilita`
--

INSERT INTO `abilita` (`id_abilita`, `nome`, `car`, `descrizione`, `id_razza`) VALUES
(18, 'Resistenza', 1, 'Il personaggio Ã¨ in grado di sopportare il dolore ed il disagio e sopporta minime dosi di agenti tossici nel proprio organismo. ', -1),
(17, 'Sopravvivenza', 4, 'Il personaggio Ã¨ in grado di procurarsi cibo e riparo all\'aperto, con mezzi minimi.', -1),
(4, 'Atletica', 2, 'Il personaggio Ã¨ ben allenato ed Ã¨ in grado di saltare efficacemente, arrampicarsi, nuotare, schivare e compiere, genericamente, movimenti fisicamente impegnativi.', -1),
(5, 'Cercare', 5, 'Il personaggio Ã¨ rapido ed efficace nel perquisire un ambiente in cerca di qualcosa.', -1),
(6, 'Conoscenza', 3, 'Il personaggio ha accumulato cultura ed esperienze, e potrebbe avere maggiori informazioni sulla situazione in cui si trova. A fronte di una prova di conoscenza il master dovrebbe fornire informazioni al giocatore via sussurro.', -1),
(7, 'Percepire intenzioni', 4, 'Il personaggio Ã¨ abile nel determinare, durante una conversazione o un interazione, se il suo interlocutore stia mentendo, sia ostile o sia ben disposto.', -1),
(8, 'Cavalcare', 2, 'Il personaggio Ã¨ in grado di cavalcare animali addestrati a tale scopo.', -1),
(9, 'Addestrare animali', 4, 'Il personaggio comprende gli atteggiamenti e le reazioni degli animali ed Ã¨ in grado di interagire con loro, addomesticarli ed addestrarli.', -1),
(10, 'Armi bianche', 0, 'Il personaggio Ã¨ addestrato al combattimento con armi bianche, scudi e protezioni.', -1),
(11, 'Armi da tiro', 5, 'Il personaggio Ã¨ addestrato all\'uso di armi da diro o da lancio.', -1),
(12, 'Lotta', 0, 'Il personaggio Ã¨ addestrato al combattimento senza armi.', -1),
(13, 'Competenze tecniche', 3, 'Il personaggio Ã¨ in grado di realizzare e riparare strumenti tecnologici. Il tipo ed il numero di tecnologie in cui Ã¨ competente dovrebbe essere specificato nel background e proporzionale al punteggio di intelligenza.', -1),
(14, 'Mezzi di trasporto', 5, 'Il personaggio Ã¨ in grado di governare o pilotare specifici mezzi di trasporto. L\'elenco dei mezzi dovrebbe essere riportato nel background e proporzionale al punteggio di intelligenza.', -1),
(15, 'Pronto soccorso', 3, 'Il personaggio Ã¨ in grado di eseguire interventi d\'emergenza su individui feriti o la cui salute sia in qualche modo minacciata.', -1),
(16, 'FurtivitÃ ', 2, 'Il personaggio Ã¨ in grado di muoversi ed agire senza dare nell\'occhio, e di scassinare serrature.', -1),
(19, 'VolontÃ ', 4, 'Il personaggio Ã¨ fortemente determinato e difficilmente si lascia persuadere o dissuadere.', -1);

-- --------------------------------------------------------

--
-- Table structure for table `abilita_extra`
--

CREATE TABLE `abilita_extra` (
  `id` int(11) NOT NULL,
  `abilita` int(11) NOT NULL,
  `grado` int(11) NOT NULL,
  `descrizione` text NOT NULL,
  `costo` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `abilita_requisiti`
--

CREATE TABLE `abilita_requisiti` (
  `id` int(11) NOT NULL,
  `abilita` int(11) NOT NULL,
  `grado` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  `id_riferimento` int(11) NOT NULL,
  `liv_riferimento` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ambientazione`
--

CREATE TABLE `ambientazione` (
  `capitolo` int(11) NOT NULL,
  `testo` text NOT NULL,
  `titolo` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `araldo`
--

CREATE TABLE `araldo` (
  `id_araldo` int(11) NOT NULL,
  `tipo` int(11) NOT NULL DEFAULT 0,
  `nome` varchar(255) DEFAULT NULL,
  `proprietari` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `araldo`
--

INSERT INTO `araldo` (`id_araldo`, `tipo`, `nome`, `proprietari`) VALUES
(1, 4, 'Resoconti quest', 0),
(2, 0, 'Notizie in gioco', 0),
(3, 2, 'Umani', 1000),
(4, 3, 'Ordini alla Guardia', 1);

-- --------------------------------------------------------

--
-- Table structure for table `araldo_letto`
--

CREATE TABLE `araldo_letto` (
  `id` bigint(20) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `araldo_id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `backmessaggi`
--

CREATE TABLE `backmessaggi` (
  `id` bigint(20) NOT NULL,
  `mittente` varchar(255) NOT NULL DEFAULT '',
  `destinatario` varchar(255) NOT NULL DEFAULT '',
  `spedito` datetime NOT NULL DEFAULT current_timestamp(),
  `letto` tinyint(1) DEFAULT 0,
  `tipo` int(11) NOT NULL DEFAULT 0,
  `oggetto` text DEFAULT NULL,
  `testo` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blacklist`
--

CREATE TABLE `blacklist` (
  `ip` varchar(255) NOT NULL DEFAULT '',
  `nota` varchar(255) DEFAULT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0,
  `ora` datetime DEFAULT NULL,
  `host` varchar(255) NOT NULL DEFAULT '-'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `id` bigint(20) NOT NULL,
  `stanza` int(11) NOT NULL DEFAULT 0,
  `imgs` varchar(255) NOT NULL DEFAULT '',
  `mittente` varchar(255) NOT NULL DEFAULT '',
  `destinatario` varchar(255) DEFAULT NULL,
  `ora` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo` varchar(255) DEFAULT NULL,
  `testo` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `classe`
--

CREATE TABLE `classe` (
  `id_classe` int(11) NOT NULL,
  `nome_classe` varchar(255) NOT NULL DEFAULT '',
  `sing_m` varchar(255) NOT NULL DEFAULT '',
  `sing_f` varchar(255) NOT NULL DEFAULT '',
  `descrizione` text NOT NULL,
  `bonus_car0` int(11) NOT NULL DEFAULT 0,
  `bonus_car1` int(11) NOT NULL DEFAULT 0,
  `bonus_car2` int(11) NOT NULL DEFAULT 0,
  `bonus_car3` int(11) NOT NULL DEFAULT 0,
  `bonus_car4` int(11) NOT NULL DEFAULT 0,
  `bonus_car5` int(11) NOT NULL DEFAULT 0,
  `immagine` varchar(255) NOT NULL DEFAULT 'standard_classe.png',
  `icon` varchar(255) NOT NULL DEFAULT 'standard_classe.png',
  `url_site` varchar(255) DEFAULT NULL,
  `iscrizione` tinyint(1) NOT NULL DEFAULT 1,
  `visibile` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `classe`
--

INSERT INTO `classe` (`id_classe`, `nome_classe`, `sing_m`, `sing_f`, `descrizione`, `bonus_car0`, `bonus_car1`, `bonus_car2`, `bonus_car3`, `bonus_car4`, `bonus_car5`, `immagine`, `icon`, `url_site`, `iscrizione`, `visibile`) VALUES
(1000, 'Lottatore', 'Lottatore', 'Lottatrice', 'test', 0, 0, 0, 0, 0, 0, 'standard_classe.png', 'standard_classe.png', '', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `clgpersonaggioabilita`
--

CREATE TABLE `clgpersonaggioabilita` (
  `nome` varchar(255) NOT NULL,
  `id_abilita` int(11) NOT NULL,
  `grado` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `clgpersonaggiomostrine`
--

CREATE TABLE `clgpersonaggiomostrine` (
  `id_mostrina` varchar(255) NOT NULL DEFAULT '',
  `nome` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `clgpersonaggiooggetto`
--

CREATE TABLE `clgpersonaggiooggetto` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL DEFAULT '',
  `id_oggetto` int(11) NOT NULL DEFAULT 0,
  `numero` int(11) DEFAULT 1,
  `cariche` int(11) NOT NULL DEFAULT -1,
  `commento` varchar(255) DEFAULT NULL,
  `posizione` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `clgpersonaggioruolo`
--

CREATE TABLE `clgpersonaggioruolo` (
  `personaggio` varchar(255) NOT NULL,
  `id_ruolo` int(11) NOT NULL DEFAULT 0,
  `scadenza` date NOT NULL DEFAULT '2010-01-01'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `codmostrina`
--

CREATE TABLE `codmostrina` (
  `id_mostrina` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `img_url` varchar(255) NOT NULL DEFAULT 'grigia.gif',
  `descrizione` varchar(255) NOT NULL DEFAULT 'nessuna'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `codtipogilda`
--

CREATE TABLE `codtipogilda` (
  `descrizione` varchar(255) NOT NULL,
  `cod_tipo` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `codtipogilda`
--

INSERT INTO `codtipogilda` (`descrizione`, `cod_tipo`) VALUES
('Positivo', 1),
('Neutrale', 2),
('Negativo', 3);

-- --------------------------------------------------------

--
-- Table structure for table `codtipooggetto`
--

CREATE TABLE `codtipooggetto` (
  `cod_tipo` int(11) NOT NULL,
  `descrizione` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `codtipooggetto`
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
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `const_name` varchar(255) NOT NULL,
  `val` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `section` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(255) DEFAULT 'String',
  `editable` tinyint(1) DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `const_name`, `val`, `label`, `section`, `description`, `type`, `editable`) VALUES
(1, 'ABI_PUBLIC', '1', 'Abilità pubbliche', 'Abilita', 'Le abilità sono pubbliche?', 'bool', 1),
(2, 'ABI_LEVEL_CAP', '5', 'Level cap Abilità', 'Abilita', 'Livello massimo abilità', 'int', 1),
(3, 'DEFAULT_PX_PER_LVL', '10', 'Costo default Abilità', 'Abilita', 'Moltiplicatore costo abilità, se non specificato', 'int', 1),
(4, 'ABI_REQUIREMENT', '1', 'Requisiti Abilità', 'Abilita', 'Abilitare requisiti abilità?', 'bool', 1),
(5, 'REQUISITO_ABI', '1', '', 'Abilita', 'Requisito di tipo abilità', 'int', 0),
(6, 'REQUISITO_STAT', '2', '', 'Abilita', 'Requisito di tipo statistica', 'int', 0),
(7, 'ABI_EXTRA', '1', 'Dati abilita extra', 'Abilita', 'Abilitare i dati abilita extra?', 'int', 0),
(8, 'CHAT_TIME', '2', 'Ore storico chat', 'Chat', 'Ore di caricamento nella chat', 'int', 1),
(9, 'CHAT_EXP', '1', 'Exp in chat', 'Chat Exp', 'Esperienza in chat, attiva?', 'bool', 1),
(10, 'CHAT_PVT_EXP', '0', 'Exp in chat pvt', 'Chat Exp', 'Esperienza in chat pvt, attiva?', 'bool', 1),
(11, 'CHAT_EXP_MASTER', '1', 'Exp master', 'Chat Exp', 'Esperienza per ogni azione master', 'int', 1),
(12, 'CHAT_EXP_AZIONE', '1', 'Exp azione', 'Chat Exp', 'Esperienza per ogni azione normale', 'int', 1),
(13, 'CHAT_EXP_MIN', '500', 'Minimo caratteri', 'Chat Exp', 'Minimo di caratteri per esperienza', 'int', 1),
(14, 'CHAT_ICONE', '1', 'Icone in chat', 'Chat', 'Icone attive in chat?', 'bool', 1),
(15, 'CHAT_AVATAR', '1', 'Avatar in chat', 'Chat', 'Avatar attivo in chat?', 'bool', 1),
(16, 'CHAT_NOTIFY', '1', 'Notifica in chat', 'Chat', 'Notifiche in chat per nuove azioni?', 'bool', 1),
(17, 'CHAT_DICE', '1', 'Dadi in chat', 'Chat Dadi', 'Dadi attivi in chat?', 'bool', 1),
(18, 'CHAT_DICE_BASE', '20', 'Tipo dado in chat', 'Chat Dadi', 'Numero massimo dado in chat', 'int', 1),
(19, 'CHAT_SKILL_BUYED', '0', 'Solo abilità acquistate', 'Chat Dadi', 'Solo skill acquistate nel lancio in chat', 'bool', 1),
(20, 'CHAT_EQUIP_BONUS', '0', 'Bonus equipaggimento', 'Chat Dadi', 'Bonus equipaggiamento ai dadi in chat?', 'bool', 1),
(21, 'CHAT_EQUIP_EQUIPPED', '1', 'Solo equipaggiamento', 'Chat Dadi', 'Solo oggetti equipaggiati in chat?', 'bool', 1),
(22, 'CHAT_SAVE', '1', 'Salva chat', 'Chat Salvataggio', 'Salva chat attivo?', 'bool', 1),
(23, 'CHAT_PVT_SAVE', '1', 'Salva chat pvt', 'Chat Salvataggio', 'Salva chat attivo in pvt?', 'bool', 1),
(24, 'CHAT_SAVE_LINK', '1', 'Salva chat in link', 'Chat Salvataggio', 'Salva chat in modalità link?', 'bool', 1),
(25, 'CHAT_SAVE_DOWNLOAD', '1', 'Salva chat download', 'Chat Salvataggio', 'Salva chat con download?', 'bool', 1),
(26, 'ESITI_ENABLE', '1', 'Attiva esiti', 'Esiti', 'Abilitare la funzione esiti?', 'bool', 1),
(27, 'ESITI_CHAT', '1', 'Attiva esiti in chat', 'Esiti', 'Abilitare la funzione di lancio degli esiti in chat?', 'bool', 1),
(28, 'ESITI_TIRI', '1', 'Lancio di dadi negli esiti', 'Esiti', 'Abilitare la possibilità di lanciare dadi all\'interno del pannello esiti?', 'bool', 1),
(29, 'ESITI_FROM_PLAYER', '1', 'Esiti dai player', 'Esiti', 'Abilitare richiesta esiti da parte dei player?', 'bool', 1),
(30, 'QUEST_ENABLED', '1', 'Attivazione Quest migliorate', 'Quest', 'Gestione quest migliorata, attiva?', 'bool', 1),
(31, 'QUEST_VIEW', '2', 'Permessi visual quest', 'Quest', 'Permesso minimo per visualizzazione delle quest', 'permission', 1),
(32, 'QUEST_SUPER_PERMISSION', '3', 'Permessi speciali', 'Quest', 'Permesso minimo per modificare qualsiasi parte del pacchetto', 'int', 1),
(33, 'QUEST_NOTIFY', '0', 'Notifiche quest', 'Quest', 'Definisce la possibilità di inviare messaggi automatici di avviso agli utenti che partecipano ad una quest', 'bool', 1),
(34, 'TRAME_ENABLED', '1', 'Attivazione trame', 'Trame', 'Sistema trame attivo?', 'bool', 1),
(35, 'QUEST_RESULTS_FOR_PAGE', '15', 'Risultati per pagina', 'Quest', 'Numero risultati per pagina nella gestione delle quest.', 'int', 1);

-- --------------------------------------------------------

--
-- Table structure for table `diario`
--

CREATE TABLE `diario` (
  `id` int(11) NOT NULL,
  `personaggio` varchar(255) DEFAULT NULL,
  `data` date NOT NULL,
  `data_inserimento` datetime NOT NULL,
  `data_modifica` datetime DEFAULT NULL,
  `visibile` varchar(255) NOT NULL,
  `titolo` varchar(255) NOT NULL DEFAULT '',
  `testo` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `esiti`
--

CREATE TABLE `esiti` (
  `id` int(11) NOT NULL,
  `autore` varchar(255) NOT NULL,
  `titolo` text NOT NULL,
  `data` datetime NOT NULL DEFAULT current_timestamp(),
  `master` varchar(255) NOT NULL DEFAULT '0',
  `closed` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `esiti_personaggio`
--

CREATE TABLE `esiti_personaggio` (
  `id` int(11) NOT NULL,
  `personaggio` int(11) NOT NULL,
  `esito` int(11) NOT NULL,
  `assegnato_il` datetime NOT NULL DEFAULT current_timestamp(),
  `assegnato_da` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `esiti_risposte`
--

CREATE TABLE `esiti_risposte` (
  `id` int(11) NOT NULL,
  `esito` int(11) NOT NULL,
  `autore` varchar(255) NOT NULL,
  `chat` int(11) NOT NULL DEFAULT 0,
  `contenuto` mediumtext NOT NULL,
  `data` datetime NOT NULL DEFAULT current_timestamp(),
  `sent` int(11) NOT NULL DEFAULT 0,
  `abilita` int(11) DEFAULT 0,
  `dice_face` int(11) NOT NULL DEFAULT 0,
  `dice_num` int(11) NOT NULL DEFAULT 0,
  `modificatore` int(4) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `esiti_risposte_cd`
--

CREATE TABLE `esiti_risposte_cd` (
  `id` int(11) NOT NULL,
  `esito` int(11) NOT NULL,
  `cd` int(11) NOT NULL,
  `testo` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `esiti_risposte_letture`
--

CREATE TABLE `esiti_risposte_letture` (
  `id` int(11) NOT NULL,
  `esito` int(11) NOT NULL,
  `personaggio` int(11) NOT NULL,
  `letto_il` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `esiti_risposte_risultati`
--

CREATE TABLE `esiti_risposte_risultati` (
  `id` int(11) NOT NULL,
  `personaggio` int(11) NOT NULL,
  `esito` int(11) NOT NULL,
  `risultato` int(11) NOT NULL,
  `testo` text DEFAULT NULL,
  `lanciato_il` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gilda`
--

CREATE TABLE `gilda` (
  `id_gilda` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL DEFAULT '',
  `tipo` varchar(255) NOT NULL DEFAULT '0',
  `immagine` varchar(255) DEFAULT NULL,
  `url_sito` varchar(255) DEFAULT NULL,
  `statuto` text DEFAULT NULL,
  `visibile` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `gilda`
--

INSERT INTO `gilda` (`id_gilda`, `nome`, `tipo`, `immagine`, `url_sito`, `statuto`, `visibile`) VALUES
(1, 'Guardia cittadina', '1', 'standard_gilda.png', '', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `nome_interessato` varchar(255) NOT NULL DEFAULT '',
  `autore` varchar(255) NOT NULL DEFAULT '',
  `data_evento` datetime NOT NULL DEFAULT current_timestamp(),
  `codice_evento` varchar(255) NOT NULL DEFAULT '',
  `descrizione_evento` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `log`
--

INSERT INTO `log` (`id`, `nome_interessato`, `autore`, `data_evento`, `codice_evento`, `descrizione_evento`) VALUES
(1, 'Super', '::1', '2021-11-15 12:20:50', '2', '::1'),
(2, 'Super', 'doppio (ip)', '2021-11-15 18:15:22', '3', 'Super'),
(3, 'Super', '::1', '2021-11-15 18:15:22', '2', '::1'),
(4, 'Super', 'doppio (ip)', '2021-11-15 18:43:47', '3', 'Super'),
(5, 'Super', '::1', '2021-11-15 18:43:47', '2', '::1'),
(6, 'Aaaa', 'Super', '2021-11-15 18:44:21', '9', '::1'),
(7, 'Aaaa', 'doppio (cookie)', '2021-11-15 18:44:32', '3', 'Super'),
(8, 'Aaaa', '::1', '2021-11-15 18:44:32', '2', '::1'),
(9, 'Super', 'doppio (cookie)', '2021-11-15 18:57:12', '3', 'Aaaa'),
(10, 'Super', '::1', '2021-11-15 18:57:12', '2', '::1'),
(11, 'Qqq', 'Super', '2021-11-15 18:57:26', '9', '::1'),
(12, 'Qqq', 'doppio (cookie)', '2021-11-15 18:57:37', '3', 'Super'),
(13, 'Qqq', '::1', '2021-11-15 18:57:37', '2', '::1'),
(14, 'Qqq', 'doppio (ip)', '2021-11-15 19:03:14', '3', 'Qqq'),
(15, 'Qqq', '::1', '2021-11-15 19:03:14', '2', '::1'),
(16, 'Super', 'doppio (cookie)', '2021-11-15 19:24:37', '3', 'Qqq'),
(17, 'Super', '::1', '2021-11-15 19:24:37', '2', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `mappa`
--

CREATE TABLE `mappa` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  `stato` varchar(255) NOT NULL DEFAULT '',
  `pagina` varchar(255) DEFAULT 'nulla.php',
  `chat` tinyint(1) NOT NULL DEFAULT 1,
  `immagine` varchar(255) DEFAULT 'standard_luogo.png',
  `stanza_apparente` varchar(255) DEFAULT NULL,
  `id_mappa` int(11) DEFAULT 0,
  `link_immagine` varchar(255) NOT NULL,
  `link_immagine_hover` varchar(255) NOT NULL,
  `id_mappa_collegata` int(11) NOT NULL DEFAULT 0,
  `x_cord` int(11) DEFAULT 0,
  `y_cord` int(11) DEFAULT 0,
  `invitati` text NOT NULL,
  `privata` tinyint(1) NOT NULL DEFAULT 0,
  `proprietario` varchar(255) DEFAULT NULL,
  `ora_prenotazione` datetime DEFAULT NULL,
  `scadenza` datetime DEFAULT NULL,
  `costo` int(11) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `mappa`
--

INSERT INTO `mappa` (`id`, `nome`, `descrizione`, `stato`, `pagina`, `chat`, `immagine`, `stanza_apparente`, `id_mappa`, `link_immagine`, `link_immagine_hover`, `id_mappa_collegata`, `x_cord`, `y_cord`, `invitati`, `privata`, `proprietario`, `ora_prenotazione`, `scadenza`, `costo`) VALUES
(1, 'Strada', 'Via che congiunge la periferia al centro.', 'Nella norma', '', 1, 'standard_luogo.png', '', 1, '', '', 0, 180, 150, '', 0, 'Nessuno', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 0),
(2, 'Piazza', 'Piccola piazza con panchine ed una fontana al centro.', 'Nella norma', '', 1, 'standard_luogo.png', '', 1, '', '', 0, 80, 150, '', 0, 'Nessuno', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `mappa_click`
--

CREATE TABLE `mappa_click` (
  `id_click` int(11) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `immagine` varchar(255) NOT NULL DEFAULT 'standard_mappa.png',
  `posizione` int(11) NOT NULL DEFAULT 0,
  `mobile` tinyint(1) NOT NULL DEFAULT 0,
  `meteo` varchar(255) NOT NULL DEFAULT '20Â°c - sereno',
  `larghezza` smallint(6) NOT NULL DEFAULT 500,
  `altezza` smallint(6) NOT NULL DEFAULT 330
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `mappa_click`
--

INSERT INTO `mappa_click` (`id_click`, `nome`, `immagine`, `posizione`, `mobile`, `meteo`, `larghezza`, `altezza`) VALUES
(1, 'Mappa principale', 'spacer.gif', 2, 0, '20Â°c - sereno', 500, 330),
(2, 'Mappa secondaria', 'spacer.gif', 2, 0, '18Â°c - nuvoloso', 500, 330);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `menu_name` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `page` varchar(255) NOT NULL,
  `permission` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `menu_name`, `section`, `name`, `page`, `permission`) VALUES
(1, 'Gestione', 'Log', 'Log Chat', 'log_chat', 'LOG_CHAT'),
(2, 'Gestione', 'Log', 'Log Eventi', 'log_eventi', 'LOG_EVENTI'),
(3, 'Gestione', 'Log', 'Log Messaggi', 'log_messaggi', 'LOG_MESSAGGI'),
(4, 'Gestione', 'Abilità', 'Gestione Abilità', 'gestione_abilita', 'MANAGE_ABILITY'),
(5, 'Gestione', 'Abilità', 'Dati Extra Abilità', 'gestione_abilita_extra', 'MANAGE_ABILITY_EXTRA'),
(6, 'Gestione', 'Abilità', 'Requisiti abilità', 'gestione_abilita_requisiti', 'MANAGE_ABILITY_REQUIREMENT'),
(7, 'Gestione', 'Locations', 'Gestione Luoghi', 'gestione_luoghi', 'MANAGE_LOCATIONS'),
(8, 'Gestione', 'Locations', 'Gestione Mappe', 'gestione_mappe', 'MANAGE_MAPS'),
(9, 'Gestione', 'Documentazioni', 'Gestione Ambientazione', 'gestione_ambientazione', 'MANAGE_AMBIENT'),
(10, 'Gestione', 'Documentazioni', 'Gestione Regolamento', 'gestione_regolamento', 'MANAGE_RULES'),
(11, 'Gestione', 'Razze', 'Gestione Razze', 'gestione_razze', 'MANAGE_RACES'),
(12, 'Gestione', 'Bacheche', 'Gestione Bacheche', 'gestione_bacheche', 'MANAGE_FORUMS'),
(13, 'Gestione', 'Gilde', 'Gestione Gilde e Ruoli', 'gestione_gilde', 'MANAGE_GUILDS'),
(14, 'Gestione', 'Gestione', 'Gestione Costanti', 'gestione_costanti', 'MANAGE_CONSTANTS'),
(15, 'Gestione', 'Gestione', 'Gestione Versioni Database', 'gestione_db_migrations', 'MANAGE_DB_MIGRATIONS'),
(16, 'Gestione', 'Permessi', 'Gestione Permessi', 'gestione_permessi', 'MANAGE_PERMISSIONS'),
(17, 'Gestione', 'Gestione', 'Manutenzione', 'gestione_manutenzione', 'MANAGE_MANUTENTIONS'),
(18, 'Gestione', 'Oggetti', 'Gestione Oggetti', 'gestione_oggetti', 'MANAGE_OBJECTS'),
(19, 'Gestione', 'Chat', 'Giocate Segnalate', 'gestione_segnalazioni', 'MANAGE_REPORTS'),
(20, 'Gestione', 'Chat', 'Esiti in chat', 'gestione_esiti', 'MANAGE_OUTCOMES'),
(21, 'Gestione', 'Quest', 'Gestione Quest', 'gestione_quest', 'MANAGE_QUESTS'),
(22, 'Gestione', 'Quest', 'Gestione Trame', 'gestione_trame', 'MANAGE_TRAME_VIEW'),
(23, 'Gestione', 'Esiti', 'Esiti', 'gestione_esiti', 'MANAGE_ESITI'),
(24, 'Gestione', 'Classi', 'Gestione Classi', 'gestione_classi', 'MANAGE_CLASSES');

-- --------------------------------------------------------

--
-- Table structure for table `mercato`
--

CREATE TABLE `mercato` (
  `id_oggetto` int(11) NOT NULL,
  `numero` int(11) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `messaggi`
--

CREATE TABLE `messaggi` (
  `id` bigint(20) NOT NULL,
  `mittente` varchar(255) NOT NULL,
  `destinatario` varchar(255) NOT NULL DEFAULT 'Nessuno',
  `spedito` datetime NOT NULL DEFAULT current_timestamp(),
  `letto` tinyint(1) DEFAULT 0,
  `mittente_del` tinyint(1) DEFAULT 0,
  `destinatario_del` tinyint(1) DEFAULT 0,
  `tipo` int(11) NOT NULL DEFAULT 0,
  `oggetto` text DEFAULT NULL,
  `testo` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `messaggi`
--

INSERT INTO `messaggi` (`id`, `mittente`, `destinatario`, `spedito`, `letto`, `mittente_del`, `destinatario_del`, `tipo`, `oggetto`, `testo`) VALUES
(1, 'Webmaster', 'Aaaa', '2021-11-15 16:00:25', 0, 0, 1, 0, NULL, 'Lo staff è lieto di darti il benvenuto e ti augura buon divertimento! Hai già letto lambientazione ed il regolameto? Se non è così ti invitiamo a farlo al più presto!.'),
(2, 'Webmaster', 'Qqq', '2021-11-15 18:56:58', 0, 0, 1, 0, NULL, 'Lo staff è lieto di darti il benvenuto e ti augura buon divertimento! Hai già letto lambientazione ed il regolameto? Se non è così ti invitiamo a farlo al più presto!.');

-- --------------------------------------------------------

--
-- Table structure for table `messaggioaraldo`
--

CREATE TABLE `messaggioaraldo` (
  `id_messaggio` bigint(20) NOT NULL,
  `id_messaggio_padre` bigint(20) NOT NULL DEFAULT 0,
  `id_araldo` int(11) DEFAULT NULL,
  `titolo` varchar(255) DEFAULT NULL,
  `messaggio` text DEFAULT NULL,
  `autore` varchar(255) DEFAULT NULL,
  `data_messaggio` datetime DEFAULT NULL,
  `data_ultimo_messaggio` datetime DEFAULT NULL,
  `importante` binary(1) NOT NULL DEFAULT '0',
  `chiuso` binary(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Table structure for table `oggetto`
--

CREATE TABLE `oggetto` (
  `id_oggetto` int(11) NOT NULL,
  `tipo` int(11) NOT NULL DEFAULT 0,
  `nome` varchar(255) NOT NULL DEFAULT 'Sconosciuto',
  `creatore` varchar(255) NOT NULL DEFAULT 'System Op',
  `data_inserimento` datetime NOT NULL DEFAULT current_timestamp(),
  `descrizione` varchar(255) NOT NULL DEFAULT 'Nessuna',
  `ubicabile` int(11) NOT NULL DEFAULT 0,
  `costo` int(11) NOT NULL DEFAULT 0,
  `difesa` int(11) NOT NULL DEFAULT 0,
  `attacco` int(11) NOT NULL DEFAULT 0,
  `cariche` varchar(255) NOT NULL DEFAULT '0',
  `bonus_car0` int(11) NOT NULL DEFAULT 0,
  `bonus_car1` int(11) NOT NULL DEFAULT 0,
  `bonus_car2` int(11) NOT NULL DEFAULT 0,
  `bonus_car3` int(11) NOT NULL DEFAULT 0,
  `bonus_car4` int(11) NOT NULL DEFAULT 0,
  `bonus_car5` int(11) NOT NULL DEFAULT 0,
  `urlimg` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `oggetto`
--

INSERT INTO `oggetto` (`id_oggetto`, `tipo`, `nome`, `creatore`, `data_inserimento`, `descrizione`, `ubicabile`, `costo`, `difesa`, `attacco`, `cariche`, `bonus_car0`, `bonus_car1`, `bonus_car2`, `bonus_car3`, `bonus_car4`, `bonus_car5`, `urlimg`) VALUES
(1, 6, 'Scopa', 'Super', '2009-12-20 14:29:33', 'Una comune scopa di saggina.', 0, 10, 0, 0, '0', 0, 0, 0, 0, 0, 0, 'standard_oggetto.png');

-- --------------------------------------------------------

--
-- Table structure for table `permessi_custom`
--

CREATE TABLE `permessi_custom` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `permessi_custom`
--

INSERT INTO `permessi_custom` (`id`, `permission_name`, `description`) VALUES
(1, 'LOG_CHAT', 'Permesso visualizzazione log chat'),
(2, 'LOG_EVENTI', 'Permesso visualizzazione log evento'),
(3, 'LOG_MESSAGGI', 'Permesso visualizzazione log messaggi'),
(4, 'MANAGE_ABILITY', 'Permesso gestione abilità'),
(5, 'MANAGE_ABILITY_EXTRA', 'Permesso gestione abilità dati extra'),
(6, 'MANAGE_ABILITY_REQUIREMENT', 'Permesso gestione requisiti abilità '),
(7, 'MANAGE_DB_MIGRATIONS', 'Permesso gestione versioni del database'),
(8, 'MANAGE_LOCATIONS', 'Permesso gestione luoghi'),
(9, 'MANAGE_MAPS', 'Permesso gestione mappe'),
(10, 'MANAGE_AMBIENT', 'Gestione ambientazione'),
(11, 'MANAGE_RULES', 'Gestione regolamento'),
(12, 'MANAGE_CONSTANTS', 'Permesso per l\'editing delle costanti'),
(13, 'MANAGE_RACES', 'Permesso per la gestione delle razze'),
(14, 'MANAGE_FORUMS', 'Permesso per la gestione delle bacheche'),
(15, 'MANAGE_GUILDS', 'Permesso per la gestione delle gilde'),
(16, 'MANAGE_PERMISSIONS', 'Permesso per la gestione dei permessi'),
(17, 'MANAGE_MANUTENTIONS', 'Permesso per la gestione della manutenzione del db'),
(18, 'MANAGE_OBJECTS', 'Permesso per la gestione degli oggetti'),
(19, 'MANAGE_REPORTS', 'Permesso per la gestione delle giocate segnalate'),
(20, 'MANAGE_ESITI', 'Permesso per la gestione base degli esiti'),
(21, 'MANAGE_ALL_ESITI', 'Permesso per la visione/modifica di qualsiasi tipo di esito'),
(22, 'MANAGE_OUTCOMES', 'Permesso per la gestione degli esiti in chat'),
(23, 'MANAGE_QUESTS', 'Permesso per la gestione delle quest'),
(24, 'MANAGE_QUESTS_OTHER', 'Permesso per la gestione delle quest altrui'),
(25, 'MANAGE_TRAME_VIEW', 'Permesso per la visualizzazione delle trame'),
(26, 'MANAGE_TRAME', 'Permesso per la modifica delle trame'),
(27, 'MANAGE_TRAME_OTHER', 'Permesso per la modifica delle trame degli altri'),
(28, 'SCHEDA_EXP_VIEW', 'Permesso per la visualizzazione della pagina esperienza in scheda'),
(29, 'SCHEDA_EXP_MANAGE', 'Permesso per la visualizzazione della pagina esperienza in scheda'),
(30, 'MANAGE_CLASSES', 'Gestione classi di gioco');

-- --------------------------------------------------------

--
-- Table structure for table `permessi_group`
--

CREATE TABLE `permessi_group` (
  `id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `superuser` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `permessi_group`
--

INSERT INTO `permessi_group` (`id`, `group_name`, `description`, `superuser`) VALUES
(1, 'MASTER', 'Gruppo permessi master', 0),
(2, 'MODERATOR', 'Gruppo permessi moderatore', 0),
(3, 'SUPERUSER', 'Gruppo Permessi superuser', 1),
(4, 'USER', 'Permessi gruppo user', 0);

-- --------------------------------------------------------

--
-- Table structure for table `permessi_group_assignment`
--

CREATE TABLE `permessi_group_assignment` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `permission` int(11) NOT NULL,
  `assigned_by` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `permessi_group_personaggio`
--

CREATE TABLE `permessi_group_personaggio` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `personaggio` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `permessi_group_personaggio`
--

INSERT INTO `permessi_group_personaggio` (`id`, `group_id`, `personaggio`) VALUES
(1, 3, '1');

-- --------------------------------------------------------

--
-- Table structure for table `permessi_personaggio`
--

CREATE TABLE `permessi_personaggio` (
  `id` int(11) NOT NULL,
  `personaggio` varchar(255) NOT NULL,
  `permission` int(11) NOT NULL,
  `assigned_by` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `personaggio`
--

CREATE TABLE `personaggio` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL DEFAULT '',
  `cognome` varchar(255) NOT NULL DEFAULT '-',
  `pass` varchar(255) NOT NULL DEFAULT '',
  `ultimo_cambiopass` datetime DEFAULT NULL,
  `data_iscrizione` datetime DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `permessi` tinyint(1) DEFAULT 0,
  `ultima_mappa` int(11) NOT NULL DEFAULT 1,
  `ultimo_luogo` int(11) NOT NULL DEFAULT -1,
  `esilio` date NOT NULL DEFAULT '2009-07-01',
  `data_esilio` date NOT NULL DEFAULT '2009-07-01',
  `motivo_esilio` varchar(255) DEFAULT NULL,
  `autore_esilio` varchar(255) DEFAULT NULL,
  `sesso` varchar(255) DEFAULT 'm',
  `id_razza` int(11) DEFAULT 1000,
  `id_classe` int(11) DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  `affetti` text DEFAULT NULL,
  `storia` text DEFAULT NULL,
  `stato` varchar(255) DEFAULT 'nessuna',
  `online_status` varchar(255) DEFAULT NULL,
  `disponibile` tinyint(1) NOT NULL DEFAULT 1,
  `url_img` varchar(255) DEFAULT 'imgs/avatars/empty.png',
  `url_img_chat` varchar(255) NOT NULL DEFAULT ' ',
  `url_media` varchar(255) DEFAULT NULL,
  `blocca_media` binary(1) NOT NULL DEFAULT '0',
  `esperienza` decimal(12,4) DEFAULT 0.0000,
  `car0` int(11) NOT NULL DEFAULT 5,
  `car1` int(11) NOT NULL DEFAULT 5,
  `car2` int(11) NOT NULL DEFAULT 5,
  `car3` int(11) NOT NULL DEFAULT 5,
  `car4` int(11) NOT NULL DEFAULT 5,
  `car5` int(11) NOT NULL DEFAULT 5,
  `salute` int(11) NOT NULL DEFAULT 100,
  `salute_max` int(11) NOT NULL DEFAULT 100,
  `data_ultima_gilda` datetime NOT NULL DEFAULT '2009-07-01 00:00:00',
  `soldi` int(11) DEFAULT 50,
  `banca` int(11) DEFAULT 0,
  `ultimo_stipendio` date NOT NULL DEFAULT '2009-07-01',
  `last_ip` varchar(255) DEFAULT NULL,
  `is_invisible` tinyint(1) NOT NULL DEFAULT 0,
  `ultimo_refresh` datetime NOT NULL DEFAULT '2009-07-01 00:00:00',
  `ora_entrata` datetime NOT NULL DEFAULT '2009-07-01 00:00:00',
  `ora_uscita` datetime NOT NULL DEFAULT current_timestamp(),
  `posizione` int(11) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `personaggio`
--

INSERT INTO `personaggio` (`id`, `nome`, `cognome`, `pass`, `ultimo_cambiopass`, `data_iscrizione`, `email`, `permessi`, `ultima_mappa`, `ultimo_luogo`, `esilio`, `data_esilio`, `motivo_esilio`, `autore_esilio`, `sesso`, `id_razza`, `id_classe`, `descrizione`, `affetti`, `storia`, `stato`, `online_status`, `disponibile`, `url_img`, `url_img_chat`, `url_media`, `blocca_media`, `esperienza`, `car0`, `car1`, `car2`, `car3`, `car4`, `car5`, `salute`, `salute_max`, `data_ultima_gilda`, `soldi`, `banca`, `ultimo_stipendio`, `last_ip`, `is_invisible`, `ultimo_refresh`, `ora_entrata`, `ora_uscita`, `posizione`) VALUES
(1, 'Super', 'User', '$P$BcH1cP941XHOf0X61wVWWjzXqcCi2a/', NULL, '2011-06-04 00:47:48', '$P$BNZYtz9JOQE.O4Tv7qZyl3SzIoZzzR.', 5, 1, -1, '2009-01-01', '2009-01-01', '', '', 'm', 1000, NULL, '', '', NULL, 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', 0x30, '1000.0000', 7, 8, 6, 5, 6, 5, 100, 100, '2009-01-01 00:00:00', 300, 50000, '2021-11-15', '::1', 0, '2021-11-15 20:05:56', '2021-11-15 19:24:37', '2021-11-15 21:15:29', 1),
(2, 'Test', 'Di FunzionaliÃ ', '$P$BUoa19QUuXsgIDlhGC3chR/3Q7hoRy0', NULL, '2011-06-04 00:47:48', '$P$Bd1amPCKkOF9GdgYsibZ96U92D5CtR0', 0, 1, -1, '2009-01-01', '2009-01-01', '', '', 'm', 1000, NULL, '', '', NULL, 'Nella norma', '', 1, 'imgs/avatars/empty.png', '', '', 0x30, '1000.0000', 7, 8, 6, 5, 6, 5, 100, 100, '2009-01-01 00:00:00', 50, 50, '2009-01-01', '127.0.0.1', 0, '2009-01-01 00:00:00', '2009-01-01 00:00:00', '2009-01-01 00:00:00', 1),
(3, 'Aaaa', 'Aaaa', '$P$Btl6kaKpFbRITXn9ltHYdRy6EgHjSK.', '2021-11-15 18:44:21', '2021-11-15 16:00:23', '$P$BHRVSHKmRY6P4edzLQALkrXvu3aLoP.', 0, 1, -1, '2009-07-01', '2009-07-01', NULL, NULL, 'm', 1000, 1000, NULL, NULL, NULL, 'nessuna', NULL, 1, 'imgs/avatars/empty.png', ' ', NULL, 0x30, '100.0000', 10, 10, 5, 5, 5, 5, 100, 100, '2009-07-01 00:00:00', 50, 0, '2021-11-15', '::1', 0, '2021-11-15 18:53:24', '2021-11-15 18:44:32', '2021-11-15 18:56:16', 1),
(4, 'Qqq', 'Qqq', '$P$BqsuoAYDMnJtVU74mYsKhQJKbnODpg0', '2021-11-15 18:57:26', '2021-11-15 18:56:56', '$P$Bscfra/S64qios6yvrcVS/.Ijrrhh21', 0, 1, -1, '2009-07-01', '2009-07-01', NULL, NULL, 'm', 1000, 1000, NULL, NULL, NULL, 'nessuna', NULL, 1, 'imgs/avatars/empty.png', ' ', NULL, 0x30, '100.0000', 10, 10, 5, 5, 5, 5, 100, 100, '2009-07-01 00:00:00', 50, 0, '2021-11-15', '::1', 0, '2021-11-15 19:12:22', '2021-11-15 19:03:14', '2021-11-15 19:24:24', 1);

-- --------------------------------------------------------

--
-- Table structure for table `personaggio_quest`
--

CREATE TABLE `personaggio_quest` (
  `id` int(11) NOT NULL,
  `id_quest` int(11) NOT NULL,
  `data` datetime NOT NULL DEFAULT current_timestamp(),
  `commento` text NOT NULL,
  `personaggio` int(11) NOT NULL,
  `px_assegnati` int(11) NOT NULL,
  `autore` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `quest`
--

CREATE TABLE `quest` (
  `id` int(11) NOT NULL,
  `titolo` text NOT NULL,
  `partecipanti` text NOT NULL,
  `descrizione` text NOT NULL,
  `trama` int(11) NOT NULL DEFAULT 0,
  `data` datetime NOT NULL DEFAULT current_timestamp(),
  `autore` varchar(255) NOT NULL,
  `autore_modifica` varchar(255) DEFAULT NULL,
  `ultima_modifica` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `quest_trama`
--

CREATE TABLE `quest_trama` (
  `id` int(11) NOT NULL,
  `titolo` varchar(255) NOT NULL,
  `descrizione` text NOT NULL,
  `data` datetime NOT NULL DEFAULT current_timestamp(),
  `autore` varchar(255) DEFAULT NULL,
  `autore_modifica` varchar(255) DEFAULT NULL,
  `ultima_modifica` datetime DEFAULT NULL,
  `stato` int(11) NOT NULL DEFAULT 0,
  `quests` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `razza`
--

CREATE TABLE `razza` (
  `id_razza` int(11) NOT NULL,
  `nome_razza` varchar(255) NOT NULL DEFAULT '',
  `sing_m` varchar(255) NOT NULL DEFAULT '',
  `sing_f` varchar(255) NOT NULL DEFAULT '',
  `descrizione` text NOT NULL,
  `bonus_car0` int(11) NOT NULL DEFAULT 0,
  `bonus_car1` int(11) NOT NULL DEFAULT 0,
  `bonus_car2` int(11) NOT NULL DEFAULT 0,
  `bonus_car3` int(11) NOT NULL DEFAULT 0,
  `bonus_car4` int(11) NOT NULL DEFAULT 0,
  `bonus_car5` int(11) NOT NULL DEFAULT 0,
  `immagine` varchar(255) NOT NULL DEFAULT 'standard_razza.png',
  `icon` varchar(255) NOT NULL DEFAULT 'standard_razza.png',
  `url_site` varchar(255) DEFAULT NULL,
  `iscrizione` tinyint(1) NOT NULL DEFAULT 1,
  `visibile` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `razza`
--

INSERT INTO `razza` (`id_razza`, `nome_razza`, `sing_m`, `sing_f`, `descrizione`, `bonus_car0`, `bonus_car1`, `bonus_car2`, `bonus_car3`, `bonus_car4`, `bonus_car5`, `immagine`, `icon`, `url_site`, `iscrizione`, `visibile`) VALUES
(1000, 'Umani', 'Umano', 'Umana', '', 0, 0, 0, 0, 0, 0, 'standard_razza.png', 'standard_razza.png', '', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `regolamento`
--

CREATE TABLE `regolamento` (
  `articolo` int(11) NOT NULL,
  `titolo` varchar(255) NOT NULL,
  `testo` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ruolo`
--

CREATE TABLE `ruolo` (
  `id_ruolo` int(11) NOT NULL,
  `gilda` int(11) NOT NULL DEFAULT -1,
  `nome_ruolo` varchar(255) NOT NULL,
  `immagine` varchar(255) NOT NULL,
  `stipendio` int(11) NOT NULL DEFAULT 0,
  `capo` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ruolo`
--

INSERT INTO `ruolo` (`id_ruolo`, `gilda`, `nome_ruolo`, `immagine`, `stipendio`, `capo`) VALUES
(1, 1, 'Capitano della guardia', 'standard_gilda.png', 100, 1),
(2, 1, 'Ufficiale della guardia', 'standard_gilda.png', 70, 0),
(5, -1, 'Lavoratore', 'standard_gilda.png', 5, 0),
(3, 1, 'Soldato della guardia', 'standard_gilda.png', 40, 0),
(4, 1, 'Recluta della guardia', 'standard_gilda.png', 15, 0);

-- --------------------------------------------------------

--
-- Table structure for table `segnalazione_role`
--

CREATE TABLE `segnalazione_role` (
  `id` bigint(20) NOT NULL,
  `stanza` int(11) NOT NULL,
  `conclusa` int(11) NOT NULL DEFAULT 0,
  `partecipanti` text DEFAULT NULL,
  `mittente` varchar(255) NOT NULL,
  `data_inizio` datetime DEFAULT NULL,
  `data_fine` datetime DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `quest` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `send_gm`
--

CREATE TABLE `send_gm` (
  `id` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `autore` varchar(255) NOT NULL,
  `role_reg` int(11) NOT NULL,
  `note` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `_gdrcd_db_versions`
--

CREATE TABLE `_gdrcd_db_versions` (
  `migration_id` varchar(255) NOT NULL,
  `applied_on` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `_gdrcd_db_versions`
--

INSERT INTO `_gdrcd_db_versions` (`migration_id`, `applied_on`) VALUES
('2020072500', '2021-11-15 11:42:48'),
('2021103018', '2021-11-15 11:42:48'),
('2021110101', '2021-11-15 11:42:48'),
('2021110102', '2021-11-15 11:42:48'),
('2021110103', '2021-11-15 11:42:48'),
('2021110104', '2021-11-15 11:42:48'),
('2021110105', '2021-11-15 11:42:48'),
('2021110106', '2021-11-15 11:42:48'),
('2021110107', '2021-11-15 11:42:48'),
('2021110108', '2021-11-15 11:42:48'),
('2021110109', '2021-11-15 11:42:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abilita`
--
ALTER TABLE `abilita`
  ADD PRIMARY KEY (`id_abilita`);

--
-- Indexes for table `abilita_extra`
--
ALTER TABLE `abilita_extra`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `abilita_requisiti`
--
ALTER TABLE `abilita_requisiti`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `araldo`
--
ALTER TABLE `araldo`
  ADD PRIMARY KEY (`id_araldo`);

--
-- Indexes for table `araldo_letto`
--
ALTER TABLE `araldo_letto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nome` (`nome`,`thread_id`);

--
-- Indexes for table `backmessaggi`
--
ALTER TABLE `backmessaggi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blacklist`
--
ALTER TABLE `blacklist`
  ADD PRIMARY KEY (`ip`),
  ADD KEY `Ora` (`ora`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Stanza` (`stanza`);

--
-- Indexes for table `classe`
--
ALTER TABLE `classe`
  ADD PRIMARY KEY (`id_classe`);

--
-- Indexes for table `clgpersonaggiomostrine`
--
ALTER TABLE `clgpersonaggiomostrine`
  ADD PRIMARY KEY (`id_mostrina`);

--
-- Indexes for table `clgpersonaggiooggetto`
--
ALTER TABLE `clgpersonaggiooggetto`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `codmostrina`
--
ALTER TABLE `codmostrina`
  ADD PRIMARY KEY (`id_mostrina`);

--
-- Indexes for table `codtipogilda`
--
ALTER TABLE `codtipogilda`
  ADD PRIMARY KEY (`cod_tipo`);

--
-- Indexes for table `codtipooggetto`
--
ALTER TABLE `codtipooggetto`
  ADD PRIMARY KEY (`cod_tipo`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `diario`
--
ALTER TABLE `diario`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `esiti`
--
ALTER TABLE `esiti`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `esiti_personaggio`
--
ALTER TABLE `esiti_personaggio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `esiti_risposte`
--
ALTER TABLE `esiti_risposte`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `esiti_risposte_cd`
--
ALTER TABLE `esiti_risposte_cd`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `esiti_risposte_letture`
--
ALTER TABLE `esiti_risposte_letture`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `esiti_risposte_risultati`
--
ALTER TABLE `esiti_risposte_risultati`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gilda`
--
ALTER TABLE `gilda`
  ADD PRIMARY KEY (`id_gilda`);

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mappa`
--
ALTER TABLE `mappa`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `mappa` ADD FULLTEXT KEY `Invitati` (`invitati`);

--
-- Indexes for table `mappa_click`
--
ALTER TABLE `mappa_click`
  ADD PRIMARY KEY (`id_click`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mercato`
--
ALTER TABLE `mercato`
  ADD PRIMARY KEY (`id_oggetto`);

--
-- Indexes for table `messaggi`
--
ALTER TABLE `messaggi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destinatario` (`destinatario`),
  ADD KEY `letto` (`letto`);

--
-- Indexes for table `messaggioaraldo`
--
ALTER TABLE `messaggioaraldo`
  ADD PRIMARY KEY (`id_messaggio`),
  ADD KEY `id_araldo` (`id_araldo`),
  ADD KEY `id_messaggio_padre` (`id_messaggio_padre`),
  ADD KEY `data_messaggio` (`data_messaggio`),
  ADD KEY `importante` (`importante`,`chiuso`);

--
-- Indexes for table `oggetto`
--
ALTER TABLE `oggetto`
  ADD PRIMARY KEY (`id_oggetto`),
  ADD KEY `Tipo` (`tipo`);

--
-- Indexes for table `permessi_custom`
--
ALTER TABLE `permessi_custom`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permessi_group`
--
ALTER TABLE `permessi_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permessi_group_assignment`
--
ALTER TABLE `permessi_group_assignment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permessi_group_personaggio`
--
ALTER TABLE `permessi_group_personaggio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permessi_personaggio`
--
ALTER TABLE `permessi_personaggio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personaggio`
--
ALTER TABLE `personaggio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDRazza` (`id_razza`),
  ADD KEY `Esilio` (`esilio`),
  ADD KEY `id_classe` (`id_classe`);

--
-- Indexes for table `personaggio_quest`
--
ALTER TABLE `personaggio_quest`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quest`
--
ALTER TABLE `quest`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quest_trama`
--
ALTER TABLE `quest_trama`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `razza`
--
ALTER TABLE `razza`
  ADD PRIMARY KEY (`id_razza`);

--
-- Indexes for table `ruolo`
--
ALTER TABLE `ruolo`
  ADD PRIMARY KEY (`id_ruolo`);

--
-- Indexes for table `segnalazione_role`
--
ALTER TABLE `segnalazione_role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `send_gm`
--
ALTER TABLE `send_gm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `_gdrcd_db_versions`
--
ALTER TABLE `_gdrcd_db_versions`
  ADD PRIMARY KEY (`migration_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abilita`
--
ALTER TABLE `abilita`
  MODIFY `id_abilita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `abilita_extra`
--
ALTER TABLE `abilita_extra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `abilita_requisiti`
--
ALTER TABLE `abilita_requisiti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `araldo`
--
ALTER TABLE `araldo`
  MODIFY `id_araldo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `araldo_letto`
--
ALTER TABLE `araldo_letto`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backmessaggi`
--
ALTER TABLE `backmessaggi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classe`
--
ALTER TABLE `classe`
  MODIFY `id_classe` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1001;

--
-- AUTO_INCREMENT for table `clgpersonaggiooggetto`
--
ALTER TABLE `clgpersonaggiooggetto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `codmostrina`
--
ALTER TABLE `codmostrina`
  MODIFY `id_mostrina` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `codtipogilda`
--
ALTER TABLE `codtipogilda`
  MODIFY `cod_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `codtipooggetto`
--
ALTER TABLE `codtipooggetto`
  MODIFY `cod_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `diario`
--
ALTER TABLE `diario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `esiti`
--
ALTER TABLE `esiti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `esiti_personaggio`
--
ALTER TABLE `esiti_personaggio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `esiti_risposte`
--
ALTER TABLE `esiti_risposte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `esiti_risposte_cd`
--
ALTER TABLE `esiti_risposte_cd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `esiti_risposte_letture`
--
ALTER TABLE `esiti_risposte_letture`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `esiti_risposte_risultati`
--
ALTER TABLE `esiti_risposte_risultati`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gilda`
--
ALTER TABLE `gilda`
  MODIFY `id_gilda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `mappa`
--
ALTER TABLE `mappa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mappa_click`
--
ALTER TABLE `mappa_click`
  MODIFY `id_click` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `messaggi`
--
ALTER TABLE `messaggi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messaggioaraldo`
--
ALTER TABLE `messaggioaraldo`
  MODIFY `id_messaggio` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oggetto`
--
ALTER TABLE `oggetto`
  MODIFY `id_oggetto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permessi_custom`
--
ALTER TABLE `permessi_custom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `permessi_group`
--
ALTER TABLE `permessi_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `permessi_group_assignment`
--
ALTER TABLE `permessi_group_assignment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permessi_group_personaggio`
--
ALTER TABLE `permessi_group_personaggio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permessi_personaggio`
--
ALTER TABLE `permessi_personaggio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personaggio`
--
ALTER TABLE `personaggio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `personaggio_quest`
--
ALTER TABLE `personaggio_quest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quest`
--
ALTER TABLE `quest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quest_trama`
--
ALTER TABLE `quest_trama`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `razza`
--
ALTER TABLE `razza`
  MODIFY `id_razza` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1001;

--
-- AUTO_INCREMENT for table `ruolo`
--
ALTER TABLE `ruolo`
  MODIFY `id_ruolo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `segnalazione_role`
--
ALTER TABLE `segnalazione_role`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `send_gm`
--
ALTER TABLE `send_gm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
