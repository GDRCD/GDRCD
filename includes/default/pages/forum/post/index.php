<?php

Router::loadRequired();
$post_id = Filters::int($_GET['post_id']);
$pagination = Filters::int($_GET['pagination']);

if ( (ForumPosts::getInstance()->getOriginalPostId($post_id) == $post_id) && ForumPermessi::getInstance()->permissionForumByPostId($post_id)) {

    $post_data = ForumPosts::getInstance()->getPost($post_id, 'id_forum,chiuso,importante');
    $forum_id = Filters::int($post_data['id_forum']);
    ForumPosts::getInstance()->readPost($post_id);

    ?>
    <div class="forum_post_container">
        <div class="general_title"><?= ForumPosts::getInstance()->renderPostName($post_id); ?> </div>


        <div class="forum_post">
            <?= ForumPosts::getInstance()->singlePost($post_id, $pagination); ?>
        </div>

        <?php if ( ForumPermessi::getInstance()->permissionPostComment($post_id) ) { ?>
            <div class="general_subtitle comment_button">
                <a href="/main.php?page=forum/index&op=post_comment&post_id=<?= $post_id; ?>">
                    Rispondi
                </a>
            </div>
        <?php } ?>

        <div class="general_subtitle comment_button">
            <a href="/main.php?page=forum/index&op=posts&forum_id=<?= $forum_id; ?>">
                Indietro
            </a>
        </div>
    </div>

    <script src="<?= Router::getPagesLink('forum/post/index.js'); ?>"></script>
<?php } else { ?>
    <div class="warning error">Permesso negato.</div>
<?php } ?>