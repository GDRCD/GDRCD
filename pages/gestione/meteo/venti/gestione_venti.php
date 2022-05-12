<?php

require_once(__DIR__ . '/../../../../includes/required.php'); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = MeteoVenti::getInstance(); # Inizializzo classe

if ($cls->permissionManageWeather()) { # Metodo di controllo per accesso alla pagina di gestione
    ?>

    <div class="general_incipit">
        <div class="title"> Gestione Meteo Venti</div>
        <br>
        <div class="subtitle">Gestione dei venti del meteo</div>
        <br>
        Da questa pagina è possibile:
        <ul>
            <li>Creare un nuovo tipo di vento</li>
            <li>Modificare un tipo di vento</li>
            <li>Rimuovere un tipo di vento</li>
        </ul>
    </div>


    <div class="form_container gestione_meteo_venti">

        <!-- INSERT -->
        <form method="POST" class="form">

            <div class="form_title">Aggiunta vento</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form edit-form">

            <div class="form_title">Modifica vento</div>

            <div class="single_input">
                <div class="label">Venti</div>
                <select name="id" required>
                    <?=$cls->listWinds();?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title">Elimina vento</div>

            <div class="single_input">
                <div class="label">Vento</div>
                <select name="id" required>
                    <?= $cls->listWinds(); ?>
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

    <script src="/pages/gestione/meteo/venti/gestione_venti.js"></script>


<?php } ?>
