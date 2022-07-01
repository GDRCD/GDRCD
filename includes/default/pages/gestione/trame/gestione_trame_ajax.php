<?php

Router::loadRequired();

$quest = Quest::getInstance();

switch ($_POST['action']){
    case 'insert_trama':
        echo json_encode($quest->insertTrama($_POST));
        break;

    case 'edit_trama':
        echo json_encode($quest->editTrama($_POST));
        break;

    case 'delete_trama':
        echo json_encode($quest->deleteTrama($_POST));
        break;
}