<?php

$esiti_chat = Functions::get_constant('ESITI_CHAT');
$esiti= Functions::get_constant('ESITI_ENABLE');
$perm= Permissions::permission('MANAGE_ESITI');

if ($perm && $esiti) {
    /*
    * Richieste POST
    */
    switch (Filters::get($_POST['op'])) {
        case 'modify': //Modifica blocco esiti
            include('new_esito/modify.php');
            break;
        case 'insert': //Inserimento nuovo blocco
            include('new_esito/insert.php');
            break;
        case 'add': //Aggiungi nuovo esito a db
            include('new_esito/add.php');
            break;
    }
    /*
    * Richieste GET
    */
    switch (Filters::get($_GET['op'])) {
        case 'new': //Invia nuovo esito
            include('new_esito/new.php');
            break;
        case 'edit': //Modifica blocco esiti
            include('new_esito/edit.php');
            break;
        case 'newchat': //Invia nuovo esito in chat
            include('new_esito/new_chat.php');
            break;
        case 'first': //Compilazione nuovo blocco
            include('new_esito/first.php');
            break;
        default:
            include('esiti_master.php');
            break;
    }

} else {
    echo '<div class="warning">Non hai i permessi per visualizzare questa sezione</div>';
} ?>