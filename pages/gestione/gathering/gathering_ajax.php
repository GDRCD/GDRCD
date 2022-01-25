<?php


require_once(__DIR__ . '/../../../includes/required.php');

$gathering_cat = GatheringCategory::getInstance();


switch ($_POST['action']) {
    case 'delete':
        echo json_encode($gathering_cat->deleteGatheringCat($_POST['id']));
        break;


}