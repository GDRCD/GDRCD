<?php

require_once(__DIR__ . '/../../../includes/required.php');

$gathering_item = GatheringItem::getInstance();
$gathering_cat = GatheringCategory::getInstance();


$op = Filters::out($_GET['op']);
?>


<div class="gestione_incipit">
    Creazione nuovo oggetto.
</div>
<div class="form_container">

    <?php if(isset($resp)){ ?>
        <div class="warning"><?=$resp['mex'];?></div>
        <div class="link_back"><a href="/main.php?page=gestione_gathering_item">Indietro</a></div>
        <?php
        Functions::redirect('/main.php?page=gestione_gathering_item',2);
    } ?>
    <form method="POST" class="form">


        <div class="single_input">
            <div class='label'>
                Nome
            </div>
            <input name="nome" required/>
        </div>
        <div class="single_input">
            <div class='label'>
                Immagine
            </div>
            <input name="immagine" />
        </div>
        <?php
        if ($gathering_item->gatheringRarity()) {
           echo '<div class="single_input">
                    <div class="label">
                        Quantità
                    </div>
                    <input name="quantita" type="number" />
                   </div>';
        }
        ?>
        <div class="single_input">
            <div class="label">Categoria</div>
            <select name="categoria">
                <?=$gathering_cat->listGatheringCat();?>
            </select>
        </div>

        <div class="single_input">
            <div class='label'>
                Descrizione
            </div>
            <textarea name="descrizione" ></textarea>
        </div>
        <!-- bottoni -->
        <div class="single_input">
            <div class='form_submit'>
                <input type="hidden" name="action" value="new_item">
                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/>
            </div>
        </div>

    </form>
    <script src="pages/gestione/gathering/JS/gathering_item_new.js"></script>

    <div class="link_back"><a href="/main.php?page=gestione_gathering_item">Indietro</a></div>
</div>