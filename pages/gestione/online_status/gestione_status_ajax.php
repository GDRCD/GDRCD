<?php

require_once(__DIR__.'/../../../includes/required.php');

$cls = OnlineStatus::getInstance();

switch ($_POST['action']){
    case 'get_status_data':
        echo json_encode($cls->getAjaxStatusData($_POST));
        break;
    case 'get_status_type_data':
        echo json_encode($cls->getAjaxStatusTypeData($_POST));
        break;
}