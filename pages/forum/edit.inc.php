<?php
$row = gdrcd_query("SELECT autore, titolo, messaggio, id_messaggio_padre FROM messaggioaraldo WHERE id_messaggio=".gdrcd_filter('num', $_POST['id_messaggio']));

if($row['autore'] == $_SESSION['login'] || ($row['autore'] != $_SESSION['login'] && $_SESSION['permessi'] >= MODERATOR)) {
    $time = strftime('%d/%m/%Y %H:%M');

    gdrcd_query("UPDATE messaggioaraldo SET messaggio = '".gdrcd_filter('in', $_POST['messaggio']).'\n\n\n\nEdit ('.$_SESSION['login'].'): '.$time."', titolo = '".gdrcd_filter('in', $_POST['titolo'])."' WHERE id_messaggio = ".gdrcd_filter('num', $_POST['id_messaggio'])." LIMIT 1");
    ?>
    <div class="warning">
        <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
    </div>
    <div class="link_back">
        <a href="main.php?page=forum">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['back']); ?>
        </a>
    </div>
    <?php
    if($row['id_messaggio_padre'] == -1) {
        gdrcd_redirect('main.php?page=forum&op=read&what='.gdrcd_filter('num', $_POST['id_messaggio']).'&where='.gdrcd_filter('num', $_POST['araldo']));
    } else {
        gdrcd_redirect('main.php?page=forum&op=read&what='.gdrcd_filter('num', $row['id_messaggio_padre']).'&where='.gdrcd_filter('num', $_POST['araldo']));
    }
} else {
    ?>
    <div class="warning">
        Permesso negato.
    </div>
    <?php
}