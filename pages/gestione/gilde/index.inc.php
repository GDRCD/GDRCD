<?php
    $pagebegin = (int) gdrcd_filter('get', $_REQUEST['offset']) * $PARAMETERS['settings']['records_per_page'];
    $pageend = $PARAMETERS['settings']['records_per_page'];
    //Conteggio record totali
    $record_globale = gdrcd_stmt_one("SELECT COUNT(*) FROM gilda");
    $totaleresults = $record_globale['COUNT(*)'];
    //Lettura record
    $result = gdrcd_stmt_all("SELECT 
    gilda.id_gilda, 
    gilda.nome, 
    gilda.visibile, 
    codtipogilda.descrizione 
    FROM gilda 
    LEFT JOIN codtipogilda ON gilda.tipo = codtipogilda.cod_tipo 
    ORDER BY nome LIMIT ".$pagebegin.", ".$pageend."");
     ?>
                    <!-- Elenco dei record paginato -->
    <div class="elenco_record_gestione">
        <table>
            <!-- Intestazione tabella -->
            <tr>
                <td class="casella_titolo">
                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['name_col']); ?></div>
                </td>
                <td class="casella_titolo">
                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['type']); ?></div>
                </td>
                <td class="casella_titolo">
                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['visible']); ?></div>
                </td>
                <td class="casella_titolo">
                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>
                </td>
            </tr>
            <!-- Record -->
            <?php 
            foreach($result as $row) {?>
                <tr>
                    <td class="casella_elemento">
                        <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['nome']); ?></div>
                    </td>
                    <td class="casella_elemento">
                        <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['descrizione']); ?></div>
                    </td>
                    <td class="casella_elemento">
                        <div class="elementi_elenco"><?php if($row['visibile'] == 1) {
                                echo gdrcd_filter('out', $MESSAGE['interface']['administration']['yes']);
                            } else {
                                echo gdrcd_filter('out', $MESSAGE['interface']['administration']['no']);
                            } ?></div>
                    </td>
                    <td class="casella_controlli"><!-- Iconcine dei controlli -->
                                                    <!-- Modifica -->
                        <div class="controlli_elenco">
                            <div class="controllo_elenco">
                                <form class="opzioni_elenco_record_gestione"
                                        action="main.php?page=gestione_gilde" method="post">
                                    <input type="hidden" name="id"
                                            value="<?php echo gdrcd_filter('out', $row['id_gilda']) ?>" />
                                    <input type="hidden" name="op" value="edit" />
                                    <input type="image"
                                            src="public/images/icons/edit.png"
                                            alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>"
                                            title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                                </form>
                            </div>
                            <!-- Elimina -->
                            <div class="controllo_elenco">
                                <form class="opzioni_elenco_record_gestione"
                                        action="main.php?page=gestione_gilde" method="post">
                                    <input type="hidden" name="id"
                                            value="<?php echo gdrcd_filter('out', $row['id_gilda']) ?>" />
                                    <input type="hidden" name="op" value="save_delete" />
                                    <input type="image"
                                            src="public/images/icons/erase.png"
                                            alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>"
                                            title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']
                                            ); ?>" />
                                </form>
                            </div>
                            <div class="controlli_elenco">
                    </td>
                </tr>
            <?php
            } //foreach
          
            ?>
        </table>
    </div>
<!-- Paginatore elenco -->
<div class="pager">
    <?php if($totaleresults > $PARAMETERS['settings']['records_per_page']) {
        echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
        for($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['records_per_page']); $i++) {
            if($i != gdrcd_filter('num', $_REQUEST['offset'])) { ?>
                <a href="main.php?page=gestione_gilde&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
            <?php } else {
                echo ' '.($i + 1).' ';
            }
        } //for
    }//if
    ?>
</div>
<!-- link crea nuovo -->
<div class="link_back">
    <a href="main.php?page=gestione_gilde&op=new">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['new']); ?>
    </a><br />
    <a href="main.php?page=gestione_gilde&op=edit&id_record=-1">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['new_role']); ?>
    </a><br />
    <a href="main.php?page=gestione_tipi&types=guilds">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['menage_types']); ?>
    </a>
</div>
<div class="link_back">
    <a href="main.php?page=gestione">Torna indietro</a>
</div>