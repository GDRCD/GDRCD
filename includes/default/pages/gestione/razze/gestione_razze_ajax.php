<?php

Router::loadRequired();

$cls = Razze::getInstance();

switch ( $_POST['action'] ) {
    case 'get_race_data':
        echo json_encode($cls->ajaxRaceData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newRace($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->modRace($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->delRace($_POST));
        break;
}

