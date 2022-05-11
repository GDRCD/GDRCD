<?php

require(__DIR__ . '/../../../../includes/required.php');

$venti = MeteoVenti::getInstance();

switch ($_POST['action']) {
    case 'get_vento_data':
        echo json_encode($venti->ajaxWindData($_POST));
        break;

    case 'get_conditions_list':
        echo json_encode($venti->ajaxCondList());
        break;

    case 'op_insert':
        echo json_encode($venti->NewWind($_POST));
        break;

    case 'op_edit':
        echo json_encode($venti->ModWind($_POST));
        break;

    case 'op_delete':
        echo json_encode($venti->DelWind($_POST));
        break;
}

