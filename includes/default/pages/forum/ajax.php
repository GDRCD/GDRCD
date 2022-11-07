<?php

Router::loadRequired();

$cls = ForumPosts::getInstance();

switch ( $_POST['action'] ) {
    case 'new_post':
        echo json_encode($cls->newPost($_POST));
        break;

    case 'edit_post':
        echo json_encode($cls->editPost($_POST));
        break;

    case 'delete_post':
        echo json_encode($cls->deletePost($_POST));
        break;

    case 'comment_post':
        echo json_encode($cls->commentPost($_POST));
        break;

    case 'restore_post':
        echo json_encode($cls->restorePost($_POST));
        break;

    case 'lock_post':
        echo json_encode($cls->lockPost($_POST));
        break;

    case 'important_post':
        echo json_encode($cls->importantPost($_POST));
        break;

    case 'frame_text':
        echo json_encode($cls->ajaxFrameText());
        break;
}

