<?php

Router::loadRequired();

$cls = Anagrafe::getInstance();

switch ( $_POST['action'] ) {
    case 'search':
        echo json_encode($cls->ajaxSearch($_POST));
        break;
}

