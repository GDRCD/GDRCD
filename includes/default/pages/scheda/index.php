<?php

Router::loadRequired();
$pg_id = Filters::int($_GET['id_pg']);
$pg_name = Personaggio::nameFromId($pg_id);
$op = Filters::out($_GET['op']);

?>

<div class="general_title">Scheda di <?= $pg_name; ?> </div>
<div class="menu_container">
    <?php require_once(__DIR__ . '/menu.inc.php'); ?>
</div>

<div class="container_character">
    <?php require_once(__DIR__ . '/' . Scheda::getInstance()->loadCharacterPage(Filters::out($op))); ?>
</div>
