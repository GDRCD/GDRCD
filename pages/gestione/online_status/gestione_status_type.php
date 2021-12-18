<?php

require_once(__DIR__.'/../../../includes/required.php'); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = OnlineStatus::getInstance(); # Inizializzo classe

if($cls->manageStatusPermission()){ # Metodo di controllo per accesso alla pagina di gestione


    if(isset($_POST['op'])){ # Se ho richiesto un'operazione
        switch ($_POST['op']){ # In base al tipo di operazione eseguo insert/edit/delete/altro
            case 'op_insert':
                $resp = $cls->insertStatusType($_POST);
                break;
            case 'op_edit':
                $resp = $cls->editStatusType($_POST);
                break;
            case 'op_delete':
                $resp = $cls->deleteStatusType($_POST);
                break;
        }
    }


    ?>

    <div class="general_incipit">
        <div class="title"> Gestione dei tipi di status online </div><br>
        La pagina serve per gestire le categorie di voci degli status online.
        <br><br>
        <div class="subtitle">Risposte disponibili</div>

        E' possibile aggiungere/modificare/eliminare le risposte dall'apposito pannello.

    </div>


    <div class="form_container">

        <?php if(isset($resp)) { # Se ho inviato il form e ricevuto una risposta ?>

            <div class="warning"><?=$resp['mex'];?></div>

            <?php
            Functions::redirect('/main.php?page=gestione_tipo_stato_online',3); # Redirect alla stessa pagina, per evitare il re-submit di un form
        } ?>

        <!-- INSERT -->
        <form method="POST" class="form">

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
                <input type="hidden" name="op" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Inserisci">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form edit-form">

            <div class="form_title">Modifica Stato Esistente</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Tipo di stato da modificare</div>
                <select name="id" required>
                    <option value=""></option>
                    <?=$cls->renderStatusTypeList();?>
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
                <input type="hidden" name="op" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title">Elimina un tipo di stato</div>
            <div class="form_info"> Eliminare una tipologia di stato eliminera' anche tutte le risposte ad essa associata.</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Tipo di stato da eliminare</div>
                <select name="id" required>
                    <option value=""></option>
                    <?=$cls->renderStatusTypeList();?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="pages/gestione/online_status/gestione_status_type.js"></script>


<?php } ?>