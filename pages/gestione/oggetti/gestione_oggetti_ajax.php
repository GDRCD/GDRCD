<?php

require_once(__DIR__.'/../../../includes/required.php');

$cls = Oggetti::getInstance();

switch ($_POST['action']){
    case 'get_object_data':
        echo json_encode($cls->ajaxObjectData($_POST));
        break;
    case 'get_object_type_data':
        echo json_encode($cls->ajaxObjectTypeData($_POST));
        break;
    case 'get_object_position_data':
        echo json_encode($cls->ajaxObjectPositionData($_POST));
        break;

    case 'op_insert_object':
        echo json_encode($cls->insertObject($_POST));
        break;
    case 'op_edit_object':
        echo json_encode($cls->editObject($_POST));
        break;
    case 'op_delete_object':
        echo json_encode($cls->deleteObject($_POST));
        break;

    case 'op_insert_object_type':
        echo json_encode($cls->insertObjectType($_POST));
        break;
    case 'op_edit_object_type':
        echo json_encode($cls->editObjectType($_POST));
        break;
    case 'op_delete_object_type':
        echo json_encode($cls->deleteObjectType($_POST));
        break;

    case 'op_insert_object_position':
        echo json_encode($cls->insertObjectPosition($_POST));
        break;
    case 'op_edit_object_position':
        echo json_encode($cls->editObjectPosition($_POST));
        break;
    case 'op_delete_object_position':
        echo json_encode($cls->deleteObjectPosition($_POST));
        break;
}