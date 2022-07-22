<div class="general_title"> Utenti presenti:  <?= Presenti::getInstance()->numberOfPresences(); ?></div>

<div class="box_presenti">
    <?= Presenti::getInstance()->listMiniPresences(); ?>
</div>