<?php

/*Livelli di accesso utente*/
define('DELETED', -1);
define('USER', 0);
define('SUPERUSER', 4);
define('MODERATOR', 3);
define('GAMEMASTER', 2);
define('GUILDMODERATOR', 1);

/*Codici di log*/
define('BLOCKED', 1);
define('LOGGEDIN', 2);
define('ACCOUNTMULTIPLO', 3);
define('ERRORELOGIN', 4);
define('BONIFICO', 5);
define('NUOVOLAVORO', 6);
define('DIMISSIONE', 7);
define('CHANGEDROLE', 8);
define('CHANGEDPASS', 9);
define('PX', 10);
define('DELETEPG', 11);
define('CHANGEDNAME', 12);

/*Stati di disponibilità*/
define('NONDISPONIBILE', 0);
define('DISPONIBILE', 1);
define('SOLOSUINVITO', 2);

/*Tipi di forum*/
define('INGIOCO', 0);
define('PERTUTTI', 1);
define('SOLORAZZA', 2);
define('SOLOGILDA', 3);
define('SOLOMASTERS', 4);
define('SOLOMODERATORS', 5);

/*Posizione degli oggetti*/
define('INVENTARIO', 0);
define('ZAINO', 1);
define('MANODX', 2);
define('MANOSX', 3);
define('TORSO', 4);
define('GAMBE', 5);
define('PIEDI', 6);
define('TESTA', 7);
define('ANELLO', 8);
define('COLLO', 9);

/*Stati della mappa*/
define('INVIAGGIO', -1);

/**
 * Livelli di filtro html
 */
define('HTML_FILTER_BASE', 0);
define('HTML_FILTER_HIGH', 1);

/**
 * Codice interno dei messaggi in chat
 */

/** @var string Messaggio di tipo Parlato */
const GDRCD_CHAT_MESSAGE_TYPE = 'P';

/** @var string Messaggio di tipo Azione */
const GDRCD_CHAT_ACTION_TYPE = 'A';

/** @var string Messaggio di tipo Sussurro */
const GDRCD_CHAT_WHISPER_TYPE = 'S';

/** @var string Messaggio di tipo Tiro su Caratteristica */
const GDRCD_CHAT_STATS_TYPE = 'C';

/** @var string Messaggio di tipo Tiro su Abilità */
const GDRCD_CHAT_SKILL_TYPE = 'F';

/** @var string Messaggio di tipo Tiro di Dado */
const GDRCD_CHAT_DICE_TYPE = 'D';

/** @var string Messaggio di tipo Utilizzo Oggetto */
const GDRCD_CHAT_ITEM_TYPE = 'O';

/** @var string Messaggio di tipo Master Fato */
const GDRCD_CHAT_MASTER_TYPE = 'M';

/** @var string Messaggio di tipo Azione PNG */
const GDRCD_CHAT_PNG_TYPE = 'N';

/** @var string Messaggio di tipo Immagine */
const GDRCD_CHAT_IMAGE_TYPE = 'I';

/** @var string Messaggio di tipo invito in chat privata */
const GDRCD_CHAT_PRIVATE_INVITE_TYPE = 'X';

/** @var string Messaggio di tipo caccia da chat privata */
const GDRCD_CHAT_PRIVATE_KICK_TYPE = 'Y';

/** @var string Messaggio di tipo elenca invitati chat privata */
const GDRCD_CHAT_PRIVATE_LIST_TYPE = 'Z';

/** @var string Tipologia di messaggio di default quando un utente invia un azione senza indicarne esplicitamente il tipo */
const GDRCD_CHAT_DEFAULT_TYPE = GDRCD_CHAT_MESSAGE_TYPE;

/**
 * Codice utente dei messaggi in chat.
 * Rappresentano il primo carattere che gli utenti
 * possono anteporre alla propria azione in chat
 * per usare una specifica formattazione
 */

/** @var string Symbolo per indicare i messaggi di tipo Parlato */
const GDRCD_CHAT_MESSAGE_SYMBOL = '.';

/** @var string Symbolo per indicare i messaggi di tipo Azione */
const GDRCD_CHAT_ACTION_SYMBOL = '+';

