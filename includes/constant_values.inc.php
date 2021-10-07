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
 *  Abilita'
 */
# Gestisce l'inclusione della tabella `abilita_extra` e dei costi e descrizioni per livello
CONST ABI_EXTRA = true;

# Moltiplicatore default di costo abilita'
CONST DEFAULT_PX_PER_LVL = 10;

# Livello massimo abilita'
CONST ABI_LEVEL_CAP = 5;

# Pagina abilita pubblica = true
# Pagina abilita solo al proprietario o ai moderatori = false
CONST ABI_PUBLIC = true;

/**
 *  Requisito Abilita'
 */
# Gestisce l'inclusione dei requisiti abilita'
CONST ABI_REQUIREMENT = true;

# Definisce i tipi di requisiti abilita'
CONST REQUISITO_ABI = 1;
CONST REQUISITO_STAT = 2;

/*Vettori globali dei parametri*/
$PARAMETER = array();
$MESSAGES = array();
