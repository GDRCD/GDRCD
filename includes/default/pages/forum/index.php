<?php

Router::loadRequired();
$op = Filters::out($_GET['op']);


?>

<?php if ( Forum::getInstance()->isActive() ) { ?>

    <div class="forum_container">
        <?php require_once(__DIR__ . '/' . Forum::getInstance()->loadPage(Filters::out($op))); ?>
    </div>

<?php } else { ?>
    <div class="warning error">Forum disabilitato</div>
<?php } ?>