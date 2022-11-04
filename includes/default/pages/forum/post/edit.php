<?php

Router::loadRequired();

$cls = Forum::getInstance();
$post_id = Filters::int($_GET['post_id']);

if ( ForumPermessi::getInstance()->haveForumPermissionByPostId($post_id) && ForumPermessi::getInstance()->permissionPostEdit($post_id) ) {

    $post_data = ForumPosts::getInstance()->getPost($post_id, 'id_forum, titolo, testo');

    ?>

    <div class="form_container forum_new_post">

        <!-- INSERT -->
        <form class="form ajax_form"
              action="forum/post/ajax.php"
              data-callback="() => editPostRedirectBack(<?=$post_id;?>)">

            <div class="form_title">Modifica post "<?= Filters::string($post_data['titolo']); ?>"</div>

            <div class="single_input">
                <div class="label">Titolo</div>
                <input type="text" name="titolo" value="<?=Filters::out($post_data['titolo']);?>" required>
            </div>

            <div class="single_input">
                <div class="label">Testo</div>
                <textarea name="testo" required><?=Filters::out($post_data['testo']);?></textarea>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="edit_post">
                <input type="hidden" name="post_id" value="<?= $post_id; ?>">
                <input type="submit" value="Invia">
            </div>

        </form>

        <div class="link_back">
            <a href="/main.php?page=forum/index&op=post&post_id=<?= $post_id ?>&pagination=1">Torna indietro</a>
        </div>
    </div>

    <script src="<?= Router::getPagesLink('forum/post/edit.js'); ?>"></script>

<?php } else { ?>
    <div class="warning error">Permesso negato</div>
<?php } ?>