<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Razze::getInstance(); # Inizializzo classe

if ( $cls->permissionManageRaces() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>
    <div class="general_incipit">
        <div class="title"> Gestione Razze</div>
        <div class="subtitle">Gestione delle razze presenti</div>

        Da questa pagina e' possibile:
        <ul>
            <li>Creare una nuova razza</li>
            <li>Modificare una razza</li>
            <li>Eliminare una razza</li>
        </ul>

    </div>

    <div class="form_container manage_races_container">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/razze/gestione_razze_ajax.php" data-callback="updateRaces">

            <div class="form_title">Crea Razza</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Immagine</div>
                <input type="text" name="immagine">
            </div>

            <div class="single_input">
                <div class="label">Singolare Maschile</div>
                <input type="text" name="sing_m">
            </div>

            <div class="single_input">
                <div class="label">Singolare Femminile</div>
                <input type="text" name="sing_f">
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <div class="label">Immagine grande</div>
                <input type="text" name="immagine">
            </div>

            <div class="single_input">
                <div class="label">Icona</div>
                <input type="text" name="icon">
            </div>

            <div class="single_input">
                <div class="label">Sito razza</div>
                <input type="text" name="url_site">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Visibile</div>
                <input type="checkbox" name="visibile">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Iscrizione</div>
                <input type="checkbox" name="iscrizione">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form edit_form ajax_form" action="gestione/razze/gestione_razze_ajax.php"
              data-callback="updateRaces">

            <div class="form_title">Modifica razza</div>

            <div class="single_input">
                <div class="label">Razze</div>
                <select name="id" required>
                    <?= $cls->listRaces(); ?>
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
                <div class="label">Singolare Maschile</div>
                <input type="text" name="sing_m">
            </div>

            <div class="single_input">
                <div class="label">Singolare Femminile</div>
                <input type="text" name="sing_f">
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <div class="label">Immagine grande</div>
                <input type="text" name="immagine">
            </div>

            <div class="single_input">
                <div class="label">Icona</div>
                <input type="text" name="icon">
            </div>

            <div class="single_input">
                <div class="label">Sito razza</div>
                <input type="text" name="url_site">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Visibile</div>
                <input type="checkbox" name="visibile">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Iscrizione</div>
                <input type="checkbox" name="iscrizione">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form class="form ajax_form" action="gestione/razze/gestione_razze_ajax.php" data-callback="updateRaces">

            <div class="form_title">Elimina razza</div>

            <div class="single_input">
                <div class="label">Razze</div>
                <select name="id" required>
                    <?= $cls->listRaces(); ?>
                </select>
            </div>


            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/razze/gestione_razze.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>


<?php } ?>
