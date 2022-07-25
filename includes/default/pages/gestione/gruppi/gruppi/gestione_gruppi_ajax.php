<?php

Router::loadRequired();

$cls = Gruppi::getInstance();

switch ( $_POST['action'] ) {
    case 'get_group_data':
        echo json_encode($cls->ajaxGroupData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->NewGroup($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->ModGroup($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->DelGroup($_POST));
        break;
}

