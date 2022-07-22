<?php

Router::loadRequired();

$cls = GruppiFondi::getInstance();

switch ($_POST['action']) {
    case 'get_found_data':
        echo json_encode($cls->ajaxFoundData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->NewFound($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->ModFound($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->DelFound($_POST));
        break;
}

