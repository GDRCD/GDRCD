<?php

Router::loadRequired();

$cls = OnlineStatus::getInstance();
$cls_type = OnlineStatusType::getInstance();

switch ( $_POST['action'] ) {
    case 'get_status_data':
        echo json_encode($cls->ajaxStatusData($_POST));
        break;
    case 'get_status_type_data':
        echo json_encode($cls_type->ajaxStatusTypeData($_POST));
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
        echo json_encode($cls_type->insertStatusType($_POST));
        break;
    case 'op_edit_status_type':
        echo json_encode($cls_type->editStatusType($_POST));
        break;
    case 'op_delete_status_type':
        echo json_encode($cls_type->deleteStatusType($_POST));
        break;
}