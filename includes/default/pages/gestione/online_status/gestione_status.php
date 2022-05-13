<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = OnlineStatus::getInstance(); # Inizializzo classe

if($cls->manageStatusPermission()){ # Metodo di controllo per accesso alla pagina di gestione

    ?>

    <div class="general_incipit">
        <div class="title"> Gestione delle risposte degli status online </div><br>
        La pagina serve per gestire le risposte presenti nel popup di scelta dello stato online.
        <br><br>
        <div class="subtitle">Tipi di risposte disponibili</div>

        E' possibile aggiungere/modificare/eliminare le tipologie di risposta dall'apposito pannello.

    </div>


    <div class="form_container gestione_status">

        <!-- INSERT -->
        <form method="POST" class="form">

            <div class="form_title">Inserisci un nuovo stato</div>

            <div class="single_input">
                <div class="label">Tipo</div>
                <select name="type" required>
                    <?=$cls->renderStatusTypeList(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Testo</div>
                <input type="text" name="text" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert_status"> <!-- OP NEEDED -->
                <input type="submit" value="Inserisci">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form edit-form">

            <div class="form_title">Modifica Stato Esistente</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Stato da modificare</div>
                <select name="id" required>
                    <?=$cls->renderStatusList();?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Tipo</div>
                <select name="type" required>
                    <?=$cls->renderStatusTypeList(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Testo</div>
                <input type="text" name="text" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit_status"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title">Elimina uno stato</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Stato da eliminare</div>
                <select name="id" required>
                    <?=$cls->renderStatusList();?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete_status"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="pages/gestione/online_status/gestione_status.js"></script>


<?php } ?>
