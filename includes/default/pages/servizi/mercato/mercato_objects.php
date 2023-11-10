<?php

Router::loadRequired();

$mercato = Mercato::getInstance();
$obj_class = Oggetti::getInstance();

$shop = Filters::int($_GET['shop']);

if ( !empty($shop) ) {
    $shop_data = $mercato->getShop($shop, 'nome');
    $shop_objects = $mercato->getAllShopObjects($shop);

    if ( isset($_POST['op']) ) {

        switch ( $_POST['op'] ) {
            case 'buy':
                $resp = $mercato->buyObject($_POST);
                break;
        }
    }

    if ( isset($resp) ) {
        ?>
        <div class='warning'><?= $resp['mex']; ?></div>
        <div class='link_back'>
            <a href="/main.php?page=servizi/mercato/mercato_index&op=objects&shop=<?= $shop; ?>">Indietro</a>
        </div>
        <?php Functions::redirect("/main.php?page=servizi/mercato/mercato_index&op=objects&shop={$shop}", 3);
    }

    ?>


    <div class="shop_objects_box">

        <div class="general_title"><?= Filters::out($shop_data['nome']); ?></div>

        <div class="objects_box">

            <?php foreach ( $shop_objects as $object ) {

                $id = Filters::int($object['id']);
                $id_obj = Filters::in($object['oggetto']);
                $nome = Filters::out($object['nome']);
                $descr = Filters::text($object['descrizione']);
                $img = Filters::out($object['immagine']);
                $costo = Filters::int($object['costo']);
                $cariche = Filters::int($object['cariche']);
                $quantity = Filters::int($object['quantity']);
                $tipo = Filters::int($object['tipo']);
                $tipo_data = OggettiTipo::getInstance()->getObjectType($tipo);
                $tipo_name = Filters::out($tipo_data['nome']);

                ?>

                <div class='single_shop_object form_container'>
                    <div class='object_img'><img src='/themes/advanced/imgs/items/<?= $img; ?>'></div>
                    <div class='object_data'>
                        <div class='object_name'><?= $nome; ?></div>
                        <div class='object_descr'><?= $descr; ?></div>
                        <div class='object_info'>Tipo : <?= $tipo_name; ?> </div>
                        <div class='object_info'>Cariche : <?= $cariche; ?></div>
                        <div class='object_info'>Costo : <?= $costo; ?></div>
                        <div class='object_info'>Quantita' : <?= $quantity; ?></div>

                        <div class="object_commands">
                            <form method="POST" class="form">
                                <div class="single_input">
                                    <input type="hidden" name="object" value="<?= $id_obj; ?>">
                                    <input type="hidden" name="op" value="buy">
                                    <input type="hidden" name="shop" value="<?= $shop; ?>">
                                    <button type="submit">Acquista</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

            <?php } ?>
        </div>

    </div>
<?php } ?>