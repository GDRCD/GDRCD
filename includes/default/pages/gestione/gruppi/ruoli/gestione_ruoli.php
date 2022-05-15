<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = GruppiRuoli::getInstance(); # Inizializzo classe

if($cls->permissionManageRoles() && $cls->activeGroups()){ # Metodo di controllo per accesso alla pagina di gestione

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

        </div>

    <div class="form_container group_roles_management">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/gruppi/ruoli/gestione_ruoli_ajax.php" data-callback="updateRolesList">

            <div class="form_title">Crea ruolo gruppo</div>

            <div class="single_input">
                <div class="label">Gruppo</div>
                <select name="gruppo" required>
                    <?= Gruppi::getInstance()->listGroups(); ?>
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
                <div class="label">Stipendio</div>
                <input type="number" name="stipendio">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Poteri</div>
                <input type="checkbox" name="poteri">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form edit_form ajax_form" action="gestione/gruppi/ruoli/gestione_ruoli_ajax.php" data-callback="updateRolesList">

            <div class="form_title">Modifica ruolo gruppo</div>

            <div class="single_input">
                <div class="label">Ruolo</div>
                <select name="id" required>
                    <?= $cls->listRoles(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Gruppo</div>
                <select name="gruppo" required>
                    <?= Gruppi::getInstance()->listGroups(); ?>
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
                <div class="label">Stipendio</div>
                <input type="number" name="stipendio">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Poteri</div>
                <input type="checkbox" name="poteri">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form ajax_form" action="gestione/gruppi/ruoli/gestione_ruoli_ajax.php" data-callback="updateRolesList">

            <div class="form_title">Elimina ruolo gruppo</div>

            <div class="single_input">
                <div class="label">Ruolo</div>
                <select name="id" required>
                    <?= $cls->listRoles(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/gruppi/ruoli/gestione_ruoli.js'); ?>"></script>


<?php } ?>
