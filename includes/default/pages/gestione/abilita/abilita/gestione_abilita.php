<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Abilita::getInstance(); # Inizializzo classe

if ( $cls->permissionManageAbility() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>
    <div class="general_incipit">
        <div class="title"> Gestione Abilità</div>
        <div class="subtitle">Gestione delle abilità</div>
        <br>
        Da questa pagina è possibile:
        <ul>
            <li>Creare un'abilità</li>
            <li>Modificare un'abilità</li>
            <li>Eliminare un'abilità</li>
        </ul>
        <br>
        Eliminare un'abilita' eliminera' la stessa anche da <span class="highlight">TUTTI i personaggi che la possiedono.</span>
    </div>

    <div class="form_container manage_ability_container">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/abilita/abilita/gestione_abilita_ajax.php"
              data-callback="updateAbilities">

            <div class="form_title">Crea abilità</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <div class="label">Statistica</div>
                <select name="statistica">
                    <?= Statistiche::getInstance()->listStats(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Razza</div>
                <select name="razza">
                    <?= Razze::getInstance()->listRaces(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form ajax_form edit_form" action="gestione/abilita/abilita/gestione_abilita_ajax.php"
              data-callback="updateAbilities">

            <div class="form_title">Modifica abilità</div>

            <div class="single_input">
                <div class="label">Abilità</div>
                <select name="id">
                    <?= $cls->listAbility() ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <div class="label">Statistica</div>
                <select name="statistica">
                    <?= Statistiche::getInstance()->listStats(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Razza</div>
                <select name="razza">
                    <?= Razze::getInstance()->listRaces(); ?>
                </select>
            </div>


            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form class="form ajax_form" action="gestione/abilita/abilita/gestione_abilita_ajax.php"
              data-callback="updateAbilities">

            <div class="form_title">Elimina abilità</div>

            <div class="single_input">
                <div class="label">Abilità</div>
                <select name="id">
                    <?= $cls->listAbility() ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/abilita/abilita/gestione_abilita.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>


<?php } ?>
