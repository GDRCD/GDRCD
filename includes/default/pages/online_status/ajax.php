<?php

Router::loadRequired();

$online = OnlineStatus::getInstance();


switch ( $_POST['action'] ) {

    case 'render_set_content':
        echo json_encode($online->ajaxRenderSetPage($_POST));
        break;

    case 'choose_status':
        echo json_encode($online->setOnlineStatus($_POST));
        break;
}