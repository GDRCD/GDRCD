<?php

require_once(__DIR__ . '/../../../includes/required.php');

$scheda_class = SchedaOggetti::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$pg_name = Filters::out($_GET['pg']);
$id_obj = Filters::int($_GET['id_obj']);


if ($scheda_class->available($id_pg)) {

    if ($scheda_class->isAccessible($id_pg)) {

        if (isset($_POST['op'])) {

            switch ($_POST['op']) {
                case 'equip':
                    $resp = $scheda_class->equipObj($_POST);
                    break;
                case 'remove':
                    $resp = $scheda_class->removeObj($_POST);
                    unset($id_obj);
                    break;
            }
        }

        ?>

        <div class="pagina_scheda_oggetti">
            <div class="dummy">
                <img src="/imgs/avatars/inventory_m.png">
            </div>

            <?php if (isset($resp)) { ?>

                <div class="warning"><?= $resp['mex']; ?></div>

                <div class="link_back">
                    <a href="/main.php?page=scheda_oggetti&pg=<?= $pg_name; ?>&id_pg=<?= $id_pg; ?>&id_obj=<?= $id_obj; ?>">Indietro</a>
                </div>

                <?php Functions::redirect("/main.php?page=scheda_oggetti&pg={$pg_name}&id_pg={$id_pg}&id_obj={$id_obj}", 3); ?>

            <?php } ?>

            <div class="general_title">Oggetti Equipaggiati</div>
            <div class="equipped_objects objects_box">
                <?= $scheda_class->renderPgEquipment($id_pg); ?>
            </div>


            <div class="general_title">Inventario</div>
            <div class="inventory_objects objects_box">
                <?= $scheda_class->renderPgInventory($id_pg); ?>
            </div>

            <?php if (!empty($id_obj)) {

                $obj_class = Oggetti::getInstance();
                $obj_data = $scheda_class->renderObjectInfo($id_obj, $id_pg);
                $type_data = $obj_class->getObjectType(Filters::int($obj_data['tipo']));
                $indossato = (Filters::bool($obj_data['indossato'])) ? 'Rimuovi' : 'Equipaggia';

                ?>

                <div class="general_title">Informazioni oggetto</div>
                <div class="objects_box single_object form_container">

                    <div class='object_img'>
                        <img src='/themes/advanced/imgs/items/<?= Filters::out($obj_data['immagine']); ?>'>
                    </div>
                    <div class='object_data'>
                        <div class='object_name'><?= Filters::out($obj_data['nome']); ?></div>
                        <div class='object_descr'><?= Filters::out($obj_data['descrizione']); ?></div>
                        <div class='object_info'>Tipo : <?= Filters::out($type_data['nome']); ?> </div>
                        <div class='object_info'>Cariche : <?= Filters::out($obj_data['cariche_obj']); ?></div>

                        <div class="object_commands">
                            <?php if (Personaggio::isMyPg($id_pg) || $scheda_class->permissionEquipObjects()) { ?>

                                <form method="POST" class="form">
                                    <div class="single_input">
                                        <input type="hidden" name="object" value="<?= $id_obj; ?>">
                                        <input type="hidden" name="pg" value="<?= $id_pg; ?>">
                                        <input type="hidden" name="op" value="equip">
                                        <button type="submit"><?= $indossato; ?></button>
                                    </div>
                                </form>

                            <?php }

                            if (Personaggio::isMyPg($id_pg) || $scheda_class->permissionRemoveObjects()) { ?>

                                <form method="POST" class="form">
                                    <div class="single_input">
                                        <input type="hidden" name="object" value="<?= $id_obj; ?>">
                                        <input type="hidden" name="pg" value="<?= $id_pg; ?>">
                                        <input type="hidden" name="op" value="remove">
                                        <button type="submit">Getta</button>
                                    </div>
                                </form>
                            <?php } ?>

                        </div>


                    </div>
                </div>

            <?php } ?>

        </div>
    <?php } else { ?>

        <div class="warning"> Permesso negato.</div>

        <?php Functions::redirect('/main.php', 3); ?>

        <?php
    }
} else { ?>

    <div class="warning"> Personaggio inesistente.</div>

    <?php Functions::redirect('/main.php', 3); ?>

<?php } ?>
