<?php

require_once(__DIR__ . '/../../../includes/required.php');

$gathering_cat = GatheringCategory::getInstance();


$op = Filters::out($_GET['op']);
?>


<div class="gestione_incipit">
    Creazione nuova categoria oggetto.
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
            <input name="nome" required/>
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
                <input type="hidden" name="action" value="new_cat">
                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/>
            </div>
        </div>

    </form>
    <script src="pages/gestione/gathering/JS/gathering_cat_new.js"></script>

    <div class="link_back"><a href="/main.php?page=gestione_gathering_category">Indietro</a></div>
</div>