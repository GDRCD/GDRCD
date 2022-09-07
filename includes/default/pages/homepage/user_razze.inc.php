<div class="user_razze">
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['races']['page_name'] . ' ' . strtolower($PARAMETERS['names']['race']['plur'])); ?></h2>
    </div>
    <div class="page_body">
        <?php /*HELP: */
        $query = "SELECT nome, sing_m, sing_f, descrizione, url_site, immagine, icon  FROM razze WHERE visibile = 1 ORDER BY nome";
        $result = gdrcd_query($query, 'result'); ?>
        <div class="panels_box">
            <div class="elenco_record_gioco">
                <table>
                    <?php while ( $row = gdrcd_query($result, 'fetch') ) { ?>
                        <tr>
                            <td colspan="2" class="casella_titolo">
                                <div class="elementi_elenco">
                                    <img class="razza_icon"
                                         alt="<?php echo gdrcd_filter('out', $row['nome']); ?>"
                                         src="themes/<?php echo $PARAMETERS['themes']['current_theme'] ?>/imgs/races/<?php echo $row['icon']; ?>"/>
                                    <?php if ( empty($row['url_site']) === true ) {
                                        echo $row['nome'] . ' (' . $row['sing_m'] . ', ' . $row['sing_f'] . ')';
                                    } else {
                                        echo '<a href="http://' . $row['url_site'] . '">' . gdrcd_filter('out', $row['nome']) . '</a> (' . gdrcd_filter('out', $row['sing_m']) . ', ' . gdrcd_filter('out', $row['sing_f']) . ')';
                                    } ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="casella_razza_immagine">
                                <div class="elementi_elenco">
                                    <?php if ( empty($row['immagine']) === true ) {
                                        echo '&nbsp;';
                                    } else { ?>
                                        <img class="razza_immagine"
                                             src="themes/<?php echo $PARAMETERS['themes']['current_theme'] ?>/imgs/races/<?php echo $row['immagine']; ?>"/>
                                    <?php } ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <h4>Descrizione:</h4>
                                    <p><?=gdrcd_bbcoder(gdrcd_filter('out', $row['descrizione']));?></p>
                                </div>
                            </td>
                        </tr>
                    <?php }//while
                    gdrcd_query($result, 'free');
                    ?>
                </table>
            </div>
            <!--elenco_record_gioco-->
        </div>
        <!--panels_box-->
    </div>
</div><!-- Box principale -->
