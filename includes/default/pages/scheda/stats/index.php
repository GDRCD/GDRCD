<?php

require_once(__DIR__ . '/../../../core/required.php');

$stat_class = SchedaStats::getInstance();
$id_pg = Filters::int($_GET['id_pg']);

if ($stat_class->available($id_pg)) {

    if ($stat_class->isAccessible($id_pg)) { ?>


        <div class="pagina_scheda pagina_scheda_stats">

            <div class="general_title">Statistiche</div>

            <?php require_once(__DIR__ . '/../menu.inc.php'); ?>

            <?php require_once(__DIR__ . "/index.php");?>

        </div>

    <?php }
} ?>
