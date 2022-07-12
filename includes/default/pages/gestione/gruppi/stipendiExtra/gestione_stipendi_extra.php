<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = GruppiStipendiExtra::getInstance(); # Inizializzo classe

if($cls->permissionServiceGroups() && $cls->activeExtraEarn()){ # Metodo di controllo per accesso alla pagina di gestione

?>
        <div class="general_incipit">
            <div class="title"> Gestione Stipendi extra </div>
            <div class="subtitle">Gestione degli stipendi extra dai gruppi ai personaggi</div>

            Da questa pagina e' possibile:
            <ul>
                <li>Creare uno stipendio extra per un personaggio</li>
                <li>Modificare uno stipendio extra per un personaggio</li>
                <li>Eliminare uno stipendio extra per un personaggio</li>
            </ul>

        </div>

    <div class="form_container group_found_managements">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/gruppi/stipendiExtra/gestione_stipendi_extra_ajax.php" data-callback="updateFounds">

            <div class="form_title">Crea fondo gruppo</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Gruppo</div>
                <select name="gruppo" required>
                    <?= Gruppi::getInstance()->listGroups(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Personaggio</div>
                <select name="personaggio" required>
                    <?= Personaggio::getInstance()->listPgs(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Denaro</div>
                <input type="number" name="denaro" required>
            </div>

            <div class="single_input">
                <div class="label">Intervallo</div>
                <input type="number" name="interval" required>
            </div>

            <div class="single_input">
                <div class="label">Intervallo Tipo</div>
                <select name="interval_type" required>
                    <?= GruppiFondi::getInstance()->listIntervalsTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Data inizio</div>
                <input type="datetime-local" name="last_exec" required>
            </div>


            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form edit_form ajax_form" action="gestione/gruppi/stipendiExtra/gestione_stipendi_extra_ajax.php" data-callback="updateFounds">

            <div class="form_title">Modifica fondo gruppo</div>

            <div class="single_input">
                <div class="label">Stipendi Extra</div>
                <select name="id" required>
                    <?= $cls->listExtraEarns(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Gruppo</div>
                <select name="gruppo" required>
                    <?= Gruppi::getInstance()->listGroups(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Personaggio</div>
                <select name="personaggio" required>
                    <?= Personaggio::getInstance()->listPgs(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Denaro</div>
                <input type="number" name="denaro" required>
            </div>

            <div class="single_input">
                <div class="label">Intervallo</div>
                <input type="number" name="interval" required>
            </div>

            <div class="single_input">
                <div class="label">Intervallo Tipo</div>
                <select name="interval_type" required>
                    <?= GruppiFondi::getInstance()->listIntervalsTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form ajax_form" action="gestione/gruppi/stipendiExtra/gestione_stipendi_extra_ajax.php" data-callback="updateFounds">

            <div class="form_title">Elimina fondo gruppo</div>

            <div class="single_input">
                <div class="label">Stipendi Extra</div>
                <select name="id" required>
                    <?= $cls->listExtraEarns(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/gruppi/fondi/gestione_fondi.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a> </div>


<?php } ?>
