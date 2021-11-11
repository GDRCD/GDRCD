<?php

require_once(__DIR__ . '/../../includes/required.php');

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
    <?= $esiti->esitiListPlayer(); ?>
    <div class="tr footer">
        <a class="but_newd" href='main.php?page=servizi_esiti&op=new'>
            Nuovo esito
        </a> |
        <a href="/main.php?page=uffici">Indietro</a>
    </div>
</div>

