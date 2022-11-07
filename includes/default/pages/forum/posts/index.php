<?php

Router::loadRequired();
$forum_id = Filters::int($_GET['forum_id']);
$pagination = ($_GET['pagination']) ? Filters::int($_GET['pagination']) : 1;

if ( ForumPermessi::getInstance()->permissionForum($forum_id) ) {
    ?>
    <div class="general_title"><?= Forum::getInstance()->renderForumName($forum_id); ?> </div>

     <div class="fake-table forum_posts_table">
        <?= ForumPosts::getInstance()->postsList($forum_id,$pagination); ?>
    </div>
<?php } else { ?>
    <div class="warning error">Permesso negato.</div>
<?php } ?>