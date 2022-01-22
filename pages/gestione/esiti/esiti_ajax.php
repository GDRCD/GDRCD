<?php


require_once(__DIR__ . '/../../../includes/required.php');

$esiti = Esiti::getInstance();

switch ($_POST['action']) {
    case 'cd_add':
        echo json_encode($esiti->htmlCDAdd());
        break;

    case 'new':
        echo json_encode($esiti->newEsitoManagement($_POST));
        break;


    case 'answer':
        echo json_encode($esiti->newAnswer($_POST));
        break;

    case 'add_member':
        echo json_encode($esiti->addMember($_POST));
        break;

    case 'delete_member':
        echo json_encode($esiti->deleteMember($_POST));
        break;

    case 'close':
        echo json_encode($esiti->esitoClose($_POST['id']));
        break;

    case 'open':
        echo json_encode($esiti->esitoOpen($_POST['id']));
        break;

    case 'change_master':
        echo json_encode($esiti->setMaster($_POST));
        break;

}