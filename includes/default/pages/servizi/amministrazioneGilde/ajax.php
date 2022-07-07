<?php

Router::loadRequired();

$gruppi = GruppiRuoli::getInstance();

switch ($_POST['action']) {
    case 'assign':
        echo json_encode($gruppi->AssignRole($_POST));
        break;

    case 'remove':
        echo json_encode($gruppi->RemoveRole($_POST));
        break;
}

