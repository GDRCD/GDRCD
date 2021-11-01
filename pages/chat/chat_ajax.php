<?php

require_once(__DIR__ . '/../../includes/required.php');

$action = Filters::in($_POST['action']);
$chat = Chat::getInstance();

switch ($action) {
    # Invio azione
    case 'send_action':
        echo json_encode($chat->send($_POST));
        break;

    # Lancio Dado
    case 'roll_dice':
        echo json_encode($chat->roll($_POST));
        break;

    # Controlla che la chat sia quella dichiarata
    case 'send_esito':
        echo json_encode($chat->esito($_POST));
        break;

    # Aggiornamento chat
    case 'aggiorna_chat':
        echo json_encode(['list' => $chat->printChat()]);
        break;

    # Controlla che la chat sia quella dichiarata
    case 'controllaChat':
        echo json_encode($chat->controllaChat($_POST));
        break;
}