<?php
/*Carico l'elenco dei forum*/
$result = gdrcd_query("SELECT id_araldo, nome, tipo, proprietari FROM araldo ORDER BY tipo, nome", 'result');

$ultimotipo = -1;
?>
<!-- Elenco forum -->
<div class="elenco_esteso">
    <div class="elenco_record_gioco">
        <table>
            <?php
            while($row = gdrcd_query($result, 'fetch')) {

                if(!gdrcd_controllo_permessi_forum(gdrcd_filter('out',$row['tipo']),$row['proprietari'])){
                    continue;
                }

                if($row['tipo'] != $ultimotipo) {
                    /*Sono ordinati per tipo, se cambia stampo il nuovo tipo come capoverso*/
                    $ultimotipo = $row['tipo'];
                    ?>
                    <tr><!-- Intestazione tabella -->
                        <td colspan="2">
                            <div class="capitolo_elenco">
                                <?php echo gdrcd_filter('out', $PARAMETERS['names']['forum']['plur'].' '.strtolower($MESSAGE['interface']['forums']['type'][$ultimotipo])); ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                } //if

                $new_msg = gdrcd_query("SELECT COUNT(id) AS num FROM araldo_letto WHERE araldo_id = ".$row['id_araldo']." AND nome = '".$_SESSION['login']."';");
                $new_msg2 = gdrcd_query("SELECT COUNT(id_messaggio) AS num FROM messaggioaraldo WHERE id_araldo = ".$row['id_araldo']." AND id_messaggio_padre = -1");

                ?>
                <tr><!-- Forum della categoria -->
                    <td class="forum_main_post_author">
                        <div class="forum_date_big">
                            <?php
                            if($new_msg2['num'] > $new_msg['num']) {
                                echo $MESSAGE['interface']['forums']['topic']['new_posts_forum'];
                            }
                            ?>
                        </div>
                    </td>
                    <td class="casella_elemento">
                        <div class="elementi_elenco">
                            <a href="main.php?page=forum&op=visit&what=<?php echo gdrcd_filter('out', $row['id_araldo']); ?>&name=<?php echo gdrcd_filter('out', $row['nome']); ?>">
                                <?php echo gdrcd_filter('out', $row['nome']); ?>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php
            }//while
            gdrcd_query($result, 'free');
            ?>
        </table>
        <?php //Pulsante segna tutto come letto ?>
        <div class="panels_box">
            <div class="form_gioco">
                <form action="main.php?page=forum" method="post">
                    <div class="form_submit">
                        <input type="hidden" name="op" value="readall" />
                        <input type="submit" name="dummy" value="Segna tutto come letto" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
