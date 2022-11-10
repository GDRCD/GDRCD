<?php

Router::loadRequired();
$op = Filters::out($_GET['op']);

?>

<?php if ( News::getInstance()->isActive() ) { ?>

    <div class="news_container">
        <?php require_once(__DIR__ . '/' . News::getInstance()->loadPage(Filters::out($op))); ?>
    </div>

<?php } else { ?>
    <div class="warning error">Permesso negato</div>
<?php } ?>