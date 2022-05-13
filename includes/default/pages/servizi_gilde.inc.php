<div class="pagina_servizi_gilde">
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $PARAMETERS['names']['guild_name']['plur']); ?></h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <?php /*Visualizzaione elenco gilde*/
        if(isset($_REQUEST['id_gilda']) === false) {
            $query = "SELECT gilda.nome, gilda.id_gilda, gilda.tipo, gilda.immagine, codtipogilda.descrizione FROM gilda JOIN codtipogilda ON gilda.tipo = codtipogilda.cod_tipo WHERE gilda.visibile = 1 ORDER BY gilda.tipo, gilda.nome";
            $result = gdrcd_query($query, 'result');

            $last_type = -1; ?>
            <div class="elenco_breve">
                <div class="elenco_record_gioco">
                    <table>
                        <?php
                        while($row = gdrcd_query($result, 'fetch')) {
                            /*Conteggio i membri di gilda*/
                            $numb = gdrcd_query("SELECT COUNT(*) FROM clgpersonaggioruolo JOIN ruolo ON clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo WHERE ruolo.gilda = ".$row['id_gilda']."");
                            /*Stampo la riga dell'allineamento gilde*/
                            if($row['tipo'] != $last_type) { ?>
                                <tr>
                                    <td colspan="3">
                                        <div class="capitolo_elenco">
                                            <?php
                                            /** * Ometto la dicitura "Allineamento:" cos� che il campo consenta pi� libert� di modifica.
                                             * @author Blancks
                                             */
                                            #echo gdrcd_filter('out',$PARAMETERS['names']['guild_name']['type']).": ";
                                            echo gdrcd_filter('out', $row['descrizione']); ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="titoli_elenco">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="titoli_elenco">
                                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['guild_name']['sing']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="titoli_elenco">
                                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['guild_name']['members']); ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php $last_type = $row['tipo'];
                            } ?>
                            <!--Elenco gilde-->
                            <tr>
                                <td>
                                    <div class="icone_elenco">
                                        <img src="themes/<?php echo gdrcd_filter('out', $PARAMETERS['themes']['current_theme']); ?>/imgs/guilds/<?php echo gdrcd_filter('out', $row['immagine']); ?>" />
                                    </div>
                                </td>
                                <td>
                                    <div class="elementi_elenco">
                                        <a href="main.php?page=servizi_gilde&id_gilda=<?php echo $row['id_gilda']; ?>">
                                            <?php echo gdrcd_filter('out', $row['nome']); ?>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <div class="elementi_elenco">
                                        <?php echo $numb['COUNT(*)'];; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }//while
                        gdrcd_query($result, 'free');
                        ?>
                    </table>
                </div>
            </div><!--elenco_breve-->
            <?php /*Visualizzazione estesa gilda*/
        } else {
            /*elenco ruoli*/
            $query = "SELECT nome_ruolo, immagine, stipendio, capo FROM ruolo WHERE gilda = ".gdrcd_filter('num', $_REQUEST['id_gilda'])." ORDER BY capo DESC, stipendio DESC";
            $result = gdrcd_query($query, 'result'); ?>

            <div class="elenco_esteso">
                <div class="elenco_record_gioco">
                    <table>
                        <tr>
                            <td colspan="4">
                                <!-- Titoletto -->
                                <div class="capitolo_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['guilds']['roles_title']['plur']); ?></div>
                            </td>
                        <tr>
                        <tr>
                            <td>
                                <div class="titoli_elenco">
                                    &nbsp;
                                </div>
                            </td>
                            <td>
                                <div class="titoli_elenco">
                                    &nbsp;
                                </div>
                            </td>
                            <td>
                                <div class="titoli_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['guilds']['roles_title']['sing']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="titoli_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['guilds']['pay']); ?>
                                </div>
                            </td>
                            <!-- Elenco -->
                            <?php while($row = gdrcd_query($result, 'fetch'))
                            { ?>
                        <tr>
                            <td>
                                <div class="icone_elenco">
                                    <?php if($row['capo'] == 1) { ?><img
                                            src="imgs/icons/crown1.gif" /><?php } else { ?>&nbsp;<?php } ?>
                                </div>
                            </td>
                            <td>
                                <div class="icone_elenco">
                                    <img src="themes/<?php echo gdrcd_filter('out', $PARAMETERS['themes']['current_theme']); ?>/imgs/guilds/<?php echo gdrcd_filter('out',
                                                                                                                                                                    $row['immagine']
                                    ); ?>" />
                                </div>
                            </td>
                            <td>
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $row['nome_ruolo']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $row['stipendio']." ".$PARAMETERS['names']['currency']['plur']); ?>
                                </div>
                            </td>
                        </tr>
                        <?php }
                        gdrcd_query($result, 'free');
                        ?>
                    </table>
                    <?php /*Elenco affiliati*/
                    $query = "SELECT clgpersonaggioruolo.personaggio, personaggio.cognome, ruolo.immagine, ruolo.capo, ruolo.nome_ruolo FROM ruolo JOIN clgpersonaggioruolo ON clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo JOIN personaggio ON personaggio.nome = clgpersonaggioruolo.personaggio WHERE ruolo.gilda = ".gdrcd_filter('num', $_REQUEST['id_gilda'])." ORDER BY ruolo.capo DESC, ruolo.stipendio DESC";
                    $result = gdrcd_query($query, 'result'); ?>
                    <table>
                        <tr>
                            <td colspan="4">
                                <!-- Titoletto -->
                                <div class="capitolo_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['guilds']['members']); ?></div>
                            </td>
                        <tr>
                        <tr>
                            <td>
                                <div class="titoli_elenco">
                                    &nbsp;
                                </div>
                            </td>
                            <td>
                                <div class="titoli_elenco">
                                    &nbsp;
                                </div>
                            </td>
                            <td>
                                <div class="titoli_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['guilds']['member']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="titoli_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['guilds']['roles_title']['sing']); ?>
                                </div>
                            </td>
                            <!-- Elenco -->
                            <?php while($row = gdrcd_query($result, 'fetch'))
                            { ?>
                        <tr>
                            <td>
                                <div class="icone_elenco">
                                    <?php if($row['capo'] == 1) { ?><img
                                            src="imgs/icons/crown1.gif" /><?php } else { ?>&nbsp;<?php } ?>
                                </div>
                            </td>
                            <td>
                                <div class="icone_elenco">
                                    <img src="themes/<?php echo gdrcd_filter('out', $PARAMETERS['themes']['current_theme']); ?>/imgs/guilds/<?php echo gdrcd_filter('out', $row['immagine']); ?>" />
                                </div>
                            </td>
                            <td>
                                <div class="elementi_elenco">
                                    <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('out', $row['personaggio']); ?>">
                                        <?php echo gdrcd_filter('out', $row['personaggio'].' '.$row['cognome']); ?>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $row['nome_ruolo']); ?>
                                </div>
                            </td>
                        </tr>
                        <?php }
                        gdrcd_query($result, 'free');
                        ?>
                    </table>
                </div>
            </div><!--elenco_breve-->

            <?php /*statuto*/
            $statuto = gdrcd_query("SELECT statuto FROM gilda WHERE id_gilda = ".gdrcd_filter('num', $_REQUEST['id_gilda'])."");

            if(empty($statuto['statuto']) === false) { ?>
                <table>
                    <tr>
                        <td colspan="4">
                            <!-- Titoletto -->
                            <div class="capitolo_elenco">Statuto</div>
                        </td>
                    <tr>
                    <tr>
                        <td>
                            <div style="text-align: justify;">
                                <?php echo gdrcd_bbcoder(gdrcd_filter('out', $statuto['statuto'])); ?>
                            </div>
                        </td>
                    </tr>
                </table>
            <?php } ?>
            <div class="link_back">
                <a href="main.php?page=servizi_gilde"><?php echo gdrcd_filter('out', $MESSAGE['interface']['guilds']['back']); ?></a>
            </div>
        <?php } ?>
    </div>
    <!-- Box principale -->
</div>
