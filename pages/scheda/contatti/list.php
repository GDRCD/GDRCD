<?php

require_once(__DIR__ . '/../../../includes/required.php');

$contatti = Contacts::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
?>


<div class="fake-table gathering_list">
    <?= $contatti->ContactList($id_pg); ?>
</div>