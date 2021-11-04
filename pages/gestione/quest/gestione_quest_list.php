<?php

require_once(__DIR__ . '/../../../includes/required.php');

$quest = Quest::getInstance();

//Determinazione pagina (paginazione)
$pagebegin = (int)$_REQUEST['offset'] * $PARAMETERS['settings']['records_per_page'];
$pageend = $PARAMETERS['settings']['records_per_page'];

# Lista delle quest visibili per questa pagina
$quests = $quest->getAllQuests($pagebegin, $pageend);

?>

<!-- link crea nuovo -->
<div class="link_back">
    <a href="main.php?page=gestione_quest&op=insert_quest">
        Registra nuova quest
    </a>
</div>


<?php

# Link per lista trame
if ($quest->trameEnabled() && $quest->viewTramePermission()) { ?>
    <div class="link_back">
        <a href="main.php?page=gestione_quest&op=lista_trame">
            Lista delle trame
        </a>
    </div>
    <?php
} ?>

<!-- Elenco dei record paginato -->
<div class="fake-table quest_list">
    <!-- Intestazione tabella -->
    <div class="tr header">
        <div class="td">
            Data
        </div>
        <div class="td">
            Titolo
        </div>
        <div class="td">
            Autore
        </div>
        <div class="td">
            Partecipanti
        </div>
        <?php if ($quest->viewTramePermission()) { ?>
            <div class="td">
                Trama
            </div>
        <?php } ?>
        <div class="td">
            Autore modifica
        </div>
        <div class="td">
            Ultima modifica
        </div>
        <div class="td">
            <?= Filters::out($MESSAGE['interface']['administration']['ops_col']); ?>
        </div>
    </div>


    <?php foreach ($quests as $row) { ?>
        <div class="tr">
            <div class="td">
                <?= Filters::date($row['data'], 'd/m/Y'); ?>
            </div>
            <div class="td">
                <?= Filters::out($row['titolo']); ?>
            </div>
            <div class="td">
                <?=  Personaggio::nameFromId(Filters::int($row['autore'])); ?>
            </div>
            <div class="td">
                <?= $quest->getPartecipantsNames($row['partecipanti']); ?>
            </div>
            <?php if ($quest->viewTramePermission()) {
                $data = $quest->getTrama(Filters::int($row['trama'])); ?>
                <div class="td">
                    <?= (!empty($data['titolo'])) ? Filters::out($data['titolo']) : 'Nessuna'; ?>
                </div>
            <?php } ?>
            <div class="td">
                <?= (!empty($data['autore_modifica'])) ? Filters::out($row['autore_modifica']) : ''; ?>
            </div>
            <div class="td">
                <?= (!empty($data['ultima_modifica'])) ? Filters::date($row['ultima_modifica'], 'd/m/Y') : ''; ?>
            </div>

            <div class="td"><!-- Iconcine dei controlli -->
                <a href="/main.php?page=gestione_quest&op=edit_quest&id_record=<?= Filters::int($row['id']); ?>">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="/main.php?page=gestione_quest&op=delete_quest&id_record=<?= Filters::int($row['id']); ?>">
                    <i class="fas fa-eraser"></i>
                </a>
            </div>
        </div>
    <?php } ?>

    <!-- Paginatore elenco -->
    <div class="pager">
        <?= $quest->getPageNumbers(Filters::int($_REQUEST['offset'])); ?>
    </div>
</div>