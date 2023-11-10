<?php

Router::loadRequired();

$cls = TicketStatus::getInstance();

switch ( $_POST['action'] ) {
    case 'get_status_data':
        echo json_encode($cls->ajaxStatusData($_POST));
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
}