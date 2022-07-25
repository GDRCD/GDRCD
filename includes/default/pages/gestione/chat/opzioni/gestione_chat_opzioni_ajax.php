<?php

Router::loadRequired();

$chatOpt = ChatOpzioni::getInstance();

switch ( $_POST['action'] ) {
    case 'get_option_data':
        echo json_encode($chatOpt->ajaxOptionData($_POST));
        break;

    case 'get_conditions_list':
        echo json_encode($chatOpt->ajaxCondList());
        break;

    case 'op_insert':
        echo json_encode($chatOpt->NewOption($_POST));
        break;

    case 'op_edit':
        echo json_encode($chatOpt->ModOption($_POST));
        break;

    case 'op_delete':
        echo json_encode($chatOpt->DelOption($_POST));
        break;
}

