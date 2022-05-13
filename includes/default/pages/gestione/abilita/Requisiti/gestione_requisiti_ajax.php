<?php

require(__DIR__ . '/../../../../core/required.php');

$abiReq = AbilitaRequisiti::getInstance();

switch ($_POST['action']) {
    case 'get_requirement_data':
        echo json_encode($abiReq->ajaxReqData($_POST));
        break;

    case 'get_requirement_list':
        echo json_encode($abiReq->ajaxReqList());
        break;

    case 'op_insert':
        echo json_encode($abiReq->NewAbiRequisito($_POST));
        break;

    case 'op_edit':
        echo json_encode($abiReq->ModAbiRequisito($_POST));
        break;

    case 'op_delete':
        echo json_encode($abiReq->DelAbiRequisito($_POST));
        break;
}

