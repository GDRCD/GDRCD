<?php
Router::loadRequired();

$contatti=Contacts::GetInstance();
$id=Filters::int($_REQUEST['id']);//id del contatto
$cls = ContactsCategories::getInstance(); # Inizializzo classe

$idcategoria=$contatti->getContact('categoria', $id); //id del personaggio del contatto
?>
 <!-- EDIT -->
        <form class="form edit_form ajax_form" action="scheda/contatti/contatti_ajax.php"   data-callback="closeNoteContatto">

            <div class="form_title">Modifica categoria</div>

            <div class="single_input">

                <select name="categoria" required>
                    <?= $cls->listCategoriesToUpdate($idcategoria['categoria']); ?>
                </select>
            </div>


            <div class="single_input">
                <input type="hidden" name="action" value="edit_contatto"> <!-- OP NEEDED -->
                <input type="hidden" name="id" value="<?= Filters::int($id); ?>">
                <input type="submit" value="Modifica">
            </div>

        </form>

<script src="<?= Router::getPagesLink('scheda/contatti/JS/edit_contatto.js'); ?>"></script>
