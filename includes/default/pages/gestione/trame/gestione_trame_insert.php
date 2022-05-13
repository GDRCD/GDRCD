<?php /*Form di inserimento/modifica*/


Router::loadRequired();

$quest = Quest::getInstance();

if ($quest->manageTramePermission()) {
    ?>

    <!-- Form di inserimento/modifica -->
    <div class="panels_box form_container quest_insert_form ">
        <form method="post" class="form">

            <div class="form_title">
                Inserisci trama
            </div>

            <div class="single_input">
                <div class='label'>
                    Titolo
                </div>
                <input type="text" name="titolo"/>
            </div>

            <div class="single_input">
                <div class='label'>
                    Descrizione
                </div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <div class='form_label'>
                    Stato trama
                </div>
                <div class='form_field'>
                    <select name="stato">
                        <?= $quest->trameStatusList(); ?>
                    </select>
                </div>
            </div>

            <!-- bottoni -->
            <div class='single_input'>
                <input type="submit" value="<?= Filters::out($MESSAGE['interface']['forms']['submit']); ?>"/>
                <input type="hidden" name="action" value="insert_trama">
            </div>

        </form>
    </div>

    <script src="<?=Router::getPagesLink('gestione/trame/JS/gestione_trame_insert.js');?>"></script>

<?php } else { ?>
    <div class="warning">Permesso negato</div>
<?php } ?>

<!-- Link di ritorno alla visualizzazione di base -->
<div class="link_back">
    <a href="main.php?page=gestione_trame">
        Torna alla lista delle trame
    </a>
</div>