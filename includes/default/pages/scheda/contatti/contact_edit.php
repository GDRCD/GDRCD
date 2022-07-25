<?php
Router::loadRequired();

$contatti = Contatti::GetInstance();
$contatti_categorie = ContattiCategorie::getInstance(); # Inizializzo classe
$id = Filters::int($_REQUEST['id']);//id del contatto

$contact_data = $contatti->getContact($id, 'categoria'); ?>

<!-- EDIT -->
<form class="form edit_form ajax_form" action="scheda/contatti/contact_ajax.php" data-callback="closeNoteContatto">

    <div class="form_title">Modifica categoria</div>

    <div class="single_input">

        <select name="categoria" required>
            <?= $contatti_categorie->listCategories($contact_data['categoria']); ?>
        </select>
    </div>

    <div class="single_input">
        <input type="hidden" name="action" value="edit_contatto"> <!-- OP NEEDED -->
        <input type="hidden" name="id" value="<?= Filters::int($id); ?>">
        <input type="submit" value="Modifica">
    </div>

</form>

<script src="<?= Router::getPagesLink('scheda/contatti/JS/contact_edit.js'); ?>"></script>
