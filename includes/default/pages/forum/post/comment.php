<?php

Router::loadRequired();

$cls = Forum::getInstance();
$post_id = Filters::int($_GET['post_id']);

if ( ForumPermessi::getInstance()->permissionPostComment($post_id) ) {
    ?>

    <div class="form_container forum_new_post">

        <!-- INSERT -->
        <form class="form ajax_form"
              action="forum/ajax.php"
              data-callback="() => commentPostRedirectBack(<?=$post_id;?>)">

            <div class="form_title">Nuovo commento in "<?= ForumPosts::getInstance()->renderPostName($post_id); ?>"</div>

            <div class="single_input">
                <div class="label">Titolo</div>
                <input type="text" name="titolo" required>
            </div>

            <div class="single_input">
                <div class="label">Testo</div>
                <textarea name="testo" required></textarea>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="comment_post">
                <input type="hidden" name="post_id" value="<?= $post_id; ?>">
                <input type="submit" value="Invia">
            </div>

        </form>

        <div class="link_back">
            <a href="/main.php?page=forum/index&op=post&post_id=<?= $post_id; ?>&pagination=1">Torna indietro</a>
        </div>
    </div>

    <script src="<?= Router::getPagesLink('forum/post/comment.js'); ?>"></script>

<?php } else { ?>
    <div class="warning error">Permesso negato</div>
<?php } ?>