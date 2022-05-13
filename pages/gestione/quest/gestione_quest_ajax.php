<?php

require_once(__DIR__.'/../../../core/required.php');

$quest = Quest::getInstance();

switch ($_POST['action']){
    case 'create_input':
        echo json_encode($quest->createMemberInput());
        break;

    case 'insert_quest':
        echo json_encode($quest->insertQuest($_POST));
        break;

    case 'edit_quest':
        echo json_encode($quest->editQuest($_POST));
        break;

    case 'delete_quest':
        echo json_encode($quest->deleteQuest($_POST));
        break;
}