<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Gruppi::getInstance(); # Inizializzo classe

if($cls->permissionManageGroups() && $cls->activeGroups()){ # Metodo di controllo per accesso alla pagina di gestione

?>
        <div class="general_incipit">
            <div class="title"> Gestione gruppi </div>
            <div class="subtitle">Gestione dei dati riferiti ai gruppi</div>

            Da questa pagina e' possibile:
            <ul>
                <li>Creare un gruppo</li>
                <li>Modificare un gruppo</li>
                <li>Eliminare un gruppo</li>
            </ul>

            L'eliminazione di un gruppo include anche l'eliminazione di <span class="highlight">tutti i suoi ruoli e la dissociazione dei ruoli da tutti ipersonaggi</span> ed e' irreversibile.

        </div>

    <div class="form_container group_management">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/gruppi/gruppi/gestione_gruppi_ajax.php" data-callback="updateGroupsList">

            <div class="form_title">Crea gruppo</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Logo</div>
                <input type="text" name="immagine">
            </div>

            <div class="single_input">
                <div class="label">Tipo</div>
                <select name="tipo" required>
                    <?= GruppiTipi::getInstance()->listTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Url Esterno</div>
                <input type="text" name="url" required>
            </div>
            <div class="single_input">
                <div class="label">Statuto</div>
                <textarea name="statuto"></textarea>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Visibile</div>
                <input type="checkbox" name="visibile">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form edit_form ajax_form" action="gestione/gruppi/gruppi/gestione_gruppi_ajax.php" data-callback="updateGroupsList">

            <div class="form_title">Modifica gruppo</div>

            <div class="single_input">
                <div class="label">Gruppo</div>
                <select name="id" required>
                    <?= $cls->listGroups(); ?>
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
                <div class="label">Tipo</div>
                <select name="tipo" required>
                    <?= GruppiTipi::getInstance()->listTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Url Esterno</div>
                <input type="text" name="url" required>
            </div>
            <div class="single_input">
                <div class="label">Statuto</div>
                <textarea name="statuto"></textarea>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Visibile</div>
                <input type="checkbox" name="visibile">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form ajax_form" action="gestione/gruppi/gruppi/gestione_gruppi_ajax.php" data-callback="updateGroupsList">

            <div class="form_title">Elimina gruppo</div>

            <div class="single_input">
                <div class="label">Gruppo</div>
                <select name="id" required>
                    <?= $cls->listGroups(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/gruppi/gruppi/gestione_gruppi.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a> </div>


<?php } ?>
