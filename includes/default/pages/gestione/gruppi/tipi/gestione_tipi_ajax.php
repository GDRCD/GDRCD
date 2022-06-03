<?php

Router::loadRequired();

$cls = GruppiTipi::getInstance();

switch ($_POST['action']) {
    case 'get_type_data':
        echo json_encode($cls->ajaxTypeData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->NewType($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->ModType($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->DelType($_POST));
        break;
}

