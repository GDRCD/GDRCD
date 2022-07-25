<?php
Router::loadRequired();
$scheda_class = Scheda::getInstance();

switch ($_POST['action']) {

    case 'update_character_data':
        echo json_encode($scheda_class->updateCharacterData($_POST));
        break;

    case 'update_character_status':
        echo json_encode($scheda_class->updateCharacterStatus($_POST));
        break;

    case 'ban_character':
        echo json_encode($scheda_class->banCharacter($_POST));
        break;

}
