<?php
Router::loadRequired();

$contatti = Contatti::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$pg = Filters::in($_GET['pg']);

if ( $contatti->contactEnables() ) { ?>

    <div class="fake-table contatti_list">
        <?= $contatti->ContactList($id_pg); ?>
    </div>

    <script src="<?= Router::getPagesLink('scheda/contatti/JS/contact_delete.js'); ?>"></script>

<?php } ?>