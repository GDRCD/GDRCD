<?php

Router::loadRequired();
$op = Filters::out($_GET['op']);

?>

<?php if ( Notifiche::getInstance()->isEnabled() ) { ?>

    <div class="notifications_container">
        <?php require_once(__DIR__ . '/' . Notifiche::getInstance()->loadPage(Filters::out($op))); ?>
    </div>

<?php } else { ?>
    <div class="warning error">Notifiche disabilitate</div>
<?php } ?>