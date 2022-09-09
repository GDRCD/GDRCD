<?php

Router::loadRequired();

$pasword = ModificaPassword::getInstance();

switch ( $_POST['action'] ) {
    case 'update_password':
        echo json_encode($pasword->updateLoggedUserPassword($_POST));
        break;
    case 'update_password_external':
        echo json_encode($pasword->updateExternalUserPassword($_POST));
        break;
}