<?php# Importazione pagine necessarierequire(__DIR__ . '/../../../core/required.php');# Inizializzazione classe necessaria$gestione = Gestione::getInstance();# Switch operazioneswitch ($_POST['action']) {    # Estrazione dinamica dati via Ajax    case 'save_constants':        echo json_encode($gestione->updateConstants($_POST));        break;}