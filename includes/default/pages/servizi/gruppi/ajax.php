<?php

Router::loadRequired();

$action = Filters::in($_POST['action']);
$gruppi_oggetto = GruppiOggetto::getInstance();

switch ( $action ) {
    # Invio azione
    case 'get_storage_object_info':
        echo json_encode($gruppi_oggetto->renderAjaxSingleObjectData($_POST));
        break;

    case 'get_storage_item':
        echo json_encode($gruppi_oggetto->retireObjectFromStorage($_POST));
        break;

}