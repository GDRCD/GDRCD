<?php


require_once(__DIR__.'/../../../includes/required.php');

$esiti = Esiti::getInstance();

switch ($_POST['op']){
    case 'cd_add':
        echo json_encode($esiti->htmlCDAdd());
        break;


}