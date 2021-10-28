<?php

require(__DIR__.'/../../../includes/required.php');

$gestione = Gestione::getInstance();

var_dump($_POST);

switch ($_POST['action']){
    case 'orderPermission':
        echo json_encode($gestione->orderPermission($_POST));
        break;

}