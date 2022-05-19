<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = GruppiTipi::getInstance(); # Inizializzo classe

if($cls->permissionManageTypes() && $cls->activeGroups()){ # Metodo di controllo per accesso alla pagina di gestione

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

    <div class="form_container group_types_management">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/gruppi/tipi/gestione_tipi_ajax.php" data-callback="updateTypesList">

            <div class="form_title">Crea tipo gruppo</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form edit_form ajax_form" action="gestione/gruppi/tipi/gestione_tipi_ajax.php" data-callback="updateTypesList">

            <div class="form_title">Modifica tipo gruppo</div>

            <div class="single_input">
                <div class="label">Tipi</div>
                <select name="id" required>
                    <?= $cls->listTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form ajax_form" action="gestione/gruppi/tipi/gestione_tipi_ajax.php" data-callback="updateTypesList">

            <div class="form_title">Elimina tipo gruppo</div>

            <div class="single_input">
                <div class="label">Tipi</div>
                <select name="id" required>
                    <?= $cls->listTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/gruppi/tipi/gestione_tipi.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a> </div>


<?php } ?>
