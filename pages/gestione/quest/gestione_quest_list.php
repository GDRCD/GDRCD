<?php

require_once(__DIR__ . '/../../../includes/required.php');

$quest = Quest::getInstance();
$page = Filters::int($_REQUEST['offset']);

?>


<!-- Elenco dei record paginato -->
<div class="fake-table quest_list">
    <?= $quest->renderQuestList($page); ?>
</div>

<!-- Paginatore elenco -->
<div class="pager">
    <?= $quest->getQuestsPageNumbers(Filters::int($_REQUEST['offset'])); ?>
</div>

<script src="/pages/gestione/quest/JS/gestione_quest_list.js"></script>
