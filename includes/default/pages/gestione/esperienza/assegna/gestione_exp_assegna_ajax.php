<?php
Router::loadRequired();

$cls = PersonaggioEsperienza::getInstance();

switch ($_POST['action']) {
    case 'op_assign':
        $cls = new PersonaggioEsperienza();
        $cls->setCharacterId(Filters::int($_POST['personaggio'])); // Set the character ID
        echo json_encode($cls->addExperience($_POST));
        break;
    
    case 'op_remove':
        $cls = new PersonaggioEsperienza();
        $cls->setCharacterId(Filters::int($_POST['personaggio'])); // Set the character ID
        echo json_encode($cls->removeExperience($_POST));
        break;
}
