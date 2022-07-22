<?php

Router::loadRequired();

$gruppi = GruppiRuoli::getInstance();
$gruppiStipendi = GruppiStipendiExtra::getInstance();

switch ($_POST['action']) {
    case 'assign':
        echo json_encode($gruppi->AssignRole($_POST));
        break;

    case 'remove':
        echo json_encode($gruppi->RemoveRole($_POST));
        break;

    case 'new_extra_earn':
        echo json_encode($gruppiStipendi->NewExtraEarnByBoss($_POST));
        break;

    case 'mod_extra_earn':
        echo json_encode($gruppiStipendi->ModExtraEarnByBoss($_POST));
        break;

    case 'remove_extra_earn':
        echo json_encode($gruppiStipendi->RemoveExtraEarnByBoss($_POST));
        break;
}

