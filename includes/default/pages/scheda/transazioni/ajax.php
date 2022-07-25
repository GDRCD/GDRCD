<?php
Router::loadRequired();
$schedaTransazioni = SchedaTransazioni::getInstance();

switch ($_POST['action']) {

    case 'add_exp':
        //Aggiunge un nuovo contatto al PG
        echo json_encode($schedaTransazioni->addManualExp($_POST));
        break;

}
