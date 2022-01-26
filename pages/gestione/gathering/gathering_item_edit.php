<?php

require_once(__DIR__ . '/../../../includes/required.php');

$gathering_item = GatheringItem::getInstance();
$gathering_cat = GatheringCategory::getInstance();


$op = Filters::out($_GET['op']);
$item=$gathering_item->getoneGatheringItem($_GET['id']);
?>


<div class="gestione_incipit">
    Modifica oggetto.
</div>
<div class="form_container">

    <?php if(isset($resp)){ ?>
        <div class="warning"><?=$resp['mex'];?></div>
        <div class="link_back"><a href="/main.php?page=gestione_gathering_category">Indietro</a></div>
        <?php
        Functions::redirect('/main.php?page=gestione_gathering_category',2);
    } ?>
    <form method="POST" class="form">


        <div class="single_input">
            <div class='label'>
                Nome
            </div>
            <input name="nome" required value="<?php echo Filters::out($item['nome']); ?>"/>
        </div>
        <div class="single_input">
            <div class='label'>
                Immagine
            </div>
            <input name="immagine"  value="<?php echo Filters::out($item['immagine']); ?>"/>
        </div>
        <div class="single_input">
            <div class="label">Categoria</div>
            <select name="categoria">
                <?=$gathering_cat->listGatheringCat(Filters::out($item['categoria']));?>
            </select>
        </div>

        <div class="single_input">
            <div class='label'>
                Descrizione
            </div>
            <textarea name="descrizione" ><?php echo Filters::out($item['descrizione']); ?></textarea>
        </div>
        <div class="form_info">
           <?php
                echo "Creato da: ". Filters::out($item['creato_da']) ." il ". Filters::date($item['creato_il'],"d/m/Y") ;
           ?>
        </div>

        <!-- bottoni -->
        <div class="single_input">
            <div class='form_submit'>
                <input type="hidden" name="action" value="edit_item">
                <input type="hidden" name="id" required value="<?php echo Filters::out($_GET['id']); ?>"/>
                <input type="submit"  value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/>
            </div>
        </div>

    </form>
    <script src="pages/gestione/gathering/JS/gathering_item_edit.js"></script>

    <div class="link_back"><a href="/main.php?page=gestione_gathering_item">Indietro</a></div>
</div>