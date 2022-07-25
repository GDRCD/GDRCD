<?php
Router::loadRequired();
$scheda_chat_opzioni = SchedaChatOpzioni::getInstance();

switch ( $_POST['action'] ) {

    case 'save_options':
        //Aggiunge un nuovo contatto al PG
        echo json_encode($scheda_chat_opzioni->updateOptions($_POST));
        break;

}
