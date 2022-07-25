<?php

Router::loadRequired();

$cls = Sessi::getInstance();

switch ( $_POST['action'] ) {
    case 'get_gender_data':
        echo json_encode($cls->ajaxGenderData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newGender($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->modGender($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->delGender($_POST));
        break;
}

