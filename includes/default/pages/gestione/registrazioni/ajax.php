
<?php
Router::loadRequired();
$scheda_class = RegistrazioneGiocate::getInstance();

switch ( $_POST['action'] ) {

    case 'registrazione_new':
        echo json_encode($scheda_class->newRegistrazione($_POST));
        break;

    case 'registrazione_edit':
        echo json_encode($scheda_class->editRegistrazione($_POST));
        break;

    case 'registrazioni_delete':
        echo json_encode($scheda_class->deleteRegistrazione($_POST));
        break;

    case 'registrazioni_controllata':
        echo json_encode($scheda_class->setControlledRegistrazione($_POST));
        break;

    case 'registrazioni_bloccata':
        echo json_encode($scheda_class->setBlockedRegistrazione($_POST));
        break;
}
