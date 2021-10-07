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
 * Impostazioni registrazioni giocate
 */

const REG_ROLE = true;
//Attiva o disattiva il pacchetto intero (default: true)
const SAVE_ROLE = true;
//Attiva o disattiva il salvataggio delle role su pc (default: true)
const SEND_GM = true;
//Attiva o disattiva la segnalazione ai GM e la relativa pagina gestionale (default: true)
const ROLE_PERM = GAMEMASTER;
//definisce quali permessi hanno accesso alla lista delle giocate dei pg e alle segnalazioni GM (default: gamemaster)
const LOG_PERM = GAMEMASTER;
//definisce quali permessi hanno accesso ai log chat (default: gamemaster)
const EDIT_PERM = GAMEMASTER;
//definisce quali permessi possono modificare le registrazioni (default: gamemaster)
CONST PG_DIARY_ENABLED = true;
//Attiva o disattiva il diario del PG
/*Vettori globali dei parametri*/
$PARAMETER = array();
$MESSAGES = array();
