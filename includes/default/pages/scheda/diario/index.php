<?php

$id_pg = Filters::int($_GET['id_pg']);

if (SchedaDiario::getInstance()->diaryActive()) {
    ?>

    <div class="fake-table scheda_diario_table">
        <?= SchedaDiario::getInstance()->diaryList($id_pg); ?>
    </div>

    <a href="main.php?page=scheda/index&op=diario_new&id_pg=<?= $id_pg; ?>">
        Nuova pagina
    </a>

    <div class="link_back">
        <a href="/main.php?page=scheda/index&id_pg=<?= $id_pg ?>">Torna indietro</a>
    </div>

    <script src="<?= Router::getPagesLink('scheda/diario/index.js'); ?>"></script>

<?php } ?>