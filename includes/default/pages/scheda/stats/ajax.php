<?php
Router::loadRequired();
$pg_stats = PersonaggioStats::getInstance();

switch ($_POST['action']) {

    case 'upgrade_stat':
        echo json_encode(PersonaggioStats::upgradePgStat($_POST));
        break;

    case 'downgrade_stat':
        echo json_encode(PersonaggioStats::downgradePgStat($_POST));
        break;

}
