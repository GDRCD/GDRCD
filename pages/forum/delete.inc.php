<?php
$postID = (int) $_POST['id_record'];
$postData = gdrcd_query("SELECT id_messaggio_padre AS padre, autore FROM messaggioaraldo WHERE id_messaggio=".$postID);

if((int) $postData['padre'] == -1 && ($_SESSION['permessi'] >= MODERATOR || $postData['autore'] == $_SESSION['login'])) {
    /*Cancello un topic da admin*/
    gdrcd_query("DELETE FROM araldo_letto WHERE thread_id = ".$postID);
    $query = "DELETE FROM messaggioaraldo WHERE id_messaggio_padre= ".$postID." OR id_messaggio= ".$postID;
    $back = 'forum';
} elseif((int) $postData['padre'] != -1 && ($_SESSION['permessi'] >= MODERATOR || $postData['autore'] == $_SESSION['login'])) {
    /*Cancello un post da admin*/
    $query = "DELETE FROM messaggioaraldo WHERE id_messaggio = ".$postID;
    $back = 'forum&op=read&what='.(int) $postData['padre'];
}

if( ! empty($query)) {
    gdrcd_query($query);
    ?>
    <div class="warning">
        <?php echo gdrcd_filter('out', $MESSAGE['warning']['deleted']); ?>
    </div>
    <div class="link_back">
        <a href="main.php?page=<?php echo $back; ?>">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['topic']); ?>
        </a>
    </div>
    <?php
} else {
    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
}