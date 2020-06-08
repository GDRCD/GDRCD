<?php /*HELP: */
$disoccupato = 0;/*Il pg e' affiliato ad una gilda*/
$lavoro = -1;
$jobsn = 0;
$ultimolavoro = strftime("%Y-%m-%d");
$query = "SELECT clgpersonaggioruolo.id_ruolo, clgpersonaggioruolo.scadenza, ruolo.gilda FROM clgpersonaggioruolo LEFT JOIN ruolo ON clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo WHERE clgpersonaggioruolo.personaggio = '".$_SESSION['login']."' ORDER BY ruolo.gilda";
$result = gdrcd_query($query, 'result');

while($jobs = gdrcd_query($result, 'fetch')) {
    $jobsn++;

    if($jobs['gilda'] == -1) {/*Il pg ha un lavoro indipentente*/
        $disoccupato = -1;
        $lavoro = $jobs['id_ruolo'];
        $ultimolavoro = $jobs['scadenza'];
    }//if
}//while
gdrcd_query($result, 'free');
?>
<div class="pagina_servizi_lavoro">
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['job']['page_name']); ?></h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <?php /*Elenco lavori*/
        if(isset($_POST['op']) === false) {
            $query = "SELECT nome_ruolo, immagine, stipendio, id_ruolo FROM ruolo WHERE gilda=-1 ORDER BY nome_ruolo";
            $result = gdrcd_query($query, 'result'); ?>
            <div class="elenco_record_gioco">
                <table>
                    <tr>
                        <td class="casella_titolo" colspan="2">
                            <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['job']['job']); ?></div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['job']['pay']); ?></div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['job']['controls']); ?></div>
                        </td>
                    </tr>
                    <?php
                    while($row = gdrcd_query($result, 'fetch')) { ?>
                        <!--table class="lavori_box" border="1"-->
                        <tr>
                            <td class="casella_elemento_img">
                                <div class="icone_elenco">
                                    <img class="" src="themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/imgs/guilds/<?php echo $row['immagine']; ?>" />
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo $row['nome_ruolo']; ?></div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo $row['stipendio'].' '.gdrcd_filter('out', $PARAMETERS['names']['currency']['plur']); ?></div>
                            </td>
                            <td class="casella_controlli">
                                <div class="controllo_elenco">
                                    <form method="post" action="main.php?page=servizi_lavoro">
                                        <?php
                                        if($ultimolavoro <= strftime("%Y-%m-%d")) {
                                            if($lavoro == $row['id_ruolo']) { ?>
                                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['job']['submit']['quit']); ?>" />
                                                <input type="hidden" name="op" value="resign" />
                                            <?php } else {
                                                if($jobsn < $PARAMETERS['settings']['guilds_limit']) { ?>
                                                    <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['job']['submit']['pick']); ?>" />
                                                    <input type="hidden" name="op" value="pick" />
                                                <?php } else {
                                                    echo '&nbsp;';
                                                }
                                            } ?>
                                            <input type="hidden" name="nome_lavoro" value="<?php echo gdrcd_filter('out', $row['nome_ruolo']); ?>" />
                                            <input type="hidden" name="id_record" value="<?php echo $row['id_ruolo']; ?>" />
                                        <?php
                                        } else {
                                            if($lavoro == $row['id_ruolo']) {
                                                $ultimolavoroexp = explode("-", $ultimolavoro);
                                                echo gdrcd_filter('out', $MESSAGE['interface']['job']['extent']
                                                    )." ".$ultimolavoroexp[2]."-".$ultimolavoroexp[1]."-".$ultimolavoroexp[0];
                                            } else {
                                                echo '&nbsp;';
                                            }
                                        } //else if
                                        ?>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php }//while
                    gdrcd_query($result, 'free');
                    ?>
                    <tr>
                        <td colspan="3">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['job']['disclaimer'])." ".$PARAMETERS['settings']['minimum_employment']; ?>
                        </td>
                    </tr>
                </table>
            </div><!--elenco_record_gioco-->
        <?php
        }//if
        /*Scelta lavoro*/
        if($_POST['op'] == 'pick') {
            if($disoccupato == -1) {
                gdrcd_query("UPDATE clgpersonaggioruolo SET id_ruolo = ".gdrcd_filter('num', $_POST['id_record']).", scadenza = DATE_ADD(NOW(), INTERVAL ".gdrcd_filter('num', $PARAMETERS['settings']['minimum_employment'])." DAY) WHERE personaggio='".$_SESSION['login']."' AND id_ruolo = ".gdrcd_filter('num', $lavoro)." LIMIT 1");
            } else {
                gdrcd_query("INSERT INTO clgpersonaggioruolo (id_ruolo, personaggio, scadenza) VALUES (".gdrcd_filter('num', $_POST['id_record']).", '".$_SESSION['login']."', DATE_ADD(NOW(), INTERVAL ".gdrcd_filter('num', $PARAMETERS['settings']['minimum_employment'])." DAY))");
            }//else

            echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['job']['ok_job']).'</div>';

            gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) VALUES ('".$_SESSION['login']."', '".$_SESSION['login']."', NOW(), ".NUOVOLAVORO.", '".gdrcd_filter_in($_POST['nome_lavoro'])."')");
            ?>
            <div class="link_back">
                <a href="main.php?page=servizi_lavoro"><?php echo gdrcd_filter('out', $MESSAGE['interface']['job']['back']); ?></a>
            </div>
        <?php
        } //if
        /*Dimissioni*/
        if($_POST['op'] == 'resign') {
            gdrcd_query("DELETE FROM clgpersonaggioruolo WHERE personaggio='".$_SESSION['login']."' AND id_ruolo = ".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1");

            echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['job']['ok_quit']).'</div>';
            gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) VALUES ('".$_SESSION['login']."', '".$_SESSION['login']."', NOW(), ".DIMISSIONE.", '".gdrcd_filter('in', $_POST['nome_lavoro'])."')");
            ?>
            <div class="panels_link">
                <a href="main.php?page=servizi_lavoro"><?php echo gdrcd_filter('out', $MESSAGE['interface']['job']['back']); ?></a>
            </div>
        <?php } ?>
    </div>
</div><!-- Box principale -->
