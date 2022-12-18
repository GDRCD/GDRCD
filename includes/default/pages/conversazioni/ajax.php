<?php

Router::loadRequired();

$cls = Conversazioni::getInstance();

switch ( $_POST['action'] ) {
    case 'get_filtered_conversations':
        echo json_encode($cls->ajaxConversations($_POST));
        break;

    case 'send_message':
        echo json_encode($cls->sendMessage($_POST));
        break;

    case 'new_conversation':
        echo json_encode($cls->newConversation($_POST));
        break;

    case 'edit_conversation':
        echo json_encode($cls->editConversation($_POST));
        break;

    case 'delete_conversation':
        echo json_encode($cls->deleteConversation($_POST));
        break;

    case 'frame_text':
        echo json_encode($cls->ajaxFrameText($_POST));
        break;
}

