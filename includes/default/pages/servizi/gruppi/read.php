<?php

Router::loadRequired();

$gruppi = GruppiRuoli::getInstance();
$corp = Filters::int($_REQUEST['group']);
$corp_data = $gruppi->getGroup($corp);

?>

<div class="general_title"> Ruoli</div>

<div class="gestione_incipit">
    Lista dei ruoli presenti nel gruppo.
</div>

<div class="fake-table roles_list">
    <?= $gruppi->rolesList($corp); ?>
</div>
<div class="general_title"> Membri</div>

<div class="fake-table members_list">
    <?= $gruppi->membersList($corp); ?>
</div>

<div class="general_title"> Statuto</div>
<div class="group_rules">
    <?= Filters::html($corp_data['statuto']); ?>
</div>

<div class="link_back"><a href="main.php?page=servizi/gruppi/index">Indietro</a></div>