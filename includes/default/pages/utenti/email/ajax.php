<?php

Router::loadRequired();

switch ( $_POST['action'] ) {
    case 'email_update':
        echo json_encode(ModificaEmail::getInstance()->sendVerificationEmail($_POST));
        break;
}