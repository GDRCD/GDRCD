<?php
    /*
    * Richieste POST
    */
    switch (Filters::get($_POST['op'])) {
        case 'insert': //Inserimento nuovo blocco
            include('esiti_pg/insert.php');
            break;
        case 'add': //Aggiungi nuovo esito a db
            include('esiti_pg/add.php');
            break;
    }
    /*
    * Richieste GET
    */
    switch (Filters::get($_GET['op'])) {
        case 'new': //Invia nuovo esito
            include('esiti_pg/new.php');
            break;
        case 'first': //Compilazione nuovo blocco
            include('esiti_pg/first.php');
            break;
    }
 ?>