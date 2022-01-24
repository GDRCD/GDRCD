<?php

require_once(__DIR__ . '/../../../includes/required.php'); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Oggetti::getInstance(); # Inizializzo classe

if ($cls->permissionManageObjectsType()) { # Metodo di controllo per accesso alla pagina di gestione
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


    <div class="form_container gestione_oggetti_posizione">

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
                <input type="hidden" name="action" value="op_insert_object_position"> <!-- OP NEEDED -->
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
                <input type="hidden" name="action" value="op_edit_object_position"> <!-- OP NEEDED -->
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
                    <?= $cls->listObjectPositions(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete_object_position"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

    </div>

    <script src="pages/gestione/oggetti/gestione_oggetti_posizioni.js"></script>


<?php } ?>
