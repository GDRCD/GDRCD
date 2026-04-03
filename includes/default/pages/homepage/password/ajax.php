<?php

Router::loadRequired();

switch ( $_POST['action'] ) {
    case 'password_recovery':
        echo json_encode(RecuperoPassword::getInstance()->recoverPassword($_POST));
        break;
}