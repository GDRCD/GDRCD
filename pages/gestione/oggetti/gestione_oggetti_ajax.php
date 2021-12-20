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
}