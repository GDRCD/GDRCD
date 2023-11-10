<?php

Router::loadRequired();

$cls = Abilita::getInstance();

switch ( $_POST['action'] ) {
    case 'get_ability_data':
        echo json_encode($cls->ajaxAbilityData($_POST));
        break;

    case 'op_insert':
        echo json_encode($cls->newAbility($_POST));
        break;

    case 'op_edit':
        echo json_encode($cls->modAbility($_POST));
        break;

    case 'op_delete':
        echo json_encode($cls->delAbility($_POST));
        break;
}

