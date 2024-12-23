<?php

Router::loadRequired();

$cls = News::getInstance();

switch ( $_POST['action'] ) {
    case 'have_new_news':
        echo json_encode($cls->ajaxNewNews());
        break;
}

