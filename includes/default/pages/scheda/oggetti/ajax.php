<?php
Router::loadRequired();
$scheda_class = SchedaOggetti::getInstance();

switch ($_POST['action']) {

    case 'equip':
        // Equipaggia un oggetto
        echo json_encode($scheda_class->equipObj($_POST));
        break
        ;
    case 'remove':
        // Rimuovi un oggetto
        echo json_encode($scheda_class->removeObj($_POST));
        break;

    case 'get_object_info':
        echo json_encode($scheda_class->renderObjectInfo($_POST));

}
