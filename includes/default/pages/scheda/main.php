<?php

$pg_id = Filters::int($_GET['id_pg']);

?>

<div class="scheda_main_page">
    <?= Scheda::getInstance()->characterMainPage($pg_id);?>
</div>
