<?php

Router::loadRequired();

$news_id = Filters::int($_GET['news_id']);

if ( News::getInstance()->permissionReadNews($news_id) ) {
    ?>
    <div class="general_title">News : "<?= News::getInstance()->renderNewsTitle($news_id); ?>"</div>

    <div class="news_container">
        <?= News::getInstance()->newsRead($news_id); ?>
    </div>

    <div class="link_back">
        <a href="main.php?page=news/index">
            Torna indietro
        </a>
    </div>

<?php } else { ?>

    <div class="warning error"> Permesso negato.</div>

<?php } ?>