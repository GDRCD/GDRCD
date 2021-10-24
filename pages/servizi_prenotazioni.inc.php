<div class="pagina_servizi_prenotazioni">
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['hotel']['page_name']); ?></h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <?php /*Elenco stanze*/
        if(isset($_POST['op']) === false) { ?>
            <div class="form_gioco">
                <?php
                /*Seleziono i ruoli su cui l'account ha competenza*/
                $query = "SELECT mappa.id, mappa.nome AS luogo, mappa.costo, mappa.proprietario, mappa.scadenza, mappa_click.nome FROM mappa JOIN mappa_click on mappa.id_mappa = mappa_click.id_click WHERE mappa.privata = 1 ORDER BY mappa.nome, mappa.costo DESC";
                $result = gdrcd_query($query, 'result');

                if((gdrcd_query($result, 'num_rows') == 0) || ($PARAMETERS['mode']['privaterooms'] == 'OFF')) {
                    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['hotel']['no_room']).'</div>';
                } else { ?>
                    <form action="main.php?page=servizi_prenotazioni"
                          method="post">
                        <div class="form_label">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['hotel']['room']); ?>
                        </div>
                        <div class="form_element">
                            <select name="id">
                                <?php
                                while($row = gdrcd_query($result, 'fetch')) { ?>
                                    <?php if($row['scadenza'] > strftime('%Y-%m-%d %H:%M:%S')) { ?>
                                        <option value="" disabled>
                                            <?php echo gdrcd_filter('out', $row['luogo'].', '.$row['nome']
                                                ).' ('.$row['proprietario'].', '.gdrcd_format_time($row['scadenza']).') '; ?>
                                        </option>
                                    <?php } else { ?>
                                        <option value="<?php echo $row['id'].'-'.$row['costo']; ?>">
                                            <?php echo gdrcd_filter('out', $row['luogo'].', '.$row['nome']
                                                ).' ('.$row['costo'].' '.strtolower($PARAMETERS['names']['currency']['plur']
                                                ).' '.$MESSAGE['interface']['hotel']['per_hour'].') '; ?>

                                        </option>
                                    <?php } ?>
                                <?php }//while
                                gdrcd_query($result, 'free');
                                ?>
                            </select>
                            <select name="ore">
                                <?php
                                for($i = 1; $i <= 12; $i++) { ?>
                                    <option value="<?php echo $i; ?>">
                                        <?php echo $i.' '.gdrcd_filter('out', $MESSAGE['interface']['hotel']['hours']); ?>
                                    </option>
                                <?php }//while
                                ?>
                            </select>
                        </div>
                        <div class="form_submit">
                            <input type="hidden" name="op" value="book" />
                            <input type="submit" name="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                        </div>
                    </form>
                <?php }//else
                ?>
            </div>
        <?php
        }
        /*Prenota*/
        if(gdrcd_filter('get', $_POST['op']) == 'book') {
            if(empty($_POST['id']) === true) {
                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['cant_do']).'</div>';
            } else {
                $id = explode('-', $_POST['id']);

                $soldi = gdrcd_query("SELECT soldi FROM personaggio WHERE nome ='".$_SESSION['login']."' LIMIT 1");
                $ore = gdrcd_filter('num', $_POST['ore']);
                if($ore < 0) {
                    $ore = 1;
                }
                if($id[1] < 1) {
                    $id[1] = 1;
                }
                if($soldi['soldi'] >= ($ore * $id[1])) {
                    /*Opero la prenotazione*/
                    gdrcd_query("UPDATE mappa SET proprietario = '".$_SESSION['login']."', invitati='', ora_prenotazione=NOW(), scadenza=DATE_ADD(NOW(), INTERVAL ".$_POST['ore']." HOUR) WHERE id = ".gdrcd_filter('num', $id[0])." and scadenza < NOW() LIMIT 1");

                    gdrcd_query("UPDATE personaggio SET soldi = soldi - ".gdrcd_filter('num', $ore * $id[1])." WHERE nome = '".$_SESSION['login']."' LIMIT 1");

                    /** * Al fine di conservare i log delle stanze private elimino la query che svuota le azioni mandate nella chat precedente
                     * @author Blancks
                     */
                    #gdrcd_query("DELETE FROM chat WHERE stanza = ".gdrcd_filter('num',$id[0])."");
                    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['hotel']['ok']).'</div>';
                } else {
                    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['interface']['hotel']['no_bucks']).'</div>';
                }
            }//else
            ?>
            <div class="panels_link">
                <a href="main.php?page=servizi_prenotazioni"><?php echo gdrcd_filter('out', $MESSAGE['interface']['hotel']['back']); ?></a>
            </div>
        <?php } ?>
    </div>
</div><!-- Box principale -->

