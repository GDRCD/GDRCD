<?php

require(__DIR__ . '/../../../../includes/required.php');

$abiReq = Abilita::getInstance();

switch ($_POST['action']) {
    case 'DatiAbiRequisito':
        echo json_encode($abiReq->DatiAbiRequisito($_POST));
        break;
}

