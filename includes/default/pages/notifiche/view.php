<?php

Router::loadRequired();

$op = Filters::out($_GET['op']);

Notifiche::getInstance()->setAllNotificationsRead();

?>

<div class="general_title">
    Notifiche
</div>
<div id="notifiche_view_container">

    <?php if ( Notifiche::getInstance()->isEnabled() ) { ?>

        <div class="notifications_container">
            <?= Notifiche::getInstance()->renderNotificationsPage(); ?>
        </div>

        <script src="<?= Router::getPagesLink('notifiche/view.js'); ?>"></script>

    <?php } else { ?>
        <div class="warning error">Notifiche disabilitate</div>
    <?php } ?>
</div>
