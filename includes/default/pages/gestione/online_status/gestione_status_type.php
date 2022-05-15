<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = OnlineStatus::getInstance(); # Inizializzo classe

if ($cls->manageStatusPermission()) { # Metodo di controllo per accesso alla pagina di gestione
    ?>

    <div class="general_incipit">
        <div class="title"> Gestione dei tipi di status online</div>
        <br>
        La pagina serve per gestire le categorie di voci degli status online.
        <br><br>
        <div class="subtitle">Risposte disponibili</div>

        E' possibile aggiungere/modificare/eliminare le risposte dall'apposito pannello.

    </div>


    <div class="form_container gestione_status_type">

        <!-- INSERT -->
        <form class="form ajax_form"
              action="gestione/online_status/gestione_status_ajax.php"
              data-callback="refreshStatusTypeList">

            <div class="form_title">Inserisci una nuova tipologia</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="label">
            </div>

            <div class="single_input">
                <div class="label">Titolo popup</div>
                <input type="text" name="request">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert_status_type"> <!-- OP NEEDED -->
                <input type="submit" value="Inserisci">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form edit-form ajax_form"
              action="gestione/online_status/gestione_status_ajax.php"
              data-callback="refreshStatusTypeList">

            <div class="form_title">Modifica Stato Esistente</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Tipo di stato da modificare</div>
                <select name="id" required>
                    <?= $cls->renderStatusTypeList(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="label">
            </div>

            <div class="single_input">
                <div class="label">Titolo popup</div>
                <input type="text" name="request">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit_status_type"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form class="form ajax_form"
              action="gestione/online_status/gestione_status_ajax.php"
              data-callback="refreshStatusTypeList">

            <div class="form_title">Elimina un tipo di stato</div>
            <div class="form_info"> Eliminare una tipologia di stato eliminera' anche tutte le risposte ad essa
                associata.
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Tipo di stato da eliminare</div>
                <select name="id" required>
                    <?= $cls->renderStatusTypeList(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete_status_type"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/online_status/gestione_status_type.js'); ?>"></script>


<?php } ?>
