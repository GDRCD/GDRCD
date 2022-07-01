<div class="user_ambientazione">
    <div class="page_title"><h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['plot']['page_name']); ?></h2></div>
    <div class="page_body">
        <?php /*HELP: */
        $query = "SELECT capitolo, titolo, testo FROM ambientazione ORDER BY capitolo";
        $result = gdrcd_query($query, 'result'); ?>
        <div class="panels_box">
            <div class="elenco_record_gioco">
                <table>
                    <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                        <tr>
                            <td class="casella_titolo">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['capitolo']); ?></div>
                            </td>
                            <td class="casella_titolo">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['titolo']); ?></div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_bbcoder(gdrcd_filter('out', $row['testo'])); ?></div>
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

