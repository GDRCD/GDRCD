<?php

Router::loadRequired();
$post_id = Filters::int($_GET['post_id']);

if ( ForumPermessi::getInstance()->permissionHistory() ) { ?>


    <div class="forum_post_container">
        <?= ForumPosts::getInstance()->viewUpdateHistory($post_id); ?>
    </div>

    <div class="link_back">
        <a href="/main.php?page=forum/index&op=post&post_id=<?= $post_id; ?>&pagination=1">Torna indietro</a>
    </div>
<?php } else { ?>
    <div class="warning error"> Permesso negato.</div>
<?php } ?>
