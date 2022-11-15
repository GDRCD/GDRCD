<?php

Router::loadRequired();

$cls = Oggetti::getInstance();

switch ( $_POST['action'] ) {
    case 'op_assign':
        echo json_encode($cls->assignObject($_POST));
        break;

}