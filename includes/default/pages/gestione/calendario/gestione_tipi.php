<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Calendario::getInstance(); # Inizializzo classe

if ( $cls->permissionManageEvents() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>
    <div class="general_incipit">
        <div class="title"> Gestione Calendario Tipi</div>
        <div class="subtitle">Gestione delle tipologie di evento del calendario </div>

        Da questa pagina e' possibile:
        <ul>
            <li>Creare una tipologia evento</li>
            <li>Modificare una tipologia evento</li>
            <li>Eliminare una tipologia evento</li>
        </ul>


    </div>

    <div class="form_container event_types_management">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/calendario/gestione_tipi_ajax.php"
              data-callback="updateCalendarEvents">

            <div class="form_title">Crea tipologia evento</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>

            <div class="single_input">
                <div class="label">Colore BG</div>
                <input type="text" name="colore_bg" required>
            </div>

            <div class="single_input">
                <div class="label">Colore Testo</div>
                <input type="text" name="colore_testo" required>
            </div>

            <div class="single_input">
                <div class="label">Pubblico</div>
                <select name="pubblico" required>
                    <option value="0">No</option>
                    <option value="1">Si</option>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Permesso necessario per assegnare eventi di questo tipo</div>
                <select name="permessi">
                    <?=Permissions::listPermissions(); ?>
                </select>
            </div>


            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form edit_form ajax_form" action="gestione/calendario/gestione_tipi_ajax.php"
              data-callback="updateCalendarEvents">

            <div class="form_title">Modifica tipologia evento</div>

            <div class="single_input">
                <div class="label">Tipologia Evento</div>
                <select name="id" required>
                    <?= $cls->listCalendarEventTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>

            <div class="single_input">
                <div class="label">Colore BG</div>
                <input type="text" name="colore_bg" required>
            </div>

            <div class="single_input">
                <div class="label">Colore Testo</div>
                <input type="text" name="colore_testo" required>
            </div>

            <div class="single_input">
                <div class="label">Pubblico</div>
                <select name="pubblico" required>
                    <option value="0">No</option>
                    <option value="1">Si</option>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Permesso necessario per assegnare eventi di questo tipo</div>
                <select name="permessi">
                    <?=Permissions::listPermissions(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form ajax_form" action="gestione/calendario/gestione_tipi_ajax.php"
              data-callback="updateCalendarEvents">

            <div class="form_title">Elimina tipologia evento</div>

            <div class="single_input">
                <div class="label">Categoria</div>
                <select name="id" required>
                    <?= $cls->listCalendarEventTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>
    <script src="<?= Router::getPagesLink('gestione/calendario/gestione_tipi.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>


<?php } ?>

