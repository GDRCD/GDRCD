<?php

Router::loadRequired();

$gruppi = GruppiLavori::getInstance();

if ( $gruppi->activeWorks() && $gruppi->permissionManageWorks() ) {
    ?>

    <div class="general_title">Aggiunta lavoro</div>

    <form class="ajax_form" action="gestione/gruppi/lavori/gestione_lavori_ajax.php">

        <div class="single_input">
            <div class="label">Personaggio</div>
            <select name="personaggio" id="personaggio" required>
                <?= Personaggio::getInstance()->listPgs(); ?>
            </select>
        </div>

        <div class="single_input">
            <div class="label">Lavoro</div>
            <select name="lavoro" id="lavoro" required>
                <?= $gruppi->listWorks(); ?>
            </select>
        </div>

        <div class="single_input">
            <input type="hidden" name="action" value="assign_work"> <!-- OP NEEDED -->
            <input type="submit" value="Assegna">
        </div>

    </form>

    <div class="general_title">Rimuovi lavoro</div>

    <form class="ajax_form" action="gestione/gruppi/lavori/gestione_lavori_ajax.php">

        <div class="single_input">
            <div class="label">Personaggio</div>
            <select name="personaggio" id="personaggio" required>
                <?= Personaggio::getInstance()->listPgs(); ?>
            </select>
        </div>

        <div class="single_input">
            <div class="label">Lavoro</div>
            <select name="lavoro" id="lavoro" required>
                <?= $gruppi->listWorks(); ?>
            </select>
        </div>

        <div class="single_input">
            <input type="hidden" name="action" value="remove_work"> <!-- OP NEEDED -->
            <input type="submit" value="Rimuovi">
        </div>

    </form>
<?php } ?>