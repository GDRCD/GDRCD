<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Oggetti::getInstance(); # Inizializzo classe

if ( $cls->permissionManageObjectsType() ) { # Metodo di controllo per accesso alla pagina di gestione

    if ( isset($_POST['op']) ) { # Se ho richiesto un'operazione
        switch ( $_POST['op'] ) { # In base al tipo di operazione eseguo insert/edit/delete/altro

        }
    }

    ?>

    <div class="general_incipit">
        <div class="title"> Gestione Tipologia oggetti</div>
        <div class="subtitle"> Sezione per la gestione delle tipologie di oggetti</div>
        <br>


        Da questa pagina e' possibile:
        <ul>
            <li>Creare una tipologia oggetto</li>
            <li>Modificare una tipologia oggetto</li>
            <li>Eliminare una tipologia oggetto</li>
        </ul>

    </div>


    <div class="form_container gestione_oggetti_tipo">

        <!-- INSERT -->
        <form class="form ajax_form"
              action="gestione/oggetti/gestione_oggetti_ajax.php"
              data-callback="refreshObjTypeList">

            <div class="form_title">Creazione tipologia oggetto</div>

            <!-- NOME -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <!-- Descrizione -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert_object_type"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form edit-form ajax_form"
              action="gestione/oggetti/gestione_oggetti_ajax.php"
              data-callback="refreshObjTypeList">

            <div class="form_title">Modifica tipologia oggetto</div>

            <!-- TIPO -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Tipo</div>
                <select name="tipo">
                    <?= $cls->listObjectTypes(); ?>
                </select>
            </div>

            <!-- NOME -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <!-- Descrizione -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit_object_type"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form class="form ajax_form"
              action="gestione/oggetti/gestione_oggetti_ajax.php"
              data-callback="refreshObjTypeList">

            <div class="form_title">Elimina tipologia Oggetto</div>

            <!-- TIPO -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Tipo</div>
                <select name="tipo">
                    <?= $cls->listObjectTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete_object_type"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/oggetti/gestione_oggetti_tipo.js'); ?>"></script>

    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>

<?php } ?>
