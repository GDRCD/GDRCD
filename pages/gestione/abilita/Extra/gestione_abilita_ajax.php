<?php# Importazione pagine necessarierequire(__DIR__ . '/../../../../includes/required.php');# Inizializzazione classe necessaria$abi_class = AbilitaExtra::getInstance();# Switch operazioneswitch ($_POST['action']) {    # Estrazione dinamica dati via Ajax    case 'get_extra_data':        echo json_encode($abi_class->ajaxExtraData($_POST));        break;}