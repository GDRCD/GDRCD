<?php


require_once(__DIR__.'/../../../includes/required.php');

$esiti = Esiti::getInstance();

switch ($_POST['action']){
    case 'cd_add':
        echo json_encode($esiti->htmlCDAdd());
        break;

    case 'new':
        echo json_encode($esiti->newEsitoManagement($_POST));
        break;

}