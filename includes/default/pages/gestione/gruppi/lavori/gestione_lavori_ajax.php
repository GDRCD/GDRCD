<?php

Router::loadRequired();

$cls = GruppiLavori::getInstance();

switch ($_POST['action']) {
    case 'get_works_data':
        echo json_encode($cls->ajaxWorkData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->NewWork($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->ModWork($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->DelWork($_POST));
        break;

    case 'assign_work':
        echo json_encode($cls->assignWork($_POST));
        break;

    case 'remove_work':
        echo json_encode($cls->removeWork($_POST));
        break;
}

