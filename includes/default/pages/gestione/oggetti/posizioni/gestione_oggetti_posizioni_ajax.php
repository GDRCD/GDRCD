<?php

Router::loadRequired();

$cls = OggettiPosizioni::getInstance();

switch ( $_POST['action'] ) {
    case 'get_object_position_data':
        echo json_encode($cls->ajaxObjectPositionData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newObjectPosition($_POST));
        break;
    case 'op_edit':
        echo json_encode($cls->editObjectPosition($_POST));
        break;
    case 'op_delete':
        echo json_encode($cls->deleteObjectPosition($_POST));
        break;
}