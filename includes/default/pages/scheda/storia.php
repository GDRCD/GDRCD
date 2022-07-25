<?php

Router::loadRequired();

$id_pg = Filters::int($_GET['id_pg']);
$data = Personaggio::getPgData($id_pg);

?>

<div class="scheda_storia">

    <div class="general_title">Storia</div>
    <div class="single_text">
        <?= Filters::html($data['storia']); ?>
    </div>

    <div class="general_title">Descrizione</div>
    <div class="single_text">
        <?= Filters::html($data['descrizione']); ?>
    </div>

</div>

