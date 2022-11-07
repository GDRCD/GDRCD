<?php

Router::loadRequired();

$cls = Forum::getInstance();

switch ( $_POST['action'] ) {
    case 'get_forum_data':
        echo json_encode($cls->ajaxForumData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newForum($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->modForum($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->delForum($_POST));
        break;
}

