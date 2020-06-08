<div class="user_abilita">
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['skills']['page_name']); ?></h2>
    </div>
    <div class="page_body">
        <?php
        $query = "SELECT nome, car, descrizione FROM abilita ORDER BY nome";
        $result = gdrcd_query($query, 'result'); ?>
        <div class="panels_box">
            <div class="elenco_record_gioco">
                <table>
                    <tr>
                        <td class="casella_titolo">
                            <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['skills']['skill']); ?></div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['skills']['car']); ?></div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['skills']['desc']); ?></div>
                        </td>
                    </tr>
                    <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                        <tr>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['nome']); ?></div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car'.$row['car']]); ?></div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_bbcoder(gdrcd_filter('out', $row['descrizione'])); ?></div>
                            </td>
                        </tr>
                        <?php
                    }//while
                    gdrcd_query($result, 'free');
                    ?>
                    <tr>
                        <td colspan="3">
                            <div class="page_title"><h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['skills']['sys_tit']); ?></h2></div>
                            <div style='text-align: justify'><?php echo gdrcd_filter('out', $MESSAGE['interface']['skills']['sys']); ?></div>
                        </td>
                    </tr>
                </table>
            </div>
            <!--elenco_record_gioco-->
        </div>
        <!--panels_box-->
    </div>
</div><!-- Box principale -->

