<?php
session_start();

require_once(dirname(__FILE__) . '/constant_values.inc.php');

// carica le configurazioni di default e quelle personali se esistono
require_once(dirname(__FILE__) . '/../configs/config.core.inc.php');
if(file_exists(dirname(__FILE__).'/../config.inc.php')){
    include_once dirname(__FILE__).'/../config.inc.php';
}

require_once dirname(__FILE__) . '/DbMigration/DbMigrationEngine.class.php';
require_once dirname(__FILE__) . '/DbMigration/DbMigration.class.php';
require_once dirname(__FILE__) . '/StmtResultData.class.php';

require_once(dirname(__FILE__) . '/../vocabulary/' . $PARAMETERS['languages']['set'] . '.vocabulary.php');
require_once(dirname(__FILE__) . '/functions.inc.php');

// include le funzioni per le api ajax/xhr
require_once(dirname(__FILE__) . '/functions.api.inc.php');

// include le funzioni per le chat
require_once(dirname(__FILE__) . '/functions.chat_core.inc.php');
require_once(dirname(__FILE__) . '/functions.chat_read.inc.php');
require_once(dirname(__FILE__) . '/functions.chat_write.inc.php');

/** Gestione dei Suoni */
require_once(dirname(__FILE__) . '/AudioController.class.php');

if(!empty($_SESSION['theme']) and array_key_exists($_SESSION['theme'], $PARAMETERS['themes']['available'])){
    $PARAMETERS['themes']['current_theme'] = $_SESSION['theme'];
}
