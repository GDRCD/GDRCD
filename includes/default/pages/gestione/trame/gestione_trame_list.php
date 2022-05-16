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

<script src="<?=Router::getPagesLink('gestione/trame/gestione_trame_list.js');?>"></script>
