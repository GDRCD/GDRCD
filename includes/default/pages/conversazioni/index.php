<?php

Router::loadRequired();
$op = Filters::out($_GET['op']);


?>

<?php if ( Conversazioni::getInstance()->conversationsEnabled() ) { ?>

    <div class="conversazioni_container">
        <?php require_once(__DIR__ . '/' . Conversazioni::getInstance()->loadPage(Filters::out($op))); ?>
    </div>

<?php } else { ?>
    <div class="warning error">Forum disabilitato</div>
<?php } ?>