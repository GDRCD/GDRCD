<?php
Router::loadRequired();

$contatti = Contatti::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$pg = Filters::out($_REQUEST['pg']);

$op = Filters::out($_GET['op']);
?>
<div class="gestione_incipit">
    Aggiunta di un nuovo contatto
</div>

<div class="form_container">
    <form class="form ajax_form"
          action="scheda/contatti/contact_ajax.php"
          data-reset="false"
          data-callback="goBackContatti">

        <div class="single_input">
            <div class='label'>
                Nome
            </div>
            <select name="contatto" required>
                <?= Contatti::getInstance()->filteredCharactersList($id_pg) ?>
            </select>
        </div>

        <div class="single_input">
            <div class='label'>
                Categoria
            </div>
            <select name="categoria">
                <?php
                echo $contatti->listContactCategories();
                ?>
            </select>
        </div>

        <div class="single_input">
            <input type="hidden" name="action" value="contact_new">
            <input type="hidden" id="id_pg" name="id_pg" value="<?= $id_pg ?>">
            <input type="hidden" id="pg" name="pg" value="<?= $pg ?>">
            <input type="hidden" id="url" value="/main.php?page=scheda/index&op=contatti&id_pg=<?= $id_pg ?>">
            <input type="submit" value="Crea contatto"/>
        </div>
    </form>


    <div class="link_back">
        <a href="/main.php?page=scheda/index&op=contatti&id_pg=<?= $id_pg ?>">Torna indietro</a>
    </div>

</div>

<script src="<?= Router::getPagesLink('scheda/contatti/JS/contact_new.js'); ?>"></script>