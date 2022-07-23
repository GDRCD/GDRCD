<?php
Router::loadRequired();
$pg_ability = PersonaggioAbilita::getInstance();

switch ($_POST['action']) {

    case 'upgrade_ability':
        echo json_encode($pg_ability->upgradeAbilita($_POST));
        break;

    case 'downgrade_ability':
        echo json_encode($pg_ability->downgradeAbilita($_POST));
        break;

}
