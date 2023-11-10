<?php

Router::loadRequired();

$cls = ContattiCategorie::getInstance();

switch ( $_POST['action'] ) {
    case 'get_category_data':
        echo json_encode($cls->ajaxCategoriesData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->NewCategory($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->ModCategory($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->DelCategory($_POST));
        break;
}

