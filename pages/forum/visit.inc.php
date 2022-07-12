<?php
//Permessi
$row = gdrcd_query("SELECT tipo, proprietari FROM araldo WHERE id_araldo = ".gdrcd_filter('num', $_REQUEST['what'])."");

if(!gdrcd_controllo_permessi_forum($row['tipo'],$row['proprietari'])){
    /*Restrizione di visualizzazione solo master e admin*/
    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    ?>
    <div class="link_back">
        <a href="main.php?page=forum">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['back']); ?>
        </a>
    </div>
    <?php
} else {
    /*
     * Procedure messaggi importanti e chiusi
     * @author Blancks <s.rotondo90@gmail.com>
     */
    if($_SESSION['permessi'] >= MODERATOR) {
        switch($_POST['ops']) {
            case 'important':
                $id_record = (int) $_POST['id_record'];
                $status_imp = (int) $_POST['status_imp'];

                gdrcd_query("UPDATE messaggioaraldo SET importante = $status_imp WHERE id_messaggio = $id_record") or die(mysql_error());

                break;

            case 'close':
                $id_record = (int) $_POST['id_record'];
                $status_cls = (int) $_POST['status_cls'];

                gdrcd_query("UPDATE messaggioaraldo SET chiuso = $status_cls WHERE id_messaggio = $id_record") or die(mysql_error());

                break;
        }
    }
    /*
     *  Fine Procedura per topic importanti/chiusi
     */
    //Determinazione pagina (paginazione)
    $pagebegin = (int) $_REQUEST['offset'] * $PARAMETERS['settings']['posts_per_page'];
    $pageend = $pagebegin + $PARAMETERS['settings']['posts_per_page'];

    //Conteggio record totali
    $record_globale = gdrcd_query("SELECT COUNT(*) FROM messaggioaraldo WHERE id_messaggio_padre = -1 AND id_araldo = ".gdrcd_filter('num', $_REQUEST['what']));
    $totaleresults = $record_globale['COUNT(*)'];

    /*Carico l'elenco dei forum*/
    $result = gdrcd_query("SELECT MA.id_messaggio, MA.titolo, MA.autore, MA.data_messaggio, MA.data_ultimo_messaggio, MA.importante, MA.chiuso, AL.id AS new_msg FROM messaggioaraldo AS MA LEFT JOIN araldo_letto AS AL ON MA.id_messaggio=AL.thread_id AND AL.nome='".$_SESSION['login']."' WHERE MA.id_messaggio_padre = -1 AND MA.id_araldo = ".gdrcd_filter('num', $_REQUEST['what'])." ORDER BY MA.importante DESC, MA.data_ultimo_messaggio DESC LIMIT ".$pagebegin.", ".$PARAMETERS['settings']['posts_per_page']."", 'result');

    if(gdrcd_query($result, 'num_rows') == 0) {
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['forums']['warning']['no_topic']).'</div>';
    } else {
        ?>
        <!-- Elenco forum -->
        <div class="elenco_esteso">
            <div class="elenco_record_gioco">
                <table>
                    <tr><!-- Intestazione tabella -->
                        <?php if($_SESSION['permessi'] >= MODERATOR) {
                        ?>
                        <td colspan="4">
                        <?php } else  { ?>
                        <td colspan="3">
                        <?php } ?>
                            <div class="capitolo_elenco">
                                <?php echo gdrcd_filter('get', $_REQUEST['nome']); ?>
                            </div>
                        </td>
                    </tr>
                    <tr><!-- Intestazione tabella -->
                        <td class="casella_titolo">
                            <div class="capitolo_elenco">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['topic']['title']); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="capitolo_elenco">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['topic']['author']); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="capitolo_elenco">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['topic']['posts']); ?>
                            </div>
                        </td>
                        <?php
                        if($_SESSION['permessi'] >= MODERATOR) {  ?>
                            <td class="casella_titolo">
                                <div class="capitolo_elenco">
                                    <?php echo '&nbsp;'; ?>
                                </div>
                            </td>
                            <?php
                        } ?>
                    </tr>
                    <?php
                    while($row = gdrcd_query($result, 'fetch')) {
                        $readinfo = gdrcd_query("SELECT MAX(data_messaggio) AS latest, COUNT(*) AS replies FROM messaggioaraldo WHERE id_messaggio_padre = ".gdrcd_filter('get', $row['id_messaggio']));
                        $lastupdate = $readinfo['latest'];
                        $postsnumber = $readinfo['replies'];
                        ?>
                        <tr><!-- Topic -->
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><!-- Titolo -->
                                    <a href="main.php?page=forum&op=read&what=<?php echo gdrcd_filter('out', $row['id_messaggio']
                                    ); ?>&where=<?php echo gdrcd_filter('num', $_REQUEST['what']); ?>">
                                        <div class="forum_column">
                                            <?php
                                            /**    * Topic importante
                                             * @author Blancks <s.rotondo90@gmail.com>
                                             */
                                            echo ($row['importante']) ? $MESSAGE['interface']['administration']['ops']['important'].': ' : '';
                                            /**    * Fine
                                             */
                                            echo gdrcd_filter('out', $row['titolo']);

                                            if($row['new_msg'] == 0) {
                                                echo '('.$MESSAGE['interface']['forums']['topic']['new_posts']['plur'].')';
                                            }
                                            ?>
                                        </div>
                                    </a>
                                    <?php
                                    /**    * Topic Chiuso
                                     * @author Blancks <s.rotondo90@gmail.com>
                                     */
                                    echo ($row['chiuso']) ? '<div class="forum_column">'.$MESSAGE['interface']['forums']['topic']['title'].' '.$MESSAGE['interface']['administration']['ops']['close'].'</div>' : '';
                                    /**    * Fine
                                     */
                                    ?>
                                    <div class="forum_date_big"><?php echo gdrcd_format_date($row['data_messaggio']).' '.gdrcd_format_time($row['data_messaggio']); ?></div>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><!-- Autore -->
                                    <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('out', $row['autore']); ?>">
                                        <?php echo gdrcd_filter('out', $row['autore']); ?>
                                    </a>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><!-- Data -->
                                    <?php echo $postsnumber.' '.gdrcd_filter('out', $MESSAGE['interface']['forums']['topic']['posts']); ?>
                                    <div class="forum_date_big">
                                        <?php if($postsnumber > 0) {
                                            echo gdrcd_filter('out', $MESSAGE['interface']['forums']['topic']['last_post']).':   '.gdrcd_format_date($lastupdate).' '.gdrcd_format_time($lastupdate);
                                        } ?>
                                    </div>
                                </div>
                            </td>
                            <?php
                            if($_SESSION['permessi'] >= MODERATOR) {
                                /**    * Topic importanti/chiusi
                                 * @author Blancks <s.rotondo90@gmail.com>
                                 */
                                $set_imp = ($row['importante']) ? '0' : '1';
                                $set_cls = ($row['chiuso']) ? '0' : '1';

                                $img_imp = ($row['importante']) ? 'importante.png' : 'non_importante.png';
                                $img_cls = ($row['chiuso']) ? 'topic_chiuso.png' : 'topic_aperto.png';

                                $label_imp = ($row['importante']) ? 'important' : 'not_important';
                                $label_cls = ($row['chiuso']) ? 'close' : 'open';

                                /**    * Fine
                                 */
                                ?>
                                <td class="casella_titolo">
                                    <div class="controlli_elenco"><!-- controlli -->

                                                                  <!--
                                                                  /**	* Topic importanti/chiusi
                                                                      * @author Blancks <s.rotondo90@gmail.com>
                                                                  */
                                                                  -->

                                                                  <!-- Importante -->
                                        <div class="controllo_elenco">
                                            <form action="main.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
                                                <input type="hidden" name="id_record" value="<?php echo $row['id_messaggio'] ?>" />
                                                <input type="hidden" name="status_imp" value="<?php echo $set_imp; ?>" />
                                                <input type="hidden" name="ops" value="important" />
                                                <input type="image" src="imgs/icons/<?php echo $img_imp; ?>"
                                                       alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops'][$label_imp]); ?>"
                                                       title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops'][$label_imp]); ?>" />
                                            </form>
                                        </div>
                                                                  <!-- Topic Chiuso -->
                                        <div class="controllo_elenco">
                                            <form action="main.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
                                                <input type="hidden" name="id_record" value="<?php echo $row['id_messaggio'] ?>" />
                                                <input type="hidden" name="status_cls" value="<?php echo $set_cls; ?>" />
                                                <input type="hidden" name="ops" value="close" />
                                                <input type="image" src="imgs/icons/<?php echo $img_cls; ?>"
                                                       alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops'][$label_cls]); ?>"
                                                       title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops'][$label_cls]); ?>" />
                                            </form>
                                        </div>
                                                                  <!-- Elimina -->
                                        <div class="controllo_elenco">
                                            <a href="main.php?page=forum&op=delete_conf&id_record=<?php echo $row['id_messaggio']; ?>&padre=-1">
                                                <img src="imgs/icons/erase.png" alt="Elimina" width="15" />
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                        <?php
                    }//while
                    gdrcd_query($result, 'free');
                    ?>
                </table>
            </div>
        </div>
        <?php
    }//else
    ?>
    <!-- Paginatore elenco -->
    <div class="pager">
        <?php
        if($totaleresults > $PARAMETERS['settings']['posts_per_page']) {
            echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
            for($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['posts_per_page']); $i++) {
                if($i != $_REQUEST['offset']) {
                    ?>
                    <a href="main.php?page=forum&op=visit&what=<?php echo gdrcd_filter('num', $_REQUEST['what']
                    ) ?>&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                    <?php
                } else {
                    echo ' '.($i + 1).' ';
                }
            } //for
        }//if
        ?>
    </div>

    <!-- link crea nuovo -->
    <div class="link_back">
        <a href="main.php?page=forum&op=composer&what=-1&where=<?php echo gdrcd_filter('num', $_REQUEST['what']); ?>">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['new_topic']); ?>
        </a><br />
        <a href="main.php?page=forum">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['back']); ?>
        </a>
    </div>
    <?php
} //else