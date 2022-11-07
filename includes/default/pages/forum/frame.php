<?php

//Includio i parametri, la configurazione, la lingua e le funzioni
Router::loadRequired();

?>

<div class="box_forums forum_frame">
    <?=Forum::getInstance()->renderFrameText();?>
</div>

<script src="<?= Router::getPagesLink('forum/frame.js'); ?>"></script>


