<?php

Router::loadRequired();

$cls = TicketsSections::getInstance();

switch ( $_POST['action'] ) {
    case 'get_section_data':
        echo json_encode($cls->ajaxSectionData($_POST));
        break;

    case 'op_insert_section':
        echo json_encode($cls->insertSection($_POST));
        break;
    case 'op_edit_section':
        echo json_encode($cls->editSection($_POST));
        break;
    case 'op_delete_section':
        echo json_encode($cls->deleteSection($_POST));
        break;
}