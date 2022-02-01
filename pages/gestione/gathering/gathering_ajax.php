<?php


require_once(__DIR__ . '/../../../includes/required.php');

$gathering_cat = GatheringCategory::getInstance();
$gathering_item = GatheringItem::getInstance();
$gathering_chat =GatheringChat::getInstance();
$chat=Chat::getInstance();


switch ($_POST['action']) {
    case 'delete_cat':
        echo json_encode($gathering_cat->deleteGatheringCat($_POST['id']));
        break;
    case 'new_cat':
        echo json_encode($gathering_cat->newGatheringCat($_POST));
        break;
    case 'edit_cat':
        echo json_encode($gathering_cat->editGatheringCat($_POST));
        break;
    case 'new_item':
        echo json_encode($gathering_item->newGatheringItem($_POST));
        break;
    case 'delete_item':
        echo json_encode($gathering_item->deleteGatheringItem($_POST['id']));
        break;
    case 'edit_item':
        echo json_encode($gathering_item->editGatheringItem($_POST));
    break;
    case 'get_object_data':
        $chat_data= $chat->getChatData($_POST['id']);
         echo json_encode($gathering_chat->ajaxObjectData($chat_data));
        break;
    case 'get_object_item':

        echo json_encode($gathering_chat->ajaxObjectItem($_POST));
        break;
    case 'new_chat':
        echo json_encode($gathering_chat->newGatheringChat($_POST));
        break;
    case 'delete_all_chat':
        echo json_encode($gathering_chat->deleteAllGatheringChat($_POST['id']));
        break;
    case 'delete_chat_item':
        echo json_encode($gathering_chat->deleteGatheringChatItem($_POST['id']));
        break;

}