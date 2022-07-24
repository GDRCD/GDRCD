<?php

Router::loadRequired();

$stat_class = SchedaStats::getInstance();
$id_pg = Filters::int($_GET['id_pg']);

if ($stat_class->available($id_pg)) {

    if ($stat_class->isAccessible($id_pg)) { ?>


        <div class="pagina_scheda_stats">


            <?php require_once(__DIR__ . "/list.php");?>

        </div>

    <?php }
} ?>
