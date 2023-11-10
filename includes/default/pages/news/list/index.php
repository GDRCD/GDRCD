<?php

Router::loadRequired();

?>
<div class="general_title">Lista News</div>

<div class="news_list">
    <?=News::getInstance()->newsList();?>
</div>