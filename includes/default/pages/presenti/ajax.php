<?php

Router::loadRequired();

$cls = Presenti::getInstance();

switch ( $_POST['action'] ) {
    case 'get_presences':
        echo json_encode($cls->ajaxPresences());
        break;
}

