<?php

Router::loadRequired();

$scheda_class = SchedaOggetti::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$pg_name = Filters::out($_GET['pg']);
$id_obj = Filters::int($_GET['id_obj']);


if ($scheda_class->available($id_pg)) {

    if ($scheda_class->isAccessible($id_pg)) { ?>
        <div class="scheda_oggetti">
            <div class="dummy">
                <img src="/imgs/avatars/inventory_m.png">asd
            </div>

            <div class="general_title">Oggetti Equipaggiati</div>
            <div class="equipped_objects objects_box">
                <?= $scheda_class->renderPgEquipment($id_pg); ?>
            </div>


            <div class="general_title">Inventario</div>
            <div class="inventory_objects objects_box">
                <?= $scheda_class->renderPgInventory($id_pg); ?>
            </div>

            <div class="general_title">Informazioni oggetto</div>
            <div class="object_info_box"></div>

            <script src="<?= Router::getPagesLink('scheda/oggetti/oggetti.js'); ?>"></script>

        </div>

    <?php } else { ?>

        <div class="warning">Pagina non accessibile</div>

    <?php }

} else { ?>
    <div class="warning">Personaggio inesistente.</div>
<?php } ?>