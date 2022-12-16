<?php

Router::loadRequired();

$cls = OggettiStatistiche::getInstance();

switch ( $_POST['action'] ) {
    case 'get_object_stat_data':
        echo json_encode($cls->ajaxObjectStatData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newObjectStat($_POST));
        break;
    case 'op_edit':
        echo json_encode($cls->editObjectStat($_POST));
        break;
    case 'op_delete':
        echo json_encode($cls->deleteObjectStat($_POST));
        break;

}