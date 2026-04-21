<?php

/**
 * Definizioni generiche
 */

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
const GDRCD_CHAT_DEFAULT_TYPE = GDRCD_CHAT_ACTION_TYPE;

/**
 * Codice utente dei messaggi in chat.
 * Rappresentano il primo carattere che gli utenti
 * possono anteporre alla propria azione in chat
 * per usare una specifica formattazione
 */

/** @var string Simbolo per indicare i messaggi di tipo Azione */
const GDRCD_CHAT_ACTION_SYMBOL = '+';

/** @var string Simbolo per indicare i messaggi di tipo Sussurro. Subito dopo va inserito il nome del destinatario e poi nuovamente lo stesso simbolo affinché abbia effetto */
const GDRCD_CHAT_WHISPER_SYMBOL = '@';

/** @var string Simbolo per indicare i messaggi di tipo Tiro su Caratteristica */
const GDRCD_CHAT_STATS_SYMBOL = '%';

/** @var string Simbolo per indicare i messaggi di tipo Tiro su Abilità */
const GDRCD_CHAT_SKILL_SYMBOL = '^';

/** @var string Simbolo per indicare i messaggi di tipo Tiro Dado. Permette di definire il lancio di un dado con le seguenti espressioni: d6, 3d6, 2d6+3, 8d10,7. La sintassi è: [numero]d[facce][modificatore],[soglia] */
const GDRCD_CHAT_DICE_SYMBOL = '#';

/** @var string Simbolo per indicare i messaggi di tipo Utilizzo Oggetto */
const GDRCD_CHAT_ITEM_SYMBOL = '=';

/** @var string Simbolo per indicare i messaggi di tipo Master */
const GDRCD_CHAT_MASTER_SYMBOL = '§';

/** @var string Simbolo per indicare i messaggi di tipo PNG. Funziona come per i sussurri */
const GDRCD_CHAT_PNG_SYMBOL = '$';

/** @var string Simbolo per indicare i messaggi di tipo Immagine. Permette di inserire la url di un immagine subito dopo il carattere */
const GDRCD_CHAT_IMAGE_SYMBOL = '*';

/** @var string Simbolo per indicare i messaggi di tipo invito in chat. Funziona come per i sussurri */
const GDRCD_CHAT_PRIVATE_INVITE_SYMBOL = '!';

/** @var string Simbolo per indicare i messaggi di tipo espelli da chat. Funziona come per i sussurri */
const GDRCD_CHAT_PRIVATE_KICK_SYMBOL = '_';

/** @var string Simbolo per indicare i messaggi di tipo elenco invitati in chat privata */
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

/**
 * Soglie di fingerprint per la validazione delle sessioni.
 * Il fingerprint è un indice probabilistico che indica quanto è probabile
 * che il client corrente sia lo stesso che ha creato la sessione.
 */

/** @var int Device identico */
const GDRCD_FINGERPRINT_VERYCONFIDENT = 3;

/** @var int Verosimilmente stesso device */
const GDRCD_FINGERPRINT_CONFIDENT = 2;

/** @var int Zona grigia, forse stesso device */
const GDRCD_FINGERPRINT_UNSURE = 1;

/** @var int Device differente */
const GDRCD_FINGERPRINT_WRONG = 0;

/**
 * Nomi dei segnali utilizzati per il fingerprint del client.
 */
const GDRCD_FINGERPRINT_SIGNAL_USERAGENT = 'user_agent';
const GDRCD_FINGERPRINT_SIGNAL_IP = 'ip';
const GDRCD_FINGERPRINT_SIGNAL_ACCEPT = 'accept';
const GDRCD_FINGERPRINT_SIGNAL_LANGUAGE = 'language';
const GDRCD_FINGERPRINT_SIGNAL_ENCODING = 'encoding';

/**
 * Pesi per il calcolo del fingerprint.
 * Ogni parametro contribuisce allo score totale in base al suo peso.
 */
const GDRCD_FINGERPRINT_WEIGHTS = [
    GDRCD_FINGERPRINT_SIGNAL_USERAGENT  => 4.0,
    GDRCD_FINGERPRINT_SIGNAL_IP         => 2.0,
    GDRCD_FINGERPRINT_SIGNAL_ACCEPT     => 1.5,
    GDRCD_FINGERPRINT_SIGNAL_LANGUAGE   => 1.5,
    GDRCD_FINGERPRINT_SIGNAL_ENCODING   => 1.0,
];

/**
 * Configurazione temporale delle sessioni
 */

/** @var int Durata massima assoluta di una sessione in secondi (24 ore) */
const GDRCD_SESSION_MAX_TTL = 86400;

/** @var int Intervallo di refresh della sessione in secondi (10 minuti) */
const GDRCD_SESSION_REFRESH_INTERVAL = 600;

/** @var int Durata della sessione dalla creazione alla scadenza in secondi (15 minuti) */
const GDRCD_SESSION_EXPIRY = 900;

/** @var int Periodo di grazia per sessioni refreshed in secondi */
const GDRCD_SESSION_GRACE_PERIOD = 20;

/** @var int Debounce per aggiornamento data_ultimavisita in secondi */
const GDRCD_SESSION_ACTIVITY_DEBOUNCE = 60;

/** @var int Durata del token di protezione takeover in secondi (5 minuti) */
const GDRCD_SESSION_TAKEOVER_TOKEN_TTL = 300;

/**
 * Stati delle sessioni
 */
const GDRCD_SESSION_STATUS_ACTIVE = 'active';
const GDRCD_SESSION_STATUS_REFRESHED = 'refreshed';
const GDRCD_SESSION_STATUS_REVOKED = 'revoked';

const GDRCD_LOGIN_SUCCESS = 'success';
const GDRCD_LOGIN_TAKEOVER = 'takeover';
const GDRCD_LOGIN_WRONG = 'wrong-credentials';
const GDRCD_LOGIN_DISABLED = 'user-deleted';

/*Vettori globali dei parametri*/
$PARAMETER = array();
$MESSAGES = array();

// Id del personaggio di "sistema" per la messaggistica interna
const WEBMASTER_ID = 0;
