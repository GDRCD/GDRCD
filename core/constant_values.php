<?php

CONST ROOT = __DIR__.'/../';

/*Livelli di accesso utente*/
define('SUPERUSER',5) ;
define('MODERATOR', 4);
define('GAMEMASTER', 3);
define('GUILDMODERATOR', 2);
define('USER', 1);
define('DELETED', 0);

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
 * Impostazioni registrazioni giocate
 */
//Azioni minime per poter registrare una giocata
CONST REG_MIN_AZIONI = 4;

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
 * Impostazioni QUEST
 */

//Attiva o disattiva il pacchetto intero (default: true)
const QUEST_ENABLED = true;

//definisce i permessi minimi necessari alla registrazione/modifica delle quest (default: gamemaster)
const QUEST_PERM = GAMEMASTER;

//Attiva o disattiva il sistema trame (default: true)
const TRAME_ENABLED = true;

//definisce i permessi minimi necessari alla registrazione/modifica delle trame (default: gamemaster)
const TRAME_PERM = GAMEMASTER;

//definisce quali permessi hanno accesso alla visione della sezione "resoconti quest" dei pg (default: gamemaster)
const VIEW_QUEST = GAMEMASTER;

//definisce quali permessi hanno accesso alla visione della sezione "trame" (default: gamemaster)
const VIEW_TRAME = GAMEMASTER;

//Definisce la possibilità per un pg abilitato a registrare quest di poter visualizzare (non modificare) le quest altrui (default: true)
const VIEW_OTHER = true;

//Definisce la possibilità per un pg abilitato a registrare trame di poter visualizzare (non modificare) le trame altrui (default: true)
const VIEW_TRAME_OTHER = true;

//Permessi minimi per modificare tutte le parti del pacchetto (default: moderator)
const EDIT_ALL_QUEST = MODERATOR;

//Definisce la possibilità di inviare messaggi automatici di avviso agli utenti che partecipano ad una quest (default: false)
const QUEST_ALERT = false;

/*Vettori globali dei parametri*/
$PARAMETER = array();
$MESSAGES = array();
