<?php

Router::loadRequired();

$pasword = ModificaPassword::getInstance();

switch ( $_POST['action'] ) {
    case 'update_password':
        echo json_encode($pasword->updateLoggedUserPassword($_POST));
        break;
}