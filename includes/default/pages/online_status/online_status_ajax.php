<?php

require_once(__DIR__ . '/../../core/required.php');

$online = OnlineStatus::getInstance();

switch ($_POST['op']){
    case 'choose_status':
        echo json_encode($online->setOnlineStatus($_POST));
        break;
}