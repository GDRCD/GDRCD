<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = GruppiLavori::getInstance(); # Inizializzo classe

if($cls->permissionManageWorks() && $cls->activeWorks()){ # Metodo di controllo per accesso alla pagina di gestione

?>
        <div class="general_incipit">
            <div class="title"> Gestione lavori </div>
            <div class="subtitle">Gestione dei dati riferiti ai lavori</div>

            Da questa pagina e' possibile:
            <ul>
                <li>Creare un lavoro</li>
                <li>Modificare un lavoro</li>
                <li>Eliminare un lavoro</li>
            </ul>
        </div>

    <div class="form_container works_management">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/gruppi/lavori/gestione_lavori_ajax.php" data-callback="updateWorksList">

            <div class="form_title">Crea lavoro</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Logo</div>
                <input type="text" name="immagine">
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Stipendio</div>
                <input type="number" name="stipendio">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form edit_form ajax_form" action="gestione/gruppi/lavori/gestione_lavori_ajax.php" data-callback="updateWorksList">

            <div class="form_title">Modifica gruppo</div>

            <div class="single_input">
                <div class="label">Lavori</div>
                <select name="id" required>
                    <?= $cls->listWorks(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Logo</div>
                <input type="text" name="immagine">
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Stipendio</div>
                <input type="number" name="stipendio">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form ajax_form" action="gestione/gruppi/lavori/gestione_lavori_ajax.php" data-callback="updateWorksList">

            <div class="form_title">Elimina gruppo</div>

            <div class="single_input">
                <div class="label">Lavori</div>
                <select name="id" required>
                    <?= $cls->listWorks(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/gruppi/lavori/gestione_lavori.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a> </div>


<?php } ?>
