<?php
session_start();

/**
 * Definizioni globali
 */

/** @var string Percorso della cartella principale */
const GDRCD_PATH = __DIR__ . '/../';

/**
 * Caricamento dei file necessari al funzionamento del sito
 */
// carica le costanti globali
require_once(GDRCD_PATH . '/includes/constant_values.inc.php');

// carica le configurazioni di default e quelle personali se esistono
require_once(GDRCD_PATH . '/configs/config.core.inc.php');
if(file_exists(GDRCD_PATH . '/config.inc.php')){
    include_once GDRCD_PATH . '/config.inc.php';
}

// carica le funzioni di gestione delle migrazioni
require_once(GDRCD_PATH . '/includes/DbMigration/DbMigrationEngine.class.php');
require_once(GDRCD_PATH . '/includes/DbMigration/DbMigration.class.php');
require_once(GDRCD_PATH . '/includes/StmtResultData.class.php');

// carica il vocabolario nella lingua selezionata
require_once(GDRCD_PATH . '/vocabulary/' . $PARAMETERS['languages']['set'] . '.vocabulary.php');

// include le funzioni generiche
require_once(GDRCD_PATH . '/includes/functions.inc.php');

// include le funzioni del database
require_once(GDRCD_PATH . '/includes/functions.database.inc.php');

// include le funzioni per le api ajax/xhr
require_once(GDRCD_PATH . '/includes/functions.api.inc.php');

// include le funzioni per le chat
require_once(GDRCD_PATH . '/includes/functions.chat_core.inc.php');
require_once(GDRCD_PATH . '/includes/functions.chat_read.inc.php');
require_once(GDRCD_PATH . '/includes/functions.chat_write.inc.php');

// include le funzioni per il logging
require_once(GDRCD_PATH . '/includes/functions.log_core.inc.php');
require_once(GDRCD_PATH . '/includes/functions.log_read.inc.php');

// carica la gestione dei suoni
require_once(GDRCD_PATH . '/includes/AudioController.class.php');

// carica il tema definito
if(!empty($_SESSION['theme']) && array_key_exists($_SESSION['theme'], $PARAMETERS['themes']['available'])){
    $PARAMETERS['themes']['current_theme'] = $_SESSION['theme'];
}
gdrcd_logs_init();