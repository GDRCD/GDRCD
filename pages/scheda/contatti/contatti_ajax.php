<?php
require_once(__DIR__ . '/../../../includes/required.php');
$contatti = Contacts::getInstance();
$contatti_nota=ContactsNotes::getInstance();



switch ($_POST['action']) {
    case 'new_contatto':
        //Aggiunge un nuovo contatto al PG
        echo json_encode($contatti->newContatto($_POST));
        break;
    case 'delete_contatto':
        //cancella un contatto
        echo json_encode($contatti->deleteContatto($_POST['id']));
        break;
    case 'new_nota':
        //Aggiunge una nuova nota al contatto
        echo json_encode($contatti_nota->newNota($_POST));
        break;
    case 'delete_nota':
        //cancella una nota
        echo json_encode($contatti_nota->deleteNota($_POST['id']));
        break;
    case 'edit_nota':
        //modifica una nota
        echo json_encode($contatti_nota->editNota($_POST));
        break;

}
