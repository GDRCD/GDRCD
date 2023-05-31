<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Oggetti::getInstance(); # Inizializzo classe

if ( OggettiTipo::getInstance()->permissionManageObjectsType() ) { # Metodo di controllo per accesso alla pagina di gestione
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
        <form class="form ajax_form"
              action="gestione/oggetti/posizioni/gestione_oggetti_posizioni_ajax.php"
              data-callback="refreshObjPosList">

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
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form edit-form ajax_form"
              action="gestione/oggetti/posizioni/gestione_oggetti_posizioni_ajax.php"
              data-callback="refreshObjPosList">

            <div class="form_title">Modifica tipologia oggetto</div>

            <!-- POSIZIONE -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Posizione</div>
                <select name="id">
                    <?= OggettiPosizioni::getInstance()->listObjectPositions(); ?>
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
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form class="form ajax_form"
              action="gestione/oggetti/posizioni/gestione_oggetti_posizioni_ajax.php"
              data-callback="refreshObjPosList">

            <div class="form_title">Elimina tipologia Oggetto</div>


            <!-- POSIZIONE -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Posizione</div>
                <select name="id">
                    <?= OggettiPosizioni::getInstance()->listObjectPositions(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/oggetti/posizioni/gestione_oggetti_posizioni.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>


<?php } ?>