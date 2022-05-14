<?php /*Form di inserimento/modifica*/

Router::loadRequired();

$quest = Quest::getInstance();

if ($quest->manageQuestPermission()) {

    ?>
    <div class="panels_box form_container">

        <form class="form quest_insert_form ajax_form"
              action="gestione/quest/gestione_quest_ajax.php">

            <div class="form_title">
                Inserisci quest
            </div>

            <div class="single_input">
                <div class='label'>
                    Titolo
                </div>
                <input name="titolo"/>
            </div>

            <div class="single_input">
                <div class='label'>
                    Descrizione
                </div>
                <textarea name="descrizione"></textarea>
            </div>

            <?php if ($quest->trameEnabled() && $quest->manageTramePermission()) { ?>
                <div class="single_input">
                    <div class='label'>
                        Trama di riferimento
                    </div>
                    <select name="trama">
                        <option value="0">Nessuno</option>
                        <?= $quest->getTrameList(); ?>
                    </select>
                </div>
            <?php } ?>

            <div class="form_title"> Partecipanti</div>

            <div class="partecipanti_box">

            </div>

            <div class='single_input'>
                <button id="new_member">Aggiungi un nuovo partecipante</button>
            </div>


            <!-- bottoni -->
            <div class='single_input'>
                <input type="submit" value="<?= Filters::out($MESSAGE['interface']['forms']['submit']); ?>"/>
                <input type="hidden" name="action" value="insert_quest">
            </div>

        </form>
    </div>

    <script src="<?=Router::getPagesLink('gestione/quest/JS/gestione_quest_insert.js');?>"></script>

<?php } ?>

<!-- Link di ritorno alla visualizzazione di base -->
<div class="link_back">
    <a href="main.php?page=gestione_quest">
        Torna a gestione quest
    </a>
</div>
