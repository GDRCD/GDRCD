<?php

require_once(__DIR__.'/../../includes/required.php'); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Example::getInstance(); # Inizializzo classe

if($cls->managePermission()){ # Metodo di controllo per accesso alla pagina di gestione


    if(isset($_POST['op'])){ # Se ho richiesto un'operazione
        switch ($_POST['op']){ # In base al tipo di operazione eseguo insert/edit/delete/altro
            case 'op_insert':
                $resp = $cls->insertOp($_POST);
                break;
            case 'op_edit':
                $resp = $cls->editOp($_POST);
                break;
            case 'op_delete':
                $resp = $cls->deleteOp($_POST);
                break;
            case 'op_other':
                $resp = $cls->altroOp($_POST);
                break;
        }
    }


?>

        <div class="general_incipit">
            <div class="title"> Un titolo </div>
            <div class="subtitle">Un sottotitolo</div>

            Testo testo <span class="highlight"> Un testo che si nota nel testo </span> test test

            <ul>
                <li>Elemento 1</li>
                <li>Elemento 2</li>
                <li>Elemento 3</li>
                <li>Elemento 4</li>
            </ul>

        </div>


    <div class="form_container">

        <?php if(isset($resp)) { # Se ho inviato il form e ricevuto una risposta ?>

            <div class="warning"><?=$resp['mex'];?></div>

        <?php
            Functions::redirect('/main.php?page=pagina_gestione_example'); # Redirect alla stessa pagina, per evitare il re-submit di un form
        } ?>

        <!-- INSERT -->
        <form method="POST" class="form">

            <div class="form_title"><!-- TITOLO SEZIONE --></div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label"></div>
                <!-- INPUT OR SELECT OR TEXTAREA -->
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_insert"> <!-- OP NEEDED -->
                <input type="hidden" name="example" value="ex_value"> <!-- EXAMPLE OF OTHER HIDDEN DATA -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form">

            <div class="form_title"><!-- TITOLO SEZIONE --></div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label"></div>
                <!-- INPUT OR SELECT OR TEXTAREA -->
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_edit"> <!-- OP NEEDED -->
                <input type="hidden" name="example" value="ex_value"> <!-- EXAMPLE OF OTHER HIDDEN DATA -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title"><!-- TITOLO SEZIONE --></div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label"></div>
                <!-- INPUT OR SELECT OR TEXTAREA -->
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_delete"> <!-- OP NEEDED -->
                <input type="hidden" name="example" value="ex_value"> <!-- EXAMPLE OF OTHER HIDDEN DATA -->
                <input type="submit" value="Invia">
            </div>

        </form>

    </div>


<?php } ?>
