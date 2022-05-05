<?php

switch (Filters::out($_POST['op'])) {

    case 'save':
        Meteo::getInstance()->saveSetting($_POST);
        break;
    case 'save_chat':

        $id = Filters::in( $_REQUEST['dir']);
        Meteo::getInstance()->saveChat($_POST , $id);
        break;
    default:
        die('Operazione non riconosciuta.');
}


echo '<div class="warning">' .Filters::out($MESSAGE['warning']['done']) . '</div>';

?>