<?php

require_once(__DIR__.'/../../../includes/required.php');

$cls = Mercato::getInstance();

switch ($_POST['action']){
    case 'get_shop_data':
        echo json_encode($cls->ajaxGetShop($_POST));
        break;

    case 'op_insert_shop':
        echo json_encode($cls->insertShop($_POST));
        break;
    case 'op_edit_shop':
        echo json_encode($cls->editShop($_POST));
        break;
    case 'op_delete_shop':
        echo json_encode($cls->deleteShop($_POST));
        break;

    case 'op_insert_shop_obj':
        echo json_encode($cls->insertShopObj($_POST));
        break;
    case 'op_edit_shop_obj':
        echo json_encode($cls->editShopObj($_POST));
        break;
    case 'op_delete_shop_obj':
        echo json_encode($cls->deleteShopObj($_POST));
        break;
}