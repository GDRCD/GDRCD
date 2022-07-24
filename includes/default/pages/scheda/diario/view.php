<?php

$id_pg = Filters::int($_GET['id_pg']);
$id = Filters::int($_GET['id']);

?>
<div class="panels_box">
    <div class="fake-table view-table">
        <?= SchedaDiario::getInstance()->diaryView($id); ?>
    </div>

    <div class="link_back">
        <a href="/main.php?page=scheda/index&op=diario&id_pg=<?= $id_pg ?>">Torna indietro</a>
    </div>
</div>