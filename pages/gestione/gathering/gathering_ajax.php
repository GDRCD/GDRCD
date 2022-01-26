<?php


require_once(__DIR__ . '/../../../includes/required.php');

$gathering_cat = GatheringCategory::getInstance();
$gathering_item = GatheringItem::getInstance();


switch ($_POST['action']) {
    case 'delete_cat':
        echo json_encode($gathering_cat->deleteGatheringCat($_POST['id']));
        break;
    case 'new_cat':
        echo json_encode($gathering_cat->newGatheringCat($_POST));
        break;
    case 'edit_cat':
        echo json_encode($gathering_cat->editGatheringCat($_POST));
    case 'new_item':
        echo json_encode($gathering_item->newGatheringItem($_POST));
        break;



}