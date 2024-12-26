<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

Router::loadRequired();

$cls = PersonaggioEsperienza::getInstance();

switch ($_POST['action']) {
    case 'op_assign':
        $cls = new PersonaggioEsperienza();
        $cls->setCharacterId(Filters::int($_POST['personaggio'])); // Set the character ID
        echo json_encode($cls->addExperience($_POST));
        break;
}
