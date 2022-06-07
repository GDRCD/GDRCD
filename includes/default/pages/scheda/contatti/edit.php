<?php
Router::loadRequired();

$contatti=Contacts::GetInstance();
$id=Filters::int($_REQUEST['id']);//id del contatto
$cls = ContactsCategories::getInstance(); # Inizializzo classe

$idcategoria=$contatti->getContact('categoria', $id); //id del personaggio del contatto
?>
 <!-- EDIT -->
        <form class="form edit_form ajax_form" action="gestione/contatti/gestione_categorie_ajax.php" data-callback="updateCategoriesList">

            <div class="form_title">Modifica categoria</div>

            <div class="single_input">

                <select name="id" required>
                    <?= $cls->listCategoriesToUpdate($idcategoria['categoria']); ?>
                </select>
            </div>


            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>