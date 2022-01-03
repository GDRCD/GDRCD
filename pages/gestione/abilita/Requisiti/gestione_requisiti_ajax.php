<?php

require(__DIR__ . '/../../../../includes/required.php');

$abiReq = AbilitaRequisiti::getInstance();

switch ($_POST['action']) {
    case 'get_requirement_data':
        echo json_encode($abiReq->ajaxReqData($_POST));
        break;
}

