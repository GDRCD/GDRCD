<?php

Router::loadRequired();

$cls = NewsTipo::getInstance();

switch ( $_POST['action'] ) {
    case 'get_news_type_data':
        echo json_encode($cls->ajaxNewsTypeData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newNewsType($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->modNewsType($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->delNewsType($_POST));
        break;
}

