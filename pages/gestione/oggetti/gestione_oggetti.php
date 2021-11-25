<?php

require_once(__DIR__.'/../../../includes/required.php'); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Oggetti::getInstance(); # Inizializzo classe

if($cls->permissionManageObjects()){ # Metodo di controllo per accesso alla pagina di gestione


    if(isset($_POST['op'])){ # Se ho richiesto un'operazione
        switch ($_POST['op']){ # In base al tipo di operazione eseguo insert/edit/delete/altro
            case 'op_insert':
                $resp = $cls->insertObject($_POST);
                break;
            case 'op_edit':
                $resp = $cls->editObject($_POST);
                break;
            case 'op_delete':
                $resp = $cls->deleteObject($_POST);
                break;
        }
    }


?>

        <div class="general_incipit">
            <div class="title"> Gestione oggetti </div>
            <div class="subtitle"> Sezione per la gestione degli oggetti</div> <br>


            Da questa pagina e' possibile:
            <ul>
                <li>Creare un oggetto</li>
                <li>Modificare un oggetto</li>
                <li>Eliminare un oggetto</li>
            </ul>

        </div>


    <div class="form_container">

        <?php if(isset($resp)) { # Se ho inviato il form e ricevuto una risposta ?>

            <div class="warning"><?=$resp['mex'];?></div>

        <?php
            Functions::redirect('/main.php?page=gestione_oggetti',3); # Redirect alla stessa pagina, per evitare il re-submit di un form
        } ?>

        <!-- INSERT -->
        <form method="POST" class="form">

            <div class="form_title">Creazione oggetto</div>

            <!-- TIPO -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Tipo</div>
                <select name="tipo">
                    <option value=""></option>
                    <?=$cls->listObjectTypes();?>
                </select>
            </div>

            <!-- NOME -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <!-- Descrizione -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <!-- Immagine -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Immagine</div>
                <input type="text" name="immagine">
            </div>

            <!-- Indossabile -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Indossabile</div>
                <input type="checkbox" name="indossabile">
            </div>

            <!-- Costo -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Costo</div>
                <input type="number" name="costo">
            </div>

            <!-- Cariche -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Cariche</div>
                <input type="number" name="cariche">
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form edit-form">

            <div class="form_title">Modifica oggetto</div>


            <!-- LISTA OGGETTI -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Oggetti</div>
                <select name="oggetto">
                    <option value=""></option>
                    <?=$cls->listObjects();?>
                </select>
            </div>

            <!-- TIPO -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Tipo</div>
                <select name="tipo">
                    <option value=""></option>
                    <?=$cls->listObjectTypes();?>
                </select>
            </div>

            <!-- NOME -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <!-- Descrizione -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <!-- Immagine -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Immagine</div>
                <input type="text" name="immagine">
            </div>

            <!-- Indossabile -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Indossabile</div>
                <input type="checkbox" name="indossabile">
            </div>

            <!-- Costo -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Costo</div>
                <input type="number" name="costo">
            </div>

            <!-- Cariche -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Cariche</div>
                <input type="number" name="cariche">
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title">Elimina Oggetto</div>

            <!-- LISTA OGGETTI -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Oggetti</div>
                <select name="oggetto">
                    <option value=""></option>
                    <?=$cls->listObjects();?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

    </div>

    <script src="pages/gestione/oggetti/gestione_oggetti.js"></script>


<?php } ?>
