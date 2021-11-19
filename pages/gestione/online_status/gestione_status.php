<?php

require_once(__DIR__.'/../../../includes/required.php'); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = OnlineStatus::getInstance(); # Inizializzo classe

if($cls->manageStatusPermission()){ # Metodo di controllo per accesso alla pagina di gestione


    if(isset($_POST['op'])){ # Se ho richiesto un'operazione
        switch ($_POST['op']){ # In base al tipo di operazione eseguo insert/edit/delete/altro
            case 'op_insert':
                $resp = $cls->insertStatus($_POST);
                break;
            case 'op_edit':
                $resp = $cls->editStatus($_POST);
                break;
            case 'op_delete':
                $resp = $cls->deleteStatus($_POST);
                break;
        }
    }


    ?>

    <div class="general_incipit">
        <div class="title"> Gestione delle risposte degli status online </div><br>
        La pagina serve per gestire le risposte presenti nel popup di scelta dello stato online.
        <br><br>
        <div class="subtitle">Tipi di risposte disponibili</div>

        E' possibile aggiungere/modificare/eliminare le tipologie di risposta dall'apposito pannello.

    </div>


    <div class="form_container">

        <?php if(isset($resp)) { # Se ho inviato il form e ricevuto una risposta ?>

            <div class="warning"><?=$resp['mex'];?></div>

            <?php
            Functions::redirect('/main.php?page=gestione_stato_online',3); # Redirect alla stessa pagina, per evitare il re-submit di un form
        } ?>

        <!-- INSERT -->
        <form method="POST" class="form">

            <div class="form_title">Inserisci un nuovo stato</div>

            <div class="single_input">
                <div class="label">Tipo</div>
                <select name="type" required>
                    <option value=""></option>
                    <?=$cls->renderStatusTypeList(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Testo</div>
                <input type="text" name="text" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Inserisci">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form edit-form">

            <div class="form_title">Modifica Stato Esistente</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Stato da modificare</div>
                <select name="id" required>
                    <option value=""></option>
                    <?=$cls->renderStatusList();?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Tipo</div>
                <select name="type" required>
                    <option value=""></option>
                    <?=$cls->renderStatusTypeList(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Testo</div>
                <input type="text" name="text" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title">Elimina uno stato</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Stato da eliminare</div>
                <select name="id" required>
                    <option value=""></option>
                    <?=$cls->renderStatusList();?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="pages/gestione/online_status/gestione_status.js"></script>


<?php } ?>
