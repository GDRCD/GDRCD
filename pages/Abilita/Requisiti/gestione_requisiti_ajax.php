<?php

require(__DIR__.'/../../../includes/required.php');
require(__DIR__.'/../abilita_class.php');

$abiReq = new Abilita();

switch ($_POST['action']) {
    case 'DatiAbiRequisito':
        echo json_encode($abiReq->DatiAbiRequisito($_POST));
        break;
}