/** @var string Symbolo per indicare i messaggi di tipo Sussurro. Subito dopo va inserito il nome del destinatario e poi nuovamente lo stesso simbolo affinché abbia effetto */
const GDRCD_CHAT_WHISPER_SYMBOL = '@';

/** @var string Symbolo per indicare i messaggi di tipo Tiro su Caratteristica */
const GDRCD_CHAT_STATS_SYMBOL = '%';

/** @var string Symbolo per indicare i messaggi di tipo Tiro su Abilità */
const GDRCD_CHAT_SKILL_SYMBOL = '^';

/** @var string Symbolo per indicare i messaggi di tipo Tiro Dado. Permette di definire il lancio di un dado con le seguenti espressioni: d6, 3d6, 8d10-7. La sintassi è: (numero-di-dadi)d(facce-del-dado)-(evidenzia-se-risultato-pari-o-maggiore) */
const GDRCD_CHAT_DICE_SYMBOL = '#';

/** @var string Symbolo per indicare i messaggi di tipo Utilizzo Oggetto */
const GDRCD_CHAT_ITEM_SYMBOL = '=';

/** @var string Symbolo per indicare i messaggi di tipo Master */
const GDRCD_CHAT_MASTER_SYMBOL = '§';

/** @var string Symbolo per indicare i messaggi di tipo PNG. Funziona come per i sussurri */
const GDRCD_CHAT_PNG_SYMBOL = '$';

/** @var string Symbolo per indicare i messaggi di tipo Immagine. Permette di inserire la url di un immagine subito dopo il carattere */
const GDRCD_CHAT_IMAGE_SYMBOL = '*';

/** @var string Symbolo per indicare i messaggi di tipo invito in chat. Funziona come per i sussurri */
const GDRCD_CHAT_PRIVATE_INVITE_SYMBOL = '!';

/** @var string Symbolo per indicare i messaggi di tipo espelli da chat. Funziona come per i sussurri */
const GDRCD_CHAT_PRIVATE_KICK_SYMBOL = '_';

/** @var string Symbolo per indicare i messaggi di tipo elenco invitati in chat privata */
const GDRCD_CHAT_PRIVATE_LIST_SYMBOL = '?';


/**
 * Impostazioni registrazioni giocate
 */
//Azioni minime per poter registrare una giocata
CONST REG_MIN_AZIONI = 1;

//Attiva o disattiva il pacchetto intero (default: true)
const REG_ROLE = true;

//Attiva o disattiva il salvataggio delle role su pc (default: true)
const SAVE_ROLE = true;

//Attiva o disattiva la segnalazione ai GM e la relativa pagina gestionale (default: true)
const SEND_GM = true;

//definisce quali permessi hanno accesso alla lista delle giocate dei pg e alle segnalazioni GM (default: gamemaster)
const ROLE_PERM = GAMEMASTER;

//definisce quali permessi hanno accesso ai log chat (default: gamemaster)
const LOG_PERM = GAMEMASTER;

//definisce quali permessi possono modificare le registrazioni (default: gamemaster)
const EDIT_PERM = GAMEMASTER;

/**
 * Diari PG
 */

//Attiva o disattiva il diario del PG
CONST PG_DIARY_ENABLED = true;

//definisce quali permessi possono modificare le registrazioni (default: gamemaster)
const PERMESSI_DIARIO = MODERATOR;

/**
 * Fasi Lunari
 */
//Attiva le fasi lunari
const MOON = true;

/**
 * Impostazioni esiti
 */

//Attiva o disattiva il pacchetto intero (default: true)
const ESITI = true;

//Attiva o disattiva l'invio degli esiti in chat (default: false) -- Funzionalità in sviluppo
const ESITI_CHAT = true;

//Attiva o disattiva i tiri via esito (default: true)
const TIRI_ESITO = true;

//definisce quali permessi hanno accesso alla pagina di gestione degli esiti (default: gamemaster)
const ESITI_PERM = GAMEMASTER;

//definisce quali permessi hanno accesso alla visione di tutti gli esiti (default: moderator)
const FULL_PERM = MODERATOR;

/*Vettori globali dei parametri*/
$PARAMETER = array();
$MESSAGES = array();
