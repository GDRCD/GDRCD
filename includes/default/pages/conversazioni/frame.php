<?php

//Includio i parametri, la configurazione, la lingua e le funzioni
Router::loadRequired();

if ( Conversazioni::getInstance()->isActive() ) {

    ?>

    <div class="box_messages messages_frame">
        <?= Conversazioni::getInstance()->renderFrameText(); ?>
    </div>

    <script src="<?= Router::getPagesLink('conversazioni/frame.js'); ?>"></script>


<?php } ?>