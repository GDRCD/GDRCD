<?php

Router::loadRequired();


?>
<div class="general_title">Lista Forum</div>

<div class="forums_list">
    <?=Forum::getInstance()->forumsList();?>
</div>