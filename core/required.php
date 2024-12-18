<?php

# Setto la timezone corretta
global $PARAMETERS;
global $MESSAGE;

date_default_timezone_set('Europe/Rome');

# Inizializzo funzioni necessarie
require_once(__DIR__ . '/../core/functions.php');

# Carico le constanti principali
require_once(__DIR__ . '/../core/constant_values.php');

# Se esiste un file di overrides di connessione del db, caricalo, altrimenti carico quello di default
if ( file_exists(__DIR__ . '/../core/db_overrides.php') ) {
    require_once __DIR__ . '/../core/db_overrides.php';
} else {
    require_once (__DIR__) . '/../core/db_config.php';
}

# Include del template engine
require_once(__DIR__ . '/../plugins/smarty/libs/Smarty.class.php');

# Inizializzo le classi fondamentali
require_once(__DIR__ . '/classes/Libraries/Base.class.php');
require_once(__DIR__ . '/classes/Libraries/Session.class.php');
require_once(__DIR__ . '/classes/Libraries/Router.class.php');
require_once(__DIR__ . '/classes/Libraries/DB.class.php');

# Avvio la sessione
Session::start(!defined('SESSION_LOCK') || !SESSION_LOCK);

# Inclusione file classe tramite routing
Router::startClasses();

try {
    if ( !DbMigrationEngine::dbNeedsInstallation() ) {
        # Inserisco il resto delle configurazioni
        require_once(__DIR__ . '/../config.inc.php');

        # Se ho selezionato un tema, sovrascrivo quello di default
        if ( !empty($_SESSION['theme']) && array_key_exists($_SESSION['theme'], $PARAMETERS['themes']['available']) ) {
            $PARAMETERS['themes']['current_theme'] = $_SESSION['theme'];
        }

        require_once(__DIR__ . '/../vocabulary/' . $PARAMETERS['languages']['set'] . '.vocabulary.php');
    }
} catch ( Throwable $e ) {
    die($e->getMessage());
}