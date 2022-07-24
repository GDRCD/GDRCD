<?php

$id_pg = Filters::int($_GET['id_pg']);
$id_diario = Filters::int($_GET['id']);
$diary_data = SchedaDiario::getInstance()->getDiary($id_diario);

if (SchedaDiario::getInstance()->diaryActive()) {

    ?>

    <div class="form_container">
        <form class="form ajax_form"
              action="scheda/diario/ajax.php"
              data-reset="false">
            <!-- TITOLO -->
            <div class="single_input">
                <div class="label">Titolo</div>
                <input type="text" name="titolo" value="<?= Filters::out($diary_data['titolo']); ?>" required>
            </div>

            <!-- DATA -->
            <div class="single_input">
                <div class="label">Date</div>
                <input required type="date" name="data" class="form_input"
                       value="<?= Filters::out($diary_data['data']); ?>"/>
            </div>


            <!-- TESTO -->
            <div class="single_input">
                <div class="label">Testo</div>
                <textarea name="testo"><?= Filters::out($diary_data['testo']); ?></textarea>
            </div>

            <!-- VISIBILE -->
            <div class="single_input">
                <div class="label">Visibile</div>
                <input type="checkbox" <?= (Filters::bool($diary_data['visibile']) ? 'checked' : ''); ?>
                       name="visibile"/>
            </div>

            <!-- SUBMIT -->
            <div class="single_input">
                <input type="submit" name="submit" value="Modifica"/>
                <input hidden name="action" value="edit_diary">
                <input hidden name="id" value="<?= $id_diario; ?>">
            </div>
        </form>
    </div>

    <div class="link_back">
        <a href="/main.php?page=scheda/index&op=diario&id_pg=<?= $id_pg ?>">Torna indietro</a>
    </div>
<?php } ?>