<?php

Router::loadRequired();

$cls = Oggetti::getInstance();

switch ( $_POST['action'] ) {
    case 'get_object_data':
        echo json_encode($cls->ajaxObjectData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->insertObject($_POST));
        break;
    case 'op_edit':
        echo json_encode($cls->editObject($_POST));
        break;
    case 'op_delete':
        echo json_encode($cls->deleteObject($_POST));
        break;

}