<?php

$stat_class = Statistiche::getInstance();
$stat_id = Filters::int($_GET['stat']);
$id_pg = Filters::int($_GET['id_pg']);

$resp = PersonaggioStats::downgradePgStat($stat_id,$id_pg);
?>


<div class="warning"> <?=$resp['mex'];?></div>
<div class="link_back">
    <a href="main.php?page=scheda_stats&id_pg=<?=$id_pg;?>">Indietro</a>
</div>

<?php Functions::redirect("/main.php?page=scheda_stats&id_pg={$id_pg}",3); ?>