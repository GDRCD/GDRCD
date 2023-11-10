<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Disponibilita::getInstance(); # Inizializzo classe

if ( $cls->permissionManageAvailabilities() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>
    <div class="general_incipit">
        <div class="title"> Gestione Disponibilità</div>
        <div class="subtitle">Gestione dei Disponibilità presenti</div>

        Da questa pagina è possibile:
        <ul>
            <li>Creare una nuova disponibilità</li>
            <li>Modificare una disponibilità</li>
            <li>Eliminare una disponibilità</li>
        </ul>

    </div>

    <div class="form_container manage_availability_container">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/disponibilita/gestione_disponibilita_ajax.php"
              data-callback="updateAvailabilities">

            <div class="form_title">Crea Disponibilità</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Immagine</div>
                <input type="text" name="immagine">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form ajax_form edit_form" action="gestione/disponibilita/gestione_disponibilita_ajax.php"
              data-callback="updateAvailabilities">

            <div class="form_title">Modifica disponibilità</div>

            <div class="single_input">
                <div class="label">Disponibilità</div>
                <select name="id" required>
                    <?= $cls->listAvailabilities(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Immagine</div>
                <input type="text" name="immagine">
            </div>


            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form class="form ajax_form" action="gestione/disponibilita/gestione_disponibilita_ajax.php"
              data-callback="updateAvailabilities">

            <div class="form_title">Elimina una disponibilità</div>

            <div class="single_input">
                <div class="label">Disponibilità</div>
                <select name="id" required>
                    <?= $cls->listAvailabilities(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/disponibilita/gestione_disponibilita.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>


<?php } ?>
