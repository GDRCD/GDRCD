<?php
$result = gdrcd_query("SELECT messaggioaraldo.id_messaggio, messaggioaraldo.id_messaggio_padre, messaggioaraldo.titolo, messaggioaraldo.messaggio, messaggioaraldo.autore, messaggioaraldo.data_messaggio, messaggioaraldo.chiuso, araldo.tipo, araldo.nome, araldo.proprietari, personaggio.url_img, araldo.id_araldo FROM messaggioaraldo LEFT JOIN araldo ON messaggioaraldo.id_araldo = araldo.id_araldo LEFT JOIN personaggio ON messaggioaraldo.autore = personaggio.nome WHERE (messaggioaraldo.id_messaggio_padre = ".gdrcd_filter('num', $_REQUEST['what'])." AND messaggioaraldo.id_messaggio_padre != -1) OR messaggioaraldo.id_messaggio = ".gdrcd_filter('num', $_REQUEST['what'])." ORDER BY id_messaggio_padre, data_messaggio", 'result');
$row = gdrcd_query($result, 'fetch');
if( ! empty($row)) {
    $araldo = (int) $row['id_araldo'];
    $chiuso = $row['chiuso'];

    /*Restrizione di accesso i forum admin e master*/
    if(!gdrcd_controllo_permessi_forum($row['tipo'],$row['proprietari'])){
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else {
        //Inserimento il record al pg come thread letto
        $check_letto = gdrcd_query("SELECT * FROM araldo_letto WHERE nome = '".$_SESSION['login']."' AND thread_id = ".gdrcd_filter('num', $_REQUEST['what']));
        if($check_letto['id'] <= 0) {
            gdrcd_query("INSERT INTO araldo_letto (nome, araldo_id, thread_id) VALUES ('".$_SESSION['login']."', ".gdrcd_filter('num', $_REQUEST['where']).", ".gdrcd_filter('num', $_REQUEST['what']).")");
        }
        ?>
        <div class="panels_box">
            <table>
                <tr><!-- Intestazione tabella -->
                    <td colspan="2">
                        <div class="capitolo_elenco">
                            <?php echo gdrcd_filter('out', $row['nome']); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="forum_main_title">
                        <div class="forum_post_title">
                            <?php echo gdrcd_filter('out', $row['titolo']); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="forum_main_post_author">
                        <div class="forum_post_author">
                            <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('out', $row['autore']); ?>">
                                <?php echo gdrcd_filter('out', $row['autore']); ?>
                            </a>

                            <div class="forum_avatar">
                                <img src="<?php echo gdrcd_filter('out', $row['url_img']); ?>"
                                     class="img_forum_avatar">
                            </div>
                            <div class="forum_date_small">
                                <?php echo gdrcd_format_date($row['data_messaggio']).' '.gdrcd_format_time($row['data_messaggio']); ?>
                            </div>
                        </div>
                    </td>
                    <td class="forum_main_post_message">
                        <div class="forum_post_message">
                            <?php

                            /** * Se è disponibile il plugin bbd per il trattamento del bbcode usiamo quella
                             * @author Blancks
                             */
                            if($PARAMETERS['settings']['forum_bbcode']['type'] == 'bbd') {
                                echo bbdecoder(gdrcd_filter('out', $row['messaggio']), true);
                            } else {
                                echo gdrcd_bbcoder(gdrcd_filter('out', $row['messaggio']));
                            }
                            ?>
                        </div>
                        <div class="forum_post_modify">
                            <?php
                            if($chiuso == 0 || $_SESSION['permessi'] >= MODERATOR) {
                                ?>
                                <a href="main.php?page=forum&op=composer&what=<?php echo gdrcd_filter('num', $_REQUEST['what']); ?>&where=<?php echo gdrcd_filter('num', $_REQUEST['where']); ?>&quote=<?php echo $row['id_messaggio']; ?>">[<?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['quote']); ?>]</a>
                                <?php
                            }

                            if(($_SESSION['login'] == $row['autore'] && $chiuso == 0) || ($_SESSION['permessi'] >= MODERATOR)) {
                                ?>
                                <a href="main.php?page=forum&op=modifica&what=<?php echo $row['id_messaggio']; ?>">[<?php echo $MESSAGE['interface']['forums']['link']['edit']; ?>]</a>
                                <a href="main.php?page=forum&op=delete_conf&id_record=<?php echo $row['id_messaggio']; ?>&padre=<?php echo $row['id_messaggio_padre']; ?>">[<?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['delete']); ?>]</a>
                                <?php
                            }
                            ?>
                        </div>
                    </td>
                </tr>
                <?php
                while($row = gdrcd_query($result, 'fetch')) {
                    ?>
                    <tr>
                        <td class="forum_other_post_author">
                            <div class="forum_post_author">
                                <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('out', $row['autore']); ?>">
                                    <?php echo gdrcd_filter('out', $row['autore']); ?>
                                </a>
                                <div class="forum_avatar">
                                    <img src="<?php echo gdrcd_filter('out', $row['url_img']); ?>" class="img_forum_avatar">
                                </div>
                                <div class="forum_date_small">
                                    <?php echo gdrcd_format_date($row['data_messaggio']).' '.gdrcd_format_time($row['data_messaggio']); ?>
                                </div>
                            </div>
                        </td>
                        <td class="forum_other_post_message">
                            <div class="forum_post_message">
                                <?php
                                /** * Se è disponibile il plugin bbd per il trattamento del bbcode usiamo quella
                                 * @author Blancks
                                 */
                                if($PARAMETERS['settings']['forum_bbcode']['type'] == 'bbd') {
                                    echo bbdecoder(gdrcd_filter('out', $row['messaggio']), true);

                                } else {
                                    echo gdrcd_bbcoder(gdrcd_filter('out', $row['messaggio']));
                                }

                                ?>
                            </div>
                            <div class="forum_post_modify">
                                <?php
                                if($chiuso == 0 || $_SESSION['permessi'] >= MODERATOR) {
                                    ?>
                                    <a href="main.php?page=forum&op=composer&what=<?php echo gdrcd_filter('num', $_REQUEST['what']); ?>&where=<?php echo gdrcd_filter('num', $_REQUEST['where']); ?>&quote=<?php echo $row['id_messaggio']; ?>">[<?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['quote']); ?>]</a>
                                    <?php
                                }
                                if(($_SESSION['login'] == $row['autore'] && $row['chiuso'] == 0) || ($_SESSION['permessi'] >= MODERATOR)) {
                                    ?>
                                    <a href="main.php?page=forum&op=modifica&what=<?php echo $row['id_messaggio']; ?>">[<?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['edit']); ?>]</a>
                                    <a href="main.php?page=forum&op=delete_conf&id_record=<?php echo $row['id_messaggio']; ?>&padre=<?php echo $row['id_messaggio_padre']; ?>">[<?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['delete']); ?>]</a>
                                    <?php
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                }//while
                gdrcd_query($result, 'free');
                ?>
            </table>
        </div>
        <?php
        if($chiuso == 0 || $_SESSION['permessi'] >= MODERATOR) {
            $padre = gdrcd_filter('num', $_REQUEST['what']);
            $araldo = gdrcd_filter('num', $_REQUEST['where']);
            ?>
            <div class="panels_box">
                <div class="form_gioco">
                    <form action="main.php?page=forum"
                          method="post">
                        <span color="000000">
                            <div class="form_label">
                                Risposta rapida
                            </div>
                            <div class="form_field">
                                <textarea name="messaggio" /></textarea>
                            </div>
                            <div class="form_info">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
                            </div>
                        </span>

                        <div class="form_submit">
                            <input type="hidden" name="op" value="insert" />
                            <input type="hidden" name="araldo" value="<?php echo $araldo; ?>" />
                            <input type="hidden" name="padre" value="<?php echo $padre; ?>" />
                            <input type="submit" name="dummy" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                        </div>
                    </form>
                </div>
            </div>
            <?php
        } //Fine risposta rapida
    }//else
    ?>
    <!-- link a fondo pagina -->
    <div class="link_back">
    <?php
    if($chiuso == 0 || $_SESSION['permessi'] >= MODERATOR) {
        ?>
        <a href="main.php?page=forum&op=composer&what=<?php echo gdrcd_filter('num', $_REQUEST['what']); ?>&where=<?php echo gdrcd_filter('num', $_REQUEST['where']); ?>">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['new_post']); ?>
        </a><br />
        <?php
    }
}//!empty
else {
    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['interface']['forums']['warning']['topic_not_exists']).'</div>';
}
?>
    <a href="main.php?page=forum&op=visit&what=<?php echo $araldo; ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['forum']); ?>
    </a><br />
    </div>