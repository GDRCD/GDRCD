<?php

Router::loadRequired();

$cls = Calendario::getInstance();

switch ( $_POST['action'] ) {
    case 'get_calendar_settings':
        echo json_encode($cls->ajaxCalendarSettings());
        break;

    case 'get_calendar_events':
        echo json_encode($cls->ajaxCalendarEvents());
        break;

    case 'get_calendar_form_body':
        echo json_encode($cls->ajaxCalendarFormBody());
        break;

    case 'get_event_data':
        echo json_encode($cls->ajaxCalendarEventData($_POST));
        break;

    case 'add_event':
        echo json_encode($cls->addEvent($_POST));
        break;

    case 'remove_event':
        echo json_encode($cls->removeEvent($_POST));
        break;

}

