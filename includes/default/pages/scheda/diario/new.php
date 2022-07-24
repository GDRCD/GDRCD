<?php /*HELP: */

$id_pg = Filters::int($_GET['id_pg']);

?>

<div class="form_container">
    <form class="form ajax_form"
          action="scheda/diario/ajax.php"
          data-reset="false">
        >
        <!-- TITOLO -->
        <div class="single_input">
            <div class="label">Titolo</div>
            <input type="text" name="titolo" class="form_input" required/>
        </div>

        <!-- DATA -->
        <div class="single_input">
            <div class="label">Data</div>
            <input required type="date" name="data" class="form_input" value="<?= date('Y-m-d'); ?>"/>
        </div>

        <!-- VISIBILE -->
        <div class="single_input">
            <div class="label">Visibile</div>
            <input type="checkbox" name="visibile"/>
        </div>

        <!-- TESTO -->
        <div class="single_input">
            <div class="label">Testo</div>
            <textarea name="testo"></textarea>
        </div>

        <!-- SUBMIT -->
        <div class="single_input">
            <input type="submit" name="submit" value="Salva"/>
            <input type="hidden" name="action" value="new_diary">
            <input type="hidden" name="pg" value="<?= $id_pg; ?>">
        </div>
</div>


<div class="link_back">
    <a href="/main.php?page=scheda/index&op=diario&id_pg=<?= $id_pg ?>">Torna indietro</a>
</div>
