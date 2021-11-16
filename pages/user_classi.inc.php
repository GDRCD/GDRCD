<div class="user_razze">
    <div class="page_title"><h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['classes']['page_name'].' '.strtolower($PARAMETERS['names']['class']['plur'])); ?></h2>
    </div>
    <div class="page_body">
        <?php /*HELP: */
        $query = "SELECT nome_classe, sing_m, sing_f, descrizione, url_site, bonus_car0, bonus_car1, bonus_car2, bonus_car3, bonus_car4, bonus_car5, immagine, icon  FROM classe WHERE visibile = 1 ORDER BY nome_classe";
        $result = gdrcd_query($query, 'result'); ?>
        <div class="panels_box">
            <div class="elenco_record_gioco">
                <table>
                    <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                        <tr>
                            <td colspan="2" class="casella_titolo">
                                <div class="elementi_elenco">
                                    <img class="classe_icon" src="themes/<?php echo $PARAMETERS['themes']['current_theme'] ?>/imgs/classes/<?php echo $row['immagine']; ?>" />
                                    <?php if(empty($row['url_site']) === true) {
                                        echo $row['nome_classe'].' ('.$row['sing_m'].', '.$row['sing_f'].')';
                                    } else {
                                        echo '<a href="http://'.$row['url_site'].'">'.gdrcd_filter('out', $row['nome_classe']).'</a> ('.gdrcd_filter('out', $row['sing_m']).', '.gdrcd_filter('out', $row['sing_f']).')';
                                    } ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="casella_classe_immagine">
                                <div class="elementi_elenco">
                                    <?php if(empty($row['immagine']) === true) {
                                        echo '&nbsp;';
                                    } else { ?>
                                        <img class="classe_immagine" src="themes/<?php echo $PARAMETERS['themes']['current_theme'] ?>/imgs/classes/<?php echo $row['immagine']; ?>" />
                                    <?php } ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_bbcoder(gdrcd_filter('out', $row['descrizione'])); ?>
                                </div>
                                <div class="elementi_elenco">
                                    <?php echo $MESSAGE['interface']['user']['classes']['bonus'].': '.$PARAMETERS['names']['stats']['car0'].' '.$row['bonus_car0'].', '.$PARAMETERS['names']['stats']['car1'].' '.$row['bonus_car1'].', '.$PARAMETERS['names']['stats']['car2'].' '.$row['bonus_car2'].', '.$PARAMETERS['names']['stats']['car3'].' '.$row['bonus_car3'].', '.$PARAMETERS['names']['stats']['car4'].' '.$row['bonus_car4'].', '.$PARAMETERS['names']['stats']['car5'].' '.$row['bonus_car5'].'.'; ?>
                                </div>
                            </td>
                        </tr>
                    <?php
                    }//while
                    gdrcd_query($result, 'free');
                    ?>
                </table>
            </div>
            <!--elenco_record_gioco-->
        </div>
        <!--panels_box-->
    </div>
</div><!-- Box principale -->
