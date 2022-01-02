<?php

require(__DIR__ . '/../../../includes/required.php');

$stat = Statistiche::getInstance();

switch ($_POST['action']) {
    case 'get_stat_data':
        echo json_encode($stat->ajaxStatData($_POST));
        break;
}

