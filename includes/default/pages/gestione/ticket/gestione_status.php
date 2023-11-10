<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = TicketStatus::getInstance(); # Inizializzo classe

if ( $cls->manageStatusPermission() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>

    <div class="gestione_pagina">

        <div class="gestione_incipit">
            <div class="title"> Gestione degli stati dei ticket</div>
            <br>
            La pagina serve per gestire gli stati presenti in cui un ticket si puo' trovare
            <br><br>
            <u>L'attivazione dei ticket avviene tramite il pannello di gestione: Gestione->Ticket</u>
            <br><br>

            <div class="title">Le informazioni attualmente modificabili sono:</div><br>
            <ul>
                <li>
                    <div class="subtitle_gst">Titolo e descrizione</div>
                </li>
                <li>
                    <div class="subtitle_gst">Colore label</div>
                    Il colore della label che viene visualizzata nella lista dei ticket
                </li>
                <li>
                    <div class="subtitle_gst">Stato iniziale</div>
                    Se selezionato, il ticket verrà creato con questo stato
                </li>
                <li>
                    <div class="subtitle_gst">Utente bloccato</div>
                    Se selezionato, l'utente non potrà rispondere al ticket quando si trova in questo stato
                </li>
            </ul>

        </div>


        <div class="form_container gestione_status">

            <!-- INSERT -->
            <form class="form ajax_form"
                  action="gestione/ticket/gestione_status_ajax.php"
                  data-callback="refreshStatusList">

                <div class="form_title">Inserisci stato ticket</div>

                <div class="single_input">
                    <div class="label">Titolo</div>
                    <input type="text" name="titolo" required>
                </div>

                <div class='single_input'>
                    <div class='label'>Descrizione</div>
                    <textarea name='descrizione'></textarea>
                </div>

                <div class="single_input">
                    <div class="label">Colore label</div>
                    <input type="text" name="colore" required>
                </div>

                <div class="single_input">
                    <div class="label">Stato iniziale</div>
                    <input type="checkbox" name="is_initial_state">
                </div>

                <div class="single_input">
                    <div class="label">Utente bloccato</div>
                    <input type="checkbox" name="is_blocked">
                </div>


                <div class="single_input">
                    <input type="hidden" name="action" value="op_insert_status"> <!-- OP NEEDED -->
                    <input type="submit" value="Inserisci">
                </div>

            </form>

            <!-- EDIT -->
            <form class="form edit-form ajax_form"
                  action="gestione/ticket/gestione_status_ajax.php"
                  data-callback="refreshStatusList">

                <div class="form_title">Modifica stato esistente</div>

                <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                    <div class="label">Stato da modificare</div>
                    <select name="id" required>
                        <?= $cls->listStatus(); ?>
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

                <div class="single_input">
                    <div class="label">Colore label</div>
                    <input type="text" name="colore" required>
                </div>

                <div class="single_input">
                    <div class="label">Stato iniziale</div>
                    <input type="checkbox" name="is_initial_state">
                </div>

                <div class="single_input">
                    <div class="label">Utente bloccato</div>
                    <input type="checkbox" name="is_blocked">
                </div>

                <div class="single_input">
                    <input type="hidden" name="action" value="op_edit_status"> <!-- OP NEEDED -->
                    <input type="submit" value="Modifica">
                </div>

            </form>

            <!-- DELETE -->
            <form class="form ajax_form"
                  action="gestione/ticket/gestione_status_ajax.php"
                  data-callback="refreshStatusList">

                <div class="form_title">Elimina stato</div>

                <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                    <div class="label">Stato da eliminare</div>
                    <select name="id" required>
                        <?= $cls->listStatus(); ?>
                    </select>
                </div>

                <div class="single_input">
                    <input type="hidden" name="action" value="op_delete_status"> <!-- OP NEEDED -->
                    <input type="submit" value="Elimina">
                </div>

            </form>

        </div>
    </div>

    <script src="<?= Router::getPagesLink('gestione/ticket/gestione_status.js'); ?>"></script>

    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>

<?php } ?>
