<?php

Router::loadRequired();

$cls = GruppiStipendiExtra::getInstance();

switch ($_POST['action']) {
    case 'get_extra_earn_data':
        echo json_encode($cls->ajaxExtraEarnData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->NewExtraEarn($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->ModExtraEarn($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->DelExtraEarn($_POST));
        break;
}

