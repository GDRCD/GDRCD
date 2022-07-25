<?php

Router::loadRequired();

$gruppi = GruppiStipendiExtra::getInstance();

if ( $gruppi->activeExtraEarn() ) {
    ?>

    <div class="group_extra_earn_container">
        <div class="general_title">Aggiunta Stipendio Extra</div>

        <form class="ajax_form" action="servizi/amministrazioneGilde/ajax.php" data-callback="updateGroupEarns">

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Gruppo</div>
                <select name="gruppo" required>
                    <?= Gruppi::getInstance()->listAvailableGroups(); ?>
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
                <input type="hidden" name="action" value="new_extra_earn"> <!-- OP NEEDED -->
                <input type="submit" value="Assegna">
            </div>

        </form>

        <div class="general_title">Modifica Stipendio Extra</div>

        <form class="ajax_form edit_form" action="servizi/amministrazioneGilde/ajax.php"
              data-callback="updateGroupEarns">


            <div class="single_input">
                <div class="label">Stipendi extra</div>
                <select name="id" id="id" required>
                    <?= GruppiStipendiExtra::getInstance()->listAvailableExtraEarns(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Gruppo</div>
                <select name="gruppo" required>
                    <?= Gruppi::getInstance()->listAvailableGroups(); ?>
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
                <input type="hidden" name="action" value="mod_extra_earn"> <!-- OP NEEDED -->
                <input type="submit" value="Assegna">
            </div>

        </form>

        <div class="general_title">Rimuovi Stipendio Extra</div>

        <form class="ajax_form" action="servizi/amministrazioneGilde/ajax.php" data-callback="updateGroupEarns">

            <div class="single_input">
                <div class="label">Stipendi extra</div>
                <select name="id" id="id" required>
                    <?= GruppiStipendiExtra::getInstance()->listAvailableExtraEarns(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="remove_extra_earn"> <!-- OP NEEDED -->
                <input type="submit" value="Rimuovi">
            </div>

        </form>

        <script src="<?= Router::getPagesLink('servizi/amministrazioneGilde/view_extra_earn.js'); ?>"></script>

        <div class="link_back">
            <a href="main.php?page=servizi/amministrazioneGilde/index">Torna indietro</a>
        </div>
    </div>
<?php } ?>