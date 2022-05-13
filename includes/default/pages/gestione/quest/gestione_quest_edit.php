<?php /*Form di inserimento/modifica*/

require_once(__DIR__ . '/../../../core/required.php');

$quest = Quest::getInstance();


if ($quest->manageQuestPermission()) {

    $quest_id = Filters::int($_GET['id_record']);

    if ($quest->questExist($quest_id)) {

        $loaded_record = $quest->getQuest($quest_id); ?>

        <!-- Form di inserimento/modifica -->
        <div class="panels_box form_container">

            <form method="post" class="form quest_edit_form">

                <div class="form_title">
                    Modifica quest
                </div>

                <div class="single_input">
                    <div class='label'>
                        Titolo
                    </div>
                    <input name="titolo" value="<?= Filters::out($loaded_record['titolo']); ?>"/>
                </div>

                <div class="single_input">
                    <div class='label'>
                        Descrizione
                    </div>
                    <textarea name="descrizione"><?= Filters::out($loaded_record['descrizione']); ?></textarea>
                </div>

                <?php if ($quest->trameEnabled() && $quest->manageTramePermission()) { ?>
                    <div class="single_input">
                        <div class='label'>
                            Trama di riferimento
                        </div>
                        <select name="trama">
                            <option value="0">Nessuno</option>
                            <?= $quest->getTrameList(Filters::int($loaded_record['trama'])); ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="form_title"> Partecipanti</div>

                <?= $quest->getQuestMembersList($quest_id); ?>

                <div class="partecipanti_box">

                </div>

                <div class='single_input'>
                    <button id="new_member">Aggiungi un nuovo partecipante</button>
                </div>

                <!-- bottoni -->
                <div class='single_input'>
                    <input type="hidden" name="quest" value="<?= $quest_id; ?>">
                    <input type="submit" value="<?= Filters::out($MESSAGE['interface']['forms']['modify']); ?>"/>
                    <input type="hidden" name="action" value="edit_quest">
                </div>

            </form>
        </div>

        <script src="pages/gestione/quest/JS/gestione_quest_edit.js"></script>

        <!-- Link di ritorno alla visualizzazione di base -->
    <?php } else { ?>
        <div class="warning">Quest inesistente.</div>
    <?php }
} else { ?>
    <div class="warning"> Permessi negati.</div>
<?php } ?>

<div class="link_back">
    <a href="main.php?page=gestione_quest">
        Torna a gestione quest
    </a>
</div>
