<?php

require_once(__DIR__.'/required.php');

switch ($_POST['action']) {
    case 'getRealPath':
        echo json_encode(
            ['link' => Router::getAjaxPagesLink($_POST['path'])]
        );
        break;
}