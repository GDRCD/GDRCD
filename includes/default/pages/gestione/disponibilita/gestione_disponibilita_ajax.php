<?php

Router::loadRequired();

$cls = Disponibilita::getInstance();

switch ($_POST['action']) {
    case 'get_availability_data':
        echo json_encode($cls->ajaxAvailabilityData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newAvailability($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->modAvailability($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->delAvailability($_POST));
        break;
}

