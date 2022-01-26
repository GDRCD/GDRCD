<?php

require_once(__DIR__.'/../../../includes/required.php');

$cls = OnlineStatus::getInstance();

switch ($_POST['action']){
    case 'get_status_data':
        echo json_encode($cls->getAjaxStatusData($_POST));
        break;
    case 'get_status_type_data':
        echo json_encode($cls->getAjaxStatusTypeData($_POST));
        break;

    case 'op_insert_status':
        echo json_encode($cls->insertStatus($_POST));
        break;
    case 'op_edit_status':
        echo json_encode($cls->editStatus($_POST));
        break;
    case 'op_delete_status':
        echo json_encode($cls->deleteStatus($_POST));
        break;

    case 'op_insert_status_type':
        echo json_encode($cls->insertStatusType($_POST));
        break;
    case 'op_edit_status_type':
        echo json_encode($cls->editStatusType($_POST));
        break;
    case 'op_delete_status_type':
        echo json_encode($cls->deleteStatusType($_POST));
        break;
}