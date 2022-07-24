<?php
Router::loadRequired();
$pg_ability = SchedaDiario::getInstance();

switch ($_POST['action']) {

    case 'delete_diary':
        echo json_encode($pg_ability->deleteDiary($_POST));
        break;

        case 'new_diary':
        echo json_encode($pg_ability->newDiary($_POST));
        break;

        case 'edit_diary':
        echo json_encode($pg_ability->editDiary($_POST));
        break;

}
