<?php

//Includio i parametri, la configurazione, la lingua e le funzioni
Router::loadRequired();

if ( News::getInstance()->newsEnabled() ) {
    ?>

    <div class="box_news news_frame">
        <?= News::getInstance()->renderFrameText(); ?>
    </div>

    <script src="<?= Router::getPagesLink('news/frame.js'); ?>"></script>

<?php } ?>
