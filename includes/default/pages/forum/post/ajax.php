<?php

Router::loadRequired();

$cls = ForumPosts::getInstance();

switch ( $_POST['action'] ) {
    case 'new_post':
        echo json_encode($cls->newPost($_POST));
        break;
}

