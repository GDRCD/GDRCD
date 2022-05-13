<?php

require(__DIR__ . '/../../../../core/required.php');

$metoCond = MeteoCondizioni::getInstance();

switch ($_POST['action']) {
    case 'get_condition_data':
        echo json_encode($metoCond->ajaxCondData($_POST));
        break;

    case 'get_conditions_list':
        echo json_encode($metoCond->ajaxCondList());
        break;

    case 'op_insert':
        echo json_encode($metoCond->NewCondition($_POST));
        break;

    case 'op_edit':
        echo json_encode($metoCond->ModAbiRequisito($_POST));
        break;

    case 'op_delete':
        echo json_encode($metoCond->DelCondition($_POST));
        break;
}

