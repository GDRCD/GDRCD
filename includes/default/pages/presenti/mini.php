<div class="presenti_mini_box">
    <div class="general_title">
        Utenti presenti: <span class="presence_counter"><?= Presenti::getInstance()->numberOfPresences(); ?></span>
    </div>

    <div class="presenti_link"><a href="main.php?page=presenti/full">Visualizza tutti i presenti</a></div>

    <div class="general_title"> Presenti nel luogo:</div>

    <div class="box_presenti">
        <?= Presenti::getInstance()->listMiniPresences(); ?>
    </div>

    <script src="<?=Router::getPagesLink('presenti/mini.js');?>"></script>

</div>