<?php

Router::loadRequired();

$cls = ContactsCategories::getInstance();

switch ($_POST['action']) {
    case 'get_group_data':
        echo json_encode($cls->ajaxCategoriesData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->NewCategories($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->ModCategories($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->DelCategories($_POST));
        break;
}

