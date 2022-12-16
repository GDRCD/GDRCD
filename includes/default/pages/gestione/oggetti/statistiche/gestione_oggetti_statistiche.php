<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = OggettiStatistiche::getInstance(); # Inizializzo classe

if ( $cls->permissionManageObjectsStats() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>

    <div class="general_incipit">
        <div class="title"> Gestione statistiche oggetti</div>
        <div class="subtitle"> Sezione per la gestione delle statistiche degli oggetti</div>
        <br>

        Da questa pagina è possibile:
        <ul>
            <li>Associare una statistica a un oggetto</li>
            <li>Modificare una statistica assegnata a un oggetto</li>
            <li>Eliminare un'associazione di una statistica a un oggetto</li>
        </ul>

    </div>


    <div class="form_container gestione_oggetti_statistiche">

        <!-- INSERT -->
        <form class="form ajax_form"
              action="gestione/oggetti/statistiche/gestione_oggetti_statistiche_ajax.php">

            <div class="form_title">Creazione statistica oggetto</div>

            <div class="single_input">
                <div class="label">Oggetto</div>
                <select name="oggetto" required>
                    <?= Oggetti::getInstance()->listObjects(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Statistica</div>
                <select name="stat" required>
                    <?= Statistiche::getInstance()->listStats(); ?>
                </select>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Valore</div>
                <input type="number" name="valore" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form ajax_form edit-form"
              action="gestione/oggetti/statistiche/gestione_oggetti_statistiche_ajax.php">

            <div class="form_title">Modifica Statistica oggetto</div>

            <div class="single_input">
                <div class="label">Oggetto</div>
                <select name="oggetto" required>
                    <?= Oggetti::getInstance()->listObjects(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Statistica</div>
                <select name="stat" required>
                    <?= Statistiche::getInstance()->listStats(); ?>
                </select>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Valore</div>
                <input type="number" name="valore" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form class="form ajax_form" action="gestione/oggetti/statistiche/gestione_oggetti_statistiche_ajax.php">

            <div class="form_title">Elimina statistica Oggetto</div>

            <!-- LISTA OGGETTI -->
            <div class="single_input">
                <div class="label">Oggetto</div>
                <select name="oggetto" required>
                    <?= Oggetti::getInstance()->listObjects(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Statistica</div>
                <select name="stat" required>
                    <?= Statistiche::getInstance()->listStats(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/oggetti/statistiche/gestione_oggetti_statistiche.js'); ?>"></script>

    <div class="link_back">
        <a href="/main.php?page=gestione">Indietro</a>
    </div>

<?php } ?>
