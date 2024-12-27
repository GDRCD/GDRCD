<?php

Router::loadRequired();

?>

<div class="box_menu_icons box_messages">
    <?php if ( Conversazioni::getInstance()->conversationsEnabled() ) { ?>
        <div class="frame_single_icon icon_messages">
            <a href="/main.php?page=conversazioni/index">
                <i class="fas fa-envelope"></i>
            </a>
        </div>
    <?php } ?>

    <?php if ( Forum::getInstance()->isActive() ) { ?>
        <div class="frame_single_icon icon_forum">
            <a href="/main.php?page=forum/index">
                <i class="fas fa-books"></i>
            </a>
        </div>
    <?php } ?>

    <?php if ( News::getInstance()->newsEnabled() ) { ?>
        <div class="frame_single_icon icon_news">
            <a href="/main.php?page=news/index">
                <i class="fas fa-newspaper"></i>
            </a>
        </div>
    <?php } ?>

    <?php if ( OnlineStatus::getInstance()->isEnabled() ) { ?>
        <div class="frame_single_icon icon_status">
            <i class="fas fa-user-tag"></i>
        </div>
    <?php } ?>

    <?php if ( Notifiche::getInstance()->isEnabled() ) { ?>
        <div class="frame_single_icon icon_notifications">
            <a href="/main.php?page=notifiche/index">
                <i class="fas fa-bell"></i>
            </a>
        </div>
    <?php } ?>

</div>

<script src="<?= Router::getPagesLink('frame_left.js'); ?>"></script>

<?php ?>
