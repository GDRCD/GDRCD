<?php

Router::loadRequired();

$action = Filters::in($_POST['action']);
$chat = Chat::getInstance();

switch ( $action ) {
    # Invio azione
    case 'send_action':
        echo json_encode($chat->send($_POST));
        break;

    case 'roll_ability':
        echo json_encode($chat->rollAbility($_POST));
        break;

    case 'roll_stat':
        echo json_encode($chat->rollStat($_POST));
        break;

    case 'roll_obj':
        echo json_encode($chat->rollObject($_POST));
        break;

    # Controlla che la chat sia quella dichiarata
    case 'send_esito':
        echo json_encode($chat->rollEsito($_POST));
        break;

    # Aggiornamento chat
    case 'aggiorna_chat':
        echo json_encode(['list' => $chat->printChat()]);
        break;

    # Controlla che la chat sia quella dichiarata
    case 'controllaChat':
        echo json_encode($chat->chatPositionTrue($_POST));
        break;
}