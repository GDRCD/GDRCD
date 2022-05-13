<?php

require_once(__DIR__ . '/../../../core/required.php');

$scheda_abi = SchedaAbilita::getInstance();
$id_pg = Filters::int($_GET['id_pg']);

if ($scheda_abi->available($id_pg)) {

    if ($scheda_abi->isAccessible($id_pg)) { ?>


        <div class="pagina_scheda pagina_scheda_stats">

            <div class="general_title">Abilità</div>

            <?php require_once(__DIR__ . '/../menu.inc.php'); ?>

            <?php require_once(__DIR__."/{$scheda_abi->indexSchedaAbilita(Filters::out($_GET['op']))}.php");?>

        </div>

    <?php }
} ?>
