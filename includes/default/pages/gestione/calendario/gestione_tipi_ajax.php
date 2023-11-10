<?php

Router::loadRequired();

$cls = Calendario::getInstance();

switch ( $_POST['action'] ) {
    case 'get_calendar_event_type':
        echo json_encode($cls->ajaxCalendarEventType($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->NewEventType($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->ModEventType($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->DelEventType($_POST));
        break;
}

