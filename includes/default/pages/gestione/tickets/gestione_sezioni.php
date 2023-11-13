<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = TicketsSections::getInstance(); # Inizializzo classe

if ( $cls->manageSectionsPermission() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>

    <div class="gestione_pagina">

        <div class="gestione_incipit">
            <div class="title"> Gestione delle sezioni dei ticket</div>
            <br>
            La pagina serve per gestire le sezioni presenti in cui un ticket si puo' aprire
            <br><br>
            <u>L'attivazione dei ticket avviene tramite il pannello di gestione: Gestione->Ticket</u>
            <br><br>

            <div class="title">Le informazioni attualmente modificabili sono:</div>
            <br>
            <ul>
                <li>
                    <div class="subtitle_gst">Titolo e descrizione</div>
                </li>
                <li>
                    <div class="subtitle_gst">Sezione padre</div>
                    Se selezionata, la sezione verrà inserita come sotto-sezione della sezione selezionata
                </li>
                <li>
                    <div class="subtitle_gst">Creabile</div>
                    Se selezionato, la sezione potrà essere selezionata per la creazione di un ticket
                </li>
            </ul>
            <br>

            <div class="highlight">Se si elimina una sezione, tutte le sezioni che si riferivano a lei come "sezione_padre" verranno impostate come senza padre</div>
        </div>


        <div class="form_container gestione_sezioni">

            <!-- INSERT -->
            <form class="form ajax_form"
                  action="gestione/tickets/gestione_sezioni_ajax.php"
                  data-callback="refreshSectionsList">

                <div class="form_title">Inserisci sezione ticket</div>

                <div class="single_input">
                    <div class="label">Titolo</div>
                    <input type="text" name="titolo" required>
                </div>

                <div class='single_input'>
                    <div class='label'>Descrizione</div>
                    <textarea name='descrizione'></textarea>
                </div>

                <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                    <div class="label">Sezione padre</div>
                    <select name="sezione_padre">
                        <?= $cls->listSections(); ?>
                    </select>
                </div>

                <div class="single_input">
                    <div class="label">Creabile</div>
                    <input type="checkbox" name="creabile">
                </div>


                <div class="single_input">
                    <input type="hidden" name="action" value="op_insert_section"> <!-- OP NEEDED -->
                    <input type="submit" value="Inserisci">
                </div>

            </form>

            <!-- EDIT -->
            <form class="form edit-form ajax_form"
                  action="gestione/tickets/gestione_sezioni_ajax.php"
                  data-callback="refreshSectionsList">

                <div class="form_title">Modifica sezione ticket</div>

                <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                    <div class="label">Sezione da modificare</div>
                    <select name="id" required>
                        <?= $cls->listSections(); ?>
                    </select>
                </div>

                <div class="single_input">
                    <div class="label">Titolo</div>
                    <input type="text" name="titolo" required>
                </div>

                <div class='single_input'>
                    <div class='label'>Descrizione</div>
                    <textarea name='descrizione'></textarea>
                </div>

                <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                    <div class="label">Sezione padre</div>
                    <select name="sezione_padre">
                        <?= $cls->listSections(); ?>
                    </select>
                </div>

                <div class="single_input">
                    <div class="label">Creabile</div>
                    <input type="checkbox" name="creabile">
                </div>

                <div class="single_input">
                    <input type="hidden" name="action" value="op_edit_section"> <!-- OP NEEDED -->
                    <input type="submit" value="Modifica">
                </div>

            </form>

            <!-- DELETE -->
            <form class="form ajax_form"
                  action="gestione/tickets/gestione_sezioni_ajax.php"
                  data-callback="refreshSectionsList">

                <div class="form_title">Elimina sezione ticket</div>

                <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                    <div class="label">Sezione da eliminare</div>
                    <select name="id" required>
                        <?= $cls->listSections(); ?>
                    </select>
                </div>

                <div class="single_input">
                    <input type="hidden" name="action" value="op_delete_section"> <!-- OP NEEDED -->
                    <input type="submit" value="Elimina">
                </div>

            </form>

        </div>
    </div>

    <script src="<?= Router::getPagesLink('gestione/tickets/gestione_sezioni.js'); ?>"></script>

    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>

<?php } ?>
