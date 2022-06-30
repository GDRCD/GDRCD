<?php
session_start();

require_once(dirname(__FILE__) . '/constant_values.inc.php');
require_once(dirname(__FILE__) . '/../config.inc.php');
if(file_exists(dirname(__FILE__).'/config-overrides.php')){
    include_once dirname(__FILE__).'/config-overrides.php';
}

require_once dirname(__FILE__) . '/DbMigration/DbMigrationEngine.class.php';
require_once dirname(__FILE__) . '/DbMigration/DbMigration.class.php';

require_once(dirname(__FILE__) . '/../vocabulary/' . $PARAMETERS['languages']['set'] . '.vocabulary.php');
require_once(dirname(__FILE__) . '/functions.inc.php');

/** Gestione dei Suoni */
require_once(dirname(__FILE__) . '/AudioController.class.php');

if(!empty($_SESSION['theme']) and array_key_exists($_SESSION['theme'], $PARAMETERS['themes']['available'])){
    $PARAMETERS['themes']['current_theme'] = $_SESSION['theme'];
}
