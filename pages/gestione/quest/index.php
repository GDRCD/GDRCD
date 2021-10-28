<?php
if (isset($_POST['op'])===FALSE){
//Definisco la lista di quest accessibile all'account in base ai permessi

//Determinazione pagina (paginazione)
$pagebegin=(int)$_REQUEST['offset']*$PARAMETERS['settings']['records_per_page'];
$pageend=$PARAMETERS['settings']['records_per_page'];

if (Functions::get_constant('QUEST_VIEW_OTHER')) {
//Conteggio record totali
    $record_globale = gdrcd_query("SELECT COUNT(*) FROM quest");
//Lettura record
    $result = gdrcd_query("SELECT * FROM quest ORDER BY trama, data DESC LIMIT " . $pagebegin . ", " . $pageend . "", 'result');
} else {
    //Conteggio record totali
        $record_globale = gdrcd_query("SELECT COUNT(*) FROM quest WHERE autore = '".gdrcd_filter('in',$_SESSION['login'])."' ");
    //Lettura record
        $result = gdrcd_query("SELECT * FROM quest WHERE autore = '".gdrcd_filter('in',$_SESSION['login'])."' 
        ORDER BY trama, data DESC LIMIT " . $pagebegin . ", " . $pageend . "", 'result');
}
$totaleresults = $record_globale['COUNT(*)'];
$numresults=gdrcd_query($result, 'num_rows'); ?>

<!-- link crea nuovo -->
<div class="link_back">
    <a href="main.php?page=gestione_quest&op=new_quest">
		Registra nuova quest
    </a>
</div>
<?php if ($_SESSION['permessi'] >= Functions::get_constant('TRAME_VIEW')  && Functions::get_constant('TRAME_ENABLED')) { ?>
    <div class="link_back" >
        <a href = "main.php?page=gestione_quest&op=lista_trame" >
        Lista delle trame
        </a >
    </div >
<?php
    }
/* Se esistono record */
if ($numresults>0){ ?>
    <!-- Elenco dei record paginato -->
    <div class="elenco_record_gestione">
        <table>
            <!-- Intestazione tabella -->
            <tr>
                <td class="casella_titolo"><div class="titoli_elenco">Data</div></td>
                <td class="casella_titolo"><div class="titoli_elenco">Titolo</div></td>
                <td class="casella_titolo"><div class="titoli_elenco">Autore</div></td>
                <td class="casella_titolo"><div class="titoli_elenco">Partecipanti</div></td>
                <?php if ($_SESSION['permessi'] >= Functions::get_constant('TRAME_VIEW')){
                    echo '<td class="casella_titolo"><div class="titoli_elenco">Trama</div></td>';
                } ?>
                <td class="casella_titolo"><div class="titoli_elenco">Ultima modifica</div></td>
                <td class="casella_titolo">
                    <div class="titoli_elenco">
                        <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops_col']); ?>
                    </div>
                </td>
            </tr>
            <!-- Record -->
            <?php while ($row=gdrcd_query($result, 'fetch')){ ?>
                <tr class="risultati_elenco_record_gestione">
                    <td class="casella_elemento">
                        <div class="elementi_elenco">
                            <?php echo gdrcd_format_date($row['data']); ?>
                        </div>
                    </td>
                    <td class="casella_elemento">
                        <div class="elementi_elenco">
                            <?php echo $row['titolo']; ?>
                        </div>
                    </td>
                    <td class="casella_elemento">
                        <div class="elementi_elenco">
                            <?php echo $row['autore']; ?>
                        </div>
                    </td>
                    <td class="casella_elemento">
                        <div class="elementi_elenco">
                            <?php echo gdrcd_filter('out',$row['partecipanti']); ?>
                        </div>
                    </td>
                    <?php if ($_SESSION['permessi'] >= Functions::get_constant('TRAME_VIEW')){
                        $quer="SELECT * FROM trama WHERE id = '".$row['trama']."' ";
                        $res=gdrcd_query($quer, 'result');
                        $rec=gdrcd_query($res, 'fetch');
                    echo '<td class="casella_elemento"><div class="elementi_elenco">';
                    echo gdrcd_filter('out',$rec['titolo']);
                    echo '</div></td>';
                    } ?>
                    <td class="casella_elemento">
                        <div class="elementi_elenco">
                            <?php echo $row['ultima_modifica']; ?>
                        </div>
                    </td>

                    <td class="casella_controlli"><!-- Iconcine dei controlli -->
                        <!-- Modifica -->
                        <div class="controlli_elenco">
                            <div class="controllo_elenco" >
                                <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_quest" method="post">
                                    <input type="hidden" name="id_record" value="<?php echo $row['id']?>" />
                                    <input type="hidden" name="op" value="edit_quest" />
                                    <input type="image"
                                           src="imgs/icons/edit.png"
                                           alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['edit']); ?>"
                                           title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                                </form>
                            </div>
                            <!-- Elimina -->
                            <div class="controllo_elenco" >
                                <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_quest" method="post">
                                    <input type="hidden" name="id_record" value="<?php echo $row['id']?>" />
                                    <input type="hidden" name="op" value="delete_quest" />
                                    <input type="image"
                                           src="imgs/icons/erase.png"
                                           onclick="return confirm('Vuoi davvero cancellare questa quest? Non potrà più essere recuperata')"
                                           alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['erase']); ?>"
                                           title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['erase']); ?>"/>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="6" class="casella_elemento">
                        <div class="elementi_elenco">
                            <?php echo gdrcd_filter('out',$row['descrizione']); ?>
                        </div>
                    </td>
                </tr>
            <?php } //while

            gdrcd_query($result, 'free');
            ?>
        </table>
    </div>
<?php }//if ?>

    <!-- Paginatore elenco -->
    <div class="pager">
        <?php if($totaleresults>$PARAMETERS['settings']['records_per_page']){
            echo gdrcd_filter('out',$MESSAGE['interface']['pager']['pages_name']);
            for($i=0;$i<=floor($totaleresults/$PARAMETERS['settings']['records_per_page']);$i++){
                if ($i!=gdrcd_filter('num',$_REQUEST['offset'])){?>
                    <a href="main.php?page=gestione_quest&offset=<?php echo $i; ?>"><?php echo $i+1; ?></a>
                <?php } else { echo ' '.($i+1).' '; }
            } //for
        }//if ?>
    </div>
<?php
}
?>