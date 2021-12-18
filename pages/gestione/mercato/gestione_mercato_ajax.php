<?php

require_once(__DIR__.'/../../../includes/required.php');

$cls = Mercato::getInstance();

switch ($_POST['action']){
    case 'get_shop_data':
        echo json_encode($cls->ajaxGetShop($_POST));
        break;
}