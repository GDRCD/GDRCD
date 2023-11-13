<?php

//Includio i parametri, la configurazione, la lingua e le funzioni
Router::loadRequired();

$cls = Tickets::getInstance();

if ( $cls::getInstance()->isEnabled() ) {
    ?>

    <div class="box_tickets tickets_frame">
        <?= $cls->renderFrameText(); ?>
    </div>

    <script src="<?= Router::getPagesLink('tickets/frame.js'); ?>"></script>

<?php } ?>
