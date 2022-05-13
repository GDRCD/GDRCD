<?php

Router::loadRequired();

$quest = Quest::getInstance();

$page = Filters::int($_REQUEST['offset']);

# Lista delle quest visibili per questa pagina
 ?>


<div class="fake-table quest_list trame_list">
    <?= $quest->renderTrameList($page); ?>
</div>

<!-- Paginatore elenco -->
<div class="pager">
    <?= $quest->getTramePageNumbers(Filters::int($_REQUEST['offset'])); ?>
</div>

<script src="/includes/default/pagesdes/default/pages/gestione/trame/JS/gestione_trame_list.js"></script>
