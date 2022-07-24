<?php

$stat_class = Statistiche::getInstance();
$stat_id = Filters::int($_GET['stat']);
$id_pg = Filters::int($_GET['id_pg']);

?>

<div class="stats_list">

    <div class="fake-table scheda_stat_table">
        <?=SchedaStats::getInstance()->statsPage($id_pg);?>
    </div>

    <div class="form_info">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['info_stats']); ?>
    </div>

</div>

<script src="<?=Router::getPagesLink('scheda/stats/list.js');?>"></script>


