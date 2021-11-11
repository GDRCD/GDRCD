<?php

require_once(__DIR__ . '/../../../includes/required.php');

$esiti = Esiti::getInstance();
?>

<div class="gestione_incipit">
    Lista delle conversazioni degli esiti.
</div>

<div class="fake-table esiti_list">
    <div class="tr header">
        <div class="td">Data</div>
        <div class="td">Autore</div>
        <div class="td">Stato</div>
        <div class="td">Titolo</div>
        <div class="td">Numero Esiti</div>
        <div class="td">Nuove risposte</div>
        <div class="td">Controlli</div>
    </div>
    <?= $esiti->esitiListManagement(); ?>
    <div class="tr footer">
        <a href='main.php?page=gestione_esiti&op=new'>
            Nuovo esito
        </a> |
        <a href="/main.php?page=gestione">Indietro</a>
    </div>
</div>

