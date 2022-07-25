<?php

$id_pg = Filters::in($_GET['id_pg']);

if ( Scheda::getInstance()->permissionAdministrationCharacter() ) {

    $pg_data = Personaggio::getPgData($id_pg);
    ?>


    <div class="general_title">Dati Amministrazione personaggio</div>

    <form method="POST" class="ajax_form chat_form_ajax" action="scheda/modifica/ajax.php" data-reset="false">

        <div class="single_input">
            <div class="label">Sesso</div>
            <select name="sesso" required>
                <?= Sessi::getInstance()->listGenders(Filters::in($pg_data['sesso'])); ?>
            </select>
        </div>

        <div class="single_input">
            <div class="label">Razza</div>
            <select name="razza" required>
                <?= Razze::getInstance()->listRaces(Filters::in($pg_data['razza'])); ?>
            </select>
        </div>

        <div class="single_input">
            <div class="label">Denaro in banca</div>
            <input type="text" name="banca" value="<?= Filters::int($pg_data['banca']); ?>" required>
        </div>

        <div class="single_input">
            <div class="label">Denaro in tasca</div>
            <input type="text" name="soldi" value="<?= Filters::int($pg_data['soldi']); ?>" required>
        </div>


        <div class="single_input">
            <input type="hidden" name="action" value="update_character_administration">
            <input type="hidden" name="pg" value="<?= $id_pg; ?>">
            <input type="submit" value="Modifica">
        </div>
    </form>


<?php } ?>