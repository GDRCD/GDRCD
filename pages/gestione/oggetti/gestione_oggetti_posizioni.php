<?php

require_once(__DIR__ . '/../../../includes/required.php'); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Oggetti::getInstance(); # Inizializzo classe

if ($cls->permissionManageObjectsType()) { # Metodo di controllo per accesso alla pagina di gestione


    if (isset($_POST['op'])) { # Se ho richiesto un'operazione
        switch ($_POST['op']) { # In base al tipo di operazione eseguo insert/edit/delete/altro
            case 'op_insert':
                $resp = $cls->insertObjectPosition($_POST);
                break;
            case 'op_edit':
                $resp = $cls->editObjectPosition($_POST);
                break;
            case 'op_delete':
                $resp = $cls->deleteObjectPosition($_POST);
                break;
        }
    }


    ?>

    <div class="general_incipit">
        <div class="title"> Gestione Posizioni oggetti</div>
        <div class="subtitle"> Sezione per la gestione delle posizioni degli oggetti</div>
        <br><br>

        Da questa pagina e' possibile:
        <ul>
            <li>Creare una posizione oggetto</li>
            <li>Modificare una posizione oggetto</li>
            <li>Eliminare una posizione oggetto</li>
        </ul>


        <br><br>

        Ogni posizione contiene i seguenti dati:
        <ul>
            <li>Nome posizione</li>
            <li>Immagine di default, per oggetti mancanti in posizione. Il percorso di default e' <span
                        class="highlight"> "themes/advanced/imgs/body"</span></li>
            <li>Numero di oggetti indossabili per ogni posizione</li>
        </ul>

    </div>


    <div class="form_container">

        <?php if (isset($resp)) { # Se ho inviato il form e ricevuto una risposta ?>

            <div class="warning"><?= $resp['mex']; ?></div>

            <div class="link_back">
                <a href="/main.php?page=gestione_oggetti_posizioni">
                    Indietro
                </a>
            </div>

            <?php
            Functions::redirect('/main.php?page=gestione_oggetti_posizioni', 3); # Redirect alla stessa pagina, per evitare il re-submit di un form
        } ?>

        <!-- INSERT -->
        <form method="POST" class="form">

            <div class="form_title">Creazione posizione oggetto</div>

            <!-- NOME -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <!-- Immagine -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Immagine</div>
                <input type="text" name="immagine">
            </div>

            <!-- Immagine -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Numero oggetti indossabili</div>
                <input type="number" name="numero">
            </div>


            <div class="single_input">
                <input type="hidden" name="op" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form edit-form">

            <div class="form_title">Modifica tipologia oggetto</div>

            <!-- POSIZIONE -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Posizione</div>
                <select name="id">
                    <option value=""></option>
                    <?= $cls->listObjectPositions(); ?>
                </select>
            </div>

            <!-- NOME -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <!-- Immagine -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Immagine</div>
                <input type="text" name="immagine">
            </div>

            <!-- Immagine -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Numero oggetti indossabili</div>
                <input type="number" name="numero">
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title">Elimina tipologia Oggetto</div>


            <!-- POSIZIONE -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Posizione</div>
                <select name="id">
                    <option value=""></option>
                    <?= $cls->listObjectPositions(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

    </div>

    <script src="pages/gestione/oggetti/gestione_oggetti_posizioni.js"></script>


<?php } ?>
