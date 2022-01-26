<?php

require(__DIR__ . '/../../../includes/required.php');

$cls = Statistiche::getInstance();

switch ($_POST['action']) {
    case 'get_stat_data':
        echo json_encode($cls->ajaxStatData($_POST));
        break;

    case 'op_insert_stat':
        echo json_encode($cls->insertStat($_POST));
        break;
    case 'op_edit_stat':
        echo json_encode($cls->editStat($_POST));
        break;
    case 'op_delete_stat':
        echo json_encode($cls->deleteStat($_POST));
        break;
}

