<?php

Router::loadRequired();

$cls = Notifiche::getInstance();

switch ( $_POST['action'] ) {
    case 'notifications_list':
        echo json_encode($cls->ajaxNotificationsListData());
        break;

    case 'count_new_notifications':
        echo json_encode($cls->ajaxCountNewNotifications());
        break;

    case 'read_all':
        echo json_encode($cls->setAllNotificationsRead());
        break;
}

