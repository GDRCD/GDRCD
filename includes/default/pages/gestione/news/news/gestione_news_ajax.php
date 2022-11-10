<?php

Router::loadRequired();

$cls = News::getInstance();

switch ( $_POST['action'] ) {
    case 'get_news_data':
        echo json_encode($cls->ajaxNewsData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newNews($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->modNews($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->delNews($_POST));
        break;
}

