<?php

Router::loadRequired();

$meteoCond = MeteoStagioni::getInstance();

switch ( $_POST['action'] ) {
    case 'op_insert':
        echo json_encode($meteoCond->NewSeason($_POST));
        break;

    case 'op_edit':
        echo json_encode($meteoCond->ModSeason($_POST));
        break;

    case 'op_delete':
        echo json_encode($meteoCond->DelSeason($_POST));
        break;

    case 'op_assign_condition':
        echo json_encode($meteoCond->AssignCondition($_POST));
        break;

    case 'op_remove_condition':
        echo json_encode($meteoCond->RemoveCondition($_POST));
        break;
}

