<?php

Router::loadRequired();

?>
<div class="general_title">Lista Tickets</div>

<div class="news_list">
    <?=Tickets::getInstance()->ticketsListUser();?>
</div>