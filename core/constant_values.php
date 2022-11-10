<?php

const ROOT = __DIR__ . '/../';

/*Livelli di accesso utente*/
define('SUPERUSER', 5);
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

/*Stati della mappa*/
define('INVIAGGIO', -1);

/**
 * Livelli di filtro html
 */
define('HTML_FILTER_BASE', 0);
define('HTML_FILTER_HIGH', 1);

/*Vettori globali dei parametri*/
$PARAMETER = [];
$MESSAGES = [];
