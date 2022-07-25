<?php

$gruppi_oggetto = GruppiOggetto::getInstance();
$group = Filters::int($_REQUEST['group']);

if ( $gruppi_oggetto->activeStorage() && $gruppi_oggetto->permissionViewStorage($group) ) {

    $group_data = Gruppi::getInstance()->getGroup($group, 'nome');
    $group_name = Filters::out($group_data['nome']);
    ?>


    <div class="general_title"><?= $group_name; ?> - Gestione magazzino gruppo</div>

    <div class="groups_storage_container">
        <div class="group_objects_container">
            <?= $gruppi_oggetto->objectListRender($group); ?>
        </div>
        <div class="group_objects_info">

        </div>
    </div>

    <script src="<?= Router::getPagesLink('servizi/gruppi/storage.js'); ?>"></script>

<?php }
