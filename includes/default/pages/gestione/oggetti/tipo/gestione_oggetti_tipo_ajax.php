<?php

Router::loadRequired();

$cls = OggettiTipo::getInstance();

switch ( $_POST['action'] ) {
    case 'get_object_type_data':
        echo json_encode($cls->ajaxObjectTypeData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newObjectType($_POST));
        break;
    case 'op_edit':
        echo json_encode($cls->editObjectType($_POST));
        break;
    case 'op_delete':
        echo json_encode($cls->deleteObjectType($_POST));
        break;

}