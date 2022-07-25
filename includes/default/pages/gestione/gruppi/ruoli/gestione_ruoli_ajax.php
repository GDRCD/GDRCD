<?php

Router::loadRequired();

$cls = GruppiRuoli::getInstance();

switch ( $_POST['action'] ) {
    case 'get_role_data':
        echo json_encode($cls->ajaxRoleData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->NewRole($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->ModRole($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->DelRole($_POST));
        break;
}

