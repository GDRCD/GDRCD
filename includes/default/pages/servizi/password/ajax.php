<?php

Router::loadRequired();

switch ( $_POST['action'] ) {
    case 'password_update':
        echo json_encode(ModificaPassword::getInstance()->updatePassword($_POST));
        break;
    case 'password_recovery':
        echo json_encode(RecuperoPassword::getInstance()->recoverPassword($_POST));
        break;
}