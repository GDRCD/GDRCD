<?php

require_once(__DIR__ . '/required.php');

if ( $_POST['path'] ) {
    Router::loadPages($_POST['path']);
}