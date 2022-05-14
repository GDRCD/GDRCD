<?php

Router::loadRequired();

$meteo = Meteo::getInstance();

switch ($_POST['action']) {
    case 'op_edit_chat':
        echo json_encode($meteo->saveChat($_POST));
        break;
}