<?php

Router::loadRequired();

$cls = News::getInstance();

switch ( $_POST['action'] ) {
    case 'frame_text':
        echo json_encode($cls->ajaxFrameText());
        break;
}

