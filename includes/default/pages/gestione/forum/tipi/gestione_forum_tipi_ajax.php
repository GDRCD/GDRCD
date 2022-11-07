<?php

Router::loadRequired();

$cls = ForumTipo::getInstance();

switch ( $_POST['action'] ) {
    case 'get_forum_type_data':
        echo json_encode($cls->ajaxForumTypeData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newType($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->modType($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->delType($_POST));
        break;
}

