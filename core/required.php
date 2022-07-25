<?php
session_start();

# Inizializzo funzioni necessarie
require_once(dirname(__FILE__) . '/../core/functions.php');

# Carico le constanti principali
require_once(dirname(__FILE__) . '/../core/constant_values.php');

# Se esiste un file di overrides di connessione del db, caricalo, altirmenti carico quello di default
if ( file_exists(dirname(__FILE__) . '/../core/db_overrides.php') ) {
    require_once dirname(__FILE__) . '/../core/db_overrides.php';
} else {
    require_once (dirname(__FILE__)) . '/../core/db_config.php';
}

# Include del template engine
require_once(dirname(__FILE__) . '/../plugins/smarty/libs/Smarty.class.php');

# Inizializzo le classi fondamentali
require_once(dirname(__FILE__) . '/classes/Libraries/Base.class.php');
require_once(dirname(__FILE__) . '/classes/Libraries/Routing.class.php');
require_once(dirname(__FILE__) . '/classes/Libraries/DB.class.php');

# Creo la connessione
$handleDBConnection = DB::connect();

# Inclusione file classe tramite routing
Router::startClasses();

if ( !DbMigrationEngine::dbNeedsInstallation() ) {
    # Inserisco il resto delle configurazioni
    require_once(dirname(__FILE__) . '/../config.inc.php');

    # Se ho selezionato un tema, sovrascrivo quello di default
    if ( !empty($_SESSION['theme']) and array_key_exists($_SESSION['theme'], $PARAMETERS['themes']['available']) ) {
        $PARAMETERS['themes']['current_theme'] = $_SESSION['theme'];
    }

    require_once(dirname(__FILE__) . '/../vocabulary/' . $PARAMETERS['languages']['set'] . '.vocabulary.php');
}

