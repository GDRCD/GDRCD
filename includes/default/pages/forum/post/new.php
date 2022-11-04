<?php

Router::loadRequired();

$cls = Forum::getInstance();
$forum_id = Filters::int($_GET['forum_id']);

if ( ForumPermessi::getInstance()->haveForumPermission($forum_id) ) {
    ?>

    <div class="form_container forum_new_post">

        <!-- INSERT -->
        <form class="form ajax_form"
              action="forum/post/ajax.php"
              data-callback="() => newPostRedirectBack(<?=$forum_id;?>)">

            <div class="form_title">Nuovo post in "<?= Forum::getInstance()->renderForumName($forum_id); ?>"</div>

            <div class="single_input">
                <div class="label">Titolo</div>
                <input type="text" name="titolo" required>
            </div>

            <div class="single_input">
                <div class="label">Testo</div>
                <textarea name="testo" required></textarea>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="new_post">
                <input type="hidden" name="forum_id" value="<?= $forum_id; ?>">
                <input type="submit" value="Invia">
            </div>

        </form>

        <div class="link_back">
            <a href="/main.php?page=forum/index&op=posts&forum_id=<?= $forum_id; ?>&pagination=1">Torna indietro</a>
        </div>
    </div>

    <script src="<?= Router::getPagesLink('forum/post/new.js'); ?>"></script>

<?php } else { ?>
    <div class="warning error">Permesso negato</div>
<?php } ?>