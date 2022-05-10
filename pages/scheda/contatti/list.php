<?php

require_once(__DIR__ . '/../../../includes/required.php');

$contatti = Contacts::getInstance();
?>


<div class="fake-table gathering_list">
    <?= $contatti->ContactList($id_pg); ?>
</div>