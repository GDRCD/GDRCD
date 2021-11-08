<?php

require_once(__DIR__ . '/../../../includes/required.php');

$quest = Quest::getInstance();

$pagebegin = (int)$_REQUEST['offset'] * $PARAMETERS['settings']['records_per_page'];
$pageend = $PARAMETERS['settings']['records_per_page'];

# Lista delle quest visibili per questa pagina
$trame = $quest->getAllTrame($pagebegin, $pageend); ?>


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
            Numero quest
        </div>
        <div class="td">
            Stato
        </div>
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

    <?php

    foreach ($trame as $trama) {

        ?>

        <div class="tr">
            <div class="td">
                <?= Filters::date($trama['data'], 'd/m/Y'); ?>
            </div>
            <div class="td">
                <?= Filters::out($trama['titolo']); ?>
            </div>
            <div class="td">
                <?= Personaggio::nameFromId(Filters::int($trama['autore'])); ?>
            </div>
            <div class="td">
                <?= $quest->getTrameQuestNums(Filters::int($trama['id'])); ?>
            </div>
            <div class="td">
                <?= $quest->getTramaStatusText(Filters::int($trama['stato'])); ?>
            </div>
            <div class="td">
                <?= (!empty($trama['autore_modifica'])) ? Filters::out($trama['autore_modifica']) : ''; ?>
            </div>
            <div class="td">
                <?= (!empty($trama['ultima_modifica'])) ? Filters::date($trama['ultima_modifica'], 'd/m/Y') : ''; ?>
            </div>

            <div class="td"><!-- Iconcine dei controlli -->
                <a href="/main.php?page=gestione_trame&op=edit_trama&id_record=<?= Filters::int($trama['id']); ?>">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="/main.php?page=gestione_trame&op=delete_trama&id_record=<?= Filters::int($trama['id']); ?>">
                    <i class="fas fa-eraser"></i>
                </a>
            </div>
        </div>

    <?php } ?>

    <div class="tr footer">

        <?php if ($quest->manageTramePermission()) { ?>
        <a href="main.php?page=gestione_trame&op=insert_trama">
            Registra nuova trama
        </a> |
        <?php } ?>
        <a href="main.php?page=gestione">
            Indietro
        </a>
    </div>

</div>

<!-- Paginatore elenco -->
<div class="pager">
    <?= $quest->getTramePageNumbers(Filters::int($_REQUEST['offset'])); ?>
</div>


