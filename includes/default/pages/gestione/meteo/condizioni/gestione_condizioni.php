<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = MeteoCondizioni::getInstance(); # Inizializzo classe

if ($cls->permissionManageWeatherConditions()) { # Metodo di controllo per accesso alla pagina di gestione
    ?>

    <div class="general_incipit">
        <div class="title"> Gestione Condizioni Meteo</div>
        <br>
        <div class="subtitle">Gestione delle condizioni del meteo</div>
        <br>
        Da questa pagina è possibile:
        <ul>
            <li>Creare una nuova condizione meteo</li>
            <li>Modificare una condizione meteo</li>
            <li>Rimuovere una condizione meteo</li>
        </ul>
    </div>


    <div class="form_container gestione_meteo_condizioni">

        <!-- INSERT -->
        <form method="POST" class="form">

            <div class="form_title">Aggiunta condizione meteo</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>

            <div class="single_input">
                <div class="label">Venti</div>
                <select data-placeholder="Opzioni per il vento" multiple class="chosen-select" name="vento[]" id="vento" required>
                    <?=MeteoVenti::getInstance()->listWinds();?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Immagine</div>
                <input type="text" name="immagine" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form edit-form">

            <div class="form_title">Modifica condizione meteo</div>

            <div class="single_input">
                <div class="label">Condizione</div>
                <select name="id" required>
                    <?=$cls->listConditions();?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>

            <div class="single_input">
                <div class="label">Venti</div>
                <select data-placeholder="Opzioni per il vento" multiple class="chosen-select" name="vento[]" id="vento" required>
                    <?=MeteoVenti::getInstance()->listWinds();?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Immagine</div>
                <input type="text" name="immagine" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title">Elimina condizione meteo</div>

            <div class="single_input">
                <div class="label">Condizione</div>
                <select name="id" required>
                    <?= $cls->listConditions(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>


        <div class="link_back">
            <a href="/main.php?page=gestione">Torna indietro</a>
        </div>
    </div>

    <script src="<?=Router::getPagesLink('gestione/meteo/condizioni/gestione_condizioni.js');?>"></script>


<?php } ?>
