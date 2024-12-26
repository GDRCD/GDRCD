<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = PersonaggioEsperienza::getInstance(); # Inizializzo classe


if ( $cls->permissionManageExp() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>

    <div class="general_incipit">
        <div class="title"> Assegnazione esperienza</div>
        <div class="subtitle"> Sezione per l'assegnazione esperienza</div>
        <br>


        Da questa pagina è possibile:
        <ul>
            <li>Assegnare esperienza</li>
        </ul>

    </div>


    <div class="form_container gestione_oggetti_assegna">

        <!-- INSERT -->
        <form class="form ajax_form"
              action="gestione/esperienza/assegna/gestione_exp_assegna_ajax.php"
              data-callback="refreshObjectList">

            <div class="form_title">Assegna EXP</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Esperienza</div>
                <input type="number" name="exp" min="0" required>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Personaggio</div>
                <select name="personaggio">
                    <?= Personaggio::getInstance()->listPgs(); ?>
                </select>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Causale</div>
                <input type="text" name="causale" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_assign"> <!-- OP NEEDED -->
                <input type="submit" value="Assegna">
            </div>

        </form>


    </div>

    <script src="<?= Router::getPagesLink('gestione/esperienza/assegna/gestione_exp_assegna.js'); ?>"></script>

    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>

<?php } else { ?>
    <div class="error_message">
        <p>Non hai i permessi necessari per accedere a questa pagina.</p>
    </div>
<?php } ?>
