<?php

$pg_id = isset($_GET['id_pg']) ? Filters::out($_GET['id_pg']) : Functions::getInstance()->getMyId();

?>

<div class="scheda_main_page">
    <?= Scheda::getInstance()->characterMainPage($pg_id); ?>
</div>
