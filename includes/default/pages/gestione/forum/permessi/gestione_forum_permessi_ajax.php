<?php

Router::loadRequired();

$cls = ForumPermessi::getInstance();

switch ( $_POST['action'] ) {
    case 'get_permissions':
        echo json_encode($cls->ajaxForumPermissions($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newPermission($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->delPermission($_POST));
        break;

}

