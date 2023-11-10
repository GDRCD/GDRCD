<?php

Router::loadRequired();

$action = Filters::in($_POST['action']);
$bank = Banca::getInstance();

switch ( $action ) {
    # Invio azione
    case 'deposit':
        echo json_encode($bank->deposit($_POST));
        break;

    case 'withdraw':
        echo json_encode($bank->withdraw($_POST));
        break;

    case 'transfer':
        echo json_encode($bank->transfer($_POST));
        break;

}