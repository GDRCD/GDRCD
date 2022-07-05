<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = ContattiCategorie::getInstance(); # Inizializzo classe

if($cls->permissionManageCategories() ){ # Metodo di controllo per accesso alla pagina di gestione

    ?>
    <div class="general_incipit">
        <div class="title"> Gestione Categorie contatti </div>
        <div class="subtitle">Gestione dei dati riferiti alle categorie dei contatti</div>

        Da questa pagina e' possibile:
        <ul>
            <li>Creare una categoria</li>
            <li>Modificare una categoria</li>
            <li>Eliminare una categoria</li>
        </ul>


    </div>

    <div class="form_container categories_management">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/contatti/gestione_categorie_ajax.php" data-callback="updateCategoriesList">

            <div class="form_title">Crea categoria</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>
            <input type="hidden" name="creato_da" value="<?=Functions::getInstance()->getMyId();?>" >


            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form edit_form ajax_form" action="gestione/contatti/gestione_categorie_ajax.php" data-callback="updateCategoriesList">

            <div class="form_title">Modifica categoria</div>

            <div class="single_input">
                <div class="label">Categorie</div>
                <select name="id" required>
                    <?= $cls->listCategories(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>


            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form ajax_form" action="gestione/contatti/gestione_categorie_ajax.php" data-callback="updateCategoriesList">

            <div class="form_title">Elimina categoria</div>

            <div class="single_input">
                <div class="label">Categoria</div>
                <select name="id" required>

                    <?= $cls->listCategories(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>
    <script src="<?= Router::getPagesLink('gestione/contatti/gestione_categorie.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a> </div>




<?php } ?>

