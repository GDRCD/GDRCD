<?php

Router::loadRequired();

if ( Calendario::getInstance()->calendarEnabled() ) {
    ?>

    <div id="calendar_container"></div>

    <script src="<?= Router::getPagesLink('calendario/index.js'); ?>"></script>

<?php } else { ?>
    <div class="warning error">Calendario disabilitato</div>
<?php } ?>