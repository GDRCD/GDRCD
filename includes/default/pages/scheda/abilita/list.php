<?php
$scheda_class = SchedaAbilita::getInstance();
$id_pg = Filters::in($_REQUEST['id_pg']);
$remained_exp = PersonaggioAbilita::getInstance()->RemainedExp($id_pg);
?>

<div class="scheda_abilita_box">
    <div class="fake-table ability_table">
        <?= $scheda_class->abilityPage($id_pg); ?>
    </div>
</div>

<div class="form_info">
    Punti esperienza disponibili: <span class="remained_exp"> <?= $remained_exp; ?></span>
</div>

<div class="link_back">
    <a href="/main.php?page=scheda/index&id_pg=<?= $id_pg ?>">Torna indietro</a>
</div>

<script src="<?= Router::getPagesLink('scheda/abilita/list.js'); ?>"></script>