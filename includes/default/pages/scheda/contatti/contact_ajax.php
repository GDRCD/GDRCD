<?php
Router::loadRequired();
$contatti = Contatti::getInstance();
$contatti_nota=ContattiNote::getInstance();

switch ($_POST['action']) {

    case 'contact_new':
        //Aggiunge un nuovo contatto al PG
        echo json_encode($contatti->newContatto($_POST));
        break;

    case 'edit_contatto':
        //modifica la categoria di un contatto
        echo json_encode($contatti->editContatto($_POST));
        break;

    case 'delete_contatto':
        //cancella un contatto
        echo json_encode($contatti->deleteContatto($_POST['id']));
        break;

    case 'note_new':
        //Aggiunge una nuova nota al contatto
        echo json_encode($contatti_nota->newNota($_POST));
        break;

    case 'note_edit':
        //modifica una nota
        echo json_encode($contatti_nota->editNota($_POST));
        break;

    case 'delete_nota':
        //cancella una nota
        echo json_encode($contatti_nota->deleteNota($_POST['id']));
        break;
}