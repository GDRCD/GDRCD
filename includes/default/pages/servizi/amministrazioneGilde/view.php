<?php

Router::loadRequired();

$gruppi = Gruppi::getInstance();
?>

<div class="general_title">Aggiunta ruolo</div>

<form class="ajax_form" action="servizi/amministrazioneGilde/ajax.php">

    <div class="single_input">
        <div class="label">Personaggio</div>
        <select name="personaggio" id="personaggio" required>
            <?= Personaggio::getInstance()->listPgs(); ?>
        </select>
    </div>

    <div class="single_input">
        <div class="label">Ruoli</div>
        <select name="ruolo" id="ruolo" required>
            <?= GruppiRuoli::getInstance()->listAvailableRoles(); ?>
        </select>
    </div>

    <div class="single_input">
        <input type="hidden" name="action" value="assign"> <!-- OP NEEDED -->
        <input type="submit" value="Assegna">
    </div>

</form>

<div class="general_title">Rimuovi ruolo</div>

<form class="ajax_form" action="servizi/amministrazioneGilde/ajax.php">

    <div class="single_input">
        <div class="label">Personaggio</div>
        <select name="personaggio" id="personaggio" required>
            <?= Personaggio::getInstance()->listPgs(); ?>
        </select>
    </div>

    <div class="single_input">
        <div class="label">Ruoli</div>
        <select name="ruolo" id="ruolo" required>
            <?= GruppiRuoli::getInstance()->listAvailableRoles(); ?>
        </select>
    </div>

    <div class="single_input">
        <input type="hidden" name="action" value="remove"> <!-- OP NEEDED -->
        <input type="submit" value="Rimuovi">
    </div>

</form>


<div class="link_back">
    <a href="main.php?page=servizi/amministrazioneGilde/index&op=view_extra_earn">Gestisci stipendi extra</a>
</div>

<div class="link_back">
    <a href="main.php?page=servizi">Torna indietro</a>
</div>