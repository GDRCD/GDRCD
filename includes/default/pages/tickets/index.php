<?php

Router::loadRequired();
$op = Filters::out($_GET['op']);

?>

<?php if ( Tickets::getInstance()->isEnabled() ) { ?>

    <div class="tickets_container">
        <?php require_once(__DIR__ . '/' . Tickets::getInstance()->loadPage(Filters::out($op))); ?>
    </div>

<?php } else { ?>
    <div class="warning error">Permesso negato</div>
<?php } ?>