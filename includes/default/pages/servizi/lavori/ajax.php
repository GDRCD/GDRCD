<?php

Router::loadRequired();

$works = GruppiLavori::getInstance();

switch ( $_POST['action'] ) {
    case 'get_work':
        echo json_encode($works->autoAssignWork($_POST));
        break;
    case 'remove_work':
        echo json_encode($works->autoRemoveWork($_POST));
        break;
}

