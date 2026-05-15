 <?php
$pagebegin = gdrcd_filter('int', $_REQUEST['offset']) * $PARAMETERS['settings']['records_per_page'];
$pageend = $PARAMETERS['settings']['records_per_page'];
//Conteggio record totali

$record_globale = gdrcd_stmt_one("SELECT COUNT(*) FROM ambientazione");
$totaleresults = $record_globale['COUNT(*)'];
//Lettura record
$result = gdrcd_stmt_all("SELECT * FROM ambientazione ORDER BY capitolo LIMIT ".$pagebegin.", ".$pageend."");
?>
    <!-- Elenco dei record paginato -->
    <div class="elenco_record_gestione">
        <table>
            <!-- Intestazione tabella -->
            <tr>
                <td class="casella_titolo">
                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['art']); ?></div>
                </td>
                <td class="casella_titolo">
                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['titolo']); ?></div>
                </td>
                <td class="casella_titolo">
                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>
                </td>
            </tr>
            <!-- Record -->
            <?php foreach($result as $row) { ?>
                <tr class="risultati_elenco_record_gestione">
                    <td class="casella_elemento">
                        <div class="elementi_elenco">
                            <?php echo $row['capitolo']; ?>
                        </div>
                    </td>
                    <td class="casella_elemento">
                        <div class="elementi_elenco">
                            <?php echo gdrcd_filter('out', $row['titolo']); ?>
                        </div>
                    </td>
                    <td class="casella_controlli"><!-- Iconcine dei controlli -->
                                                    <!-- Modifica -->
                        <div class="controlli_elenco">
                            <div class="controllo_elenco">
                                <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_ambientazione" method="post">
                                    <input type="hidden" name="id_record" value="<?php echo $row['capitolo'] ?>" />
                                    <input type="hidden" name="op" value="edit" />
                                    <input type="image" src="public/images/icons/edit.png" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                                </form>
                            </div>
                            <!-- Elimina -->
                            <div class="controllo_elenco">
                                <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_ambientazione" method="post">
                                    <input type="hidden" name="id_record" value="<?php echo $row['capitolo'] ?>" />
                                    <input type="hidden" name="op" value="erase" />
                                    <input type="image" src="public/images/icons/erase.png" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>" title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>" />
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php } //while
                
            ?>
        </table>
    </div>

<!-- Paginatore elenco -->
<div class="pager">
    <?php
    if($totaleresults > $PARAMETERS['settings']['records_per_page']) {
        echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
        for($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['records_per_page']); $i++) {
            if($i != gdrcd_filter('num', $_REQUEST['offset'])) {
                ?>
                <a href="main.php?page=gestione_ambientazione&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
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
    <a href="main.php?page=gestione_ambientazione&op=new">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['link']['new']); ?>
    </a>
</div>
<div class="link_back">
    <a href="main.php?page=gestione">Torna indietro</a>
</div>