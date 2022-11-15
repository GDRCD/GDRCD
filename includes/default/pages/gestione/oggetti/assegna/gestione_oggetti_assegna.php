<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Oggetti::getInstance(); # Inizializzo classe

if ( $cls->permissionManageObjects() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>

    <div class="general_incipit">
        <div class="title"> Assegnazione oggetti</div>
        <div class="subtitle"> Sezione per l'assegnazione degli oggetti</div>
        <br>


        Da questa pagina è possibile:
        <ul>
            <li>Assegnare un oggetto</li>
        </ul>

    </div>


    <div class="form_container gestione_oggetti_assegna">

        <!-- INSERT -->
        <form class="form ajax_form"
              action="gestione/oggetti/assegna/gestione_oggetti_assegna_ajax.php"
              data-callback="refreshObjectList">

            <div class="form_title">Assegna oggetto</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Oggetto</div>
                <select name="oggetto">
                    <?= Oggetti::getInstance()->listObjects(); ?>
                </select>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Personaggio</div>
                <select name="personaggio">
                    <?= Personaggio::getInstance()->listPgs(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_assign"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>


    </div>

    <script src="<?= Router::getPagesLink('gestione/oggetti/assegna/gestione_oggetti_assegna.js'); ?>"></script>

    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>

<?php } ?>