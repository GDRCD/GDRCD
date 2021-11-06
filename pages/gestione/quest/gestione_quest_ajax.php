<?php

require_once(__DIR__.'/../../../includes/required.php');

$quest = Quest::getInstance();

switch ($_POST['action']){
    case 'create_input':
        echo json_encode($quest->createMemberInput());
        break;
}