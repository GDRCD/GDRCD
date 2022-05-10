<?php
require_once(__DIR__ . '/../../../includes/required.php');
$contatti = Contacts::getInstance();


switch ($_POST['action']) {
    case 'new_contatto':
        //Aggiunge un nuovo contatto al PG
        echo json_encode($contatti->newContatto($_POST));
        break;
}
