<?php
$record_globale = gdrcd_query("SELECT COUNT(*) as tot FROM eventi");
$totaleresults = $record_globale['tot'];
if($totaleresults>0){

//Determinazione pagina (paginazione)
    $pagebegin=(int)gdrcd_filter('get',$_POST['offset'])*$PARAMETERS['settings']['records_per_page_calendar'];

    $pageend=$PARAMETERS['settings']['records_per_page_calendar'];
    //Conteggio record totali
    $pg=gdrcd_filter('out', $_GET['pg']);

    //Lettura record
    $query= "SELECT eventi_personaggio.id,  start, end,eventi_tipo.title, eventi_colori.backgroundColor,
       eventi_colori.borderColor, eventi_colori.textColor  
FROM eventi_personaggio 

LEFT JOIN eventi_tipo ON eventi_personaggio.title = eventi_tipo.id
LEFT  JOIN eventi_colori ON eventi_personaggio.colore = eventi_colori.id 
WHERE personaggio='{$pg}' ORDER BY start ASC LIMIT ".$pagebegin.", ".$pageend;
    $result=gdrcd_query($query, 'result');
    $numresults=gdrcd_query($result, 'num_rows');
/* Se esistono record */
?>
    <!-- Elenco dei record paginato -->
    <table class="tabella_modifica">
        <?php
        foreach ($result as $row) {
            ?>
            <tr class="risultati_elenco_record_gestione">
                <td class="casella_elemento">
                    <div class="elementi_elenco">
                        <?php echo $row['title']. " - ".$row['titolo']; ?>
                    </div>
                </td>
                <td class="casella_elemento">
                    <div class="elementi_elenco">
                        <?= $row['start']; ?>
                    </div>
                </td>
                <?php
                if($_SESSION['permessi']>=MODERATOR){
                ?>
                <td class="casella_controlli"><!-- Iconcine dei controlli -->
                    <!-- Modifica -->
                    <div class="controlli_elenco" >
                        <div class="controllo_elenco" >
                            <form action="main.php?page=scheda_calendario&pg=<?= $pg?>" method="post">
                                <input type="hidden" name="id" value="<?= $row['id']?>" />
                                <input type="hidden" name="pg" value="<?= $pg?>" />
                                <input hidden value="edit_page" name="op">
                                <input type="image" src="imgs/icons/edit.png"
                                       alt="<?= gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['edit']); ?>"
                                       title="<?= gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                            </form>
                        </div>
                        <div class="controllo_elenco" >
                            <form action="<?php echo (CALENDAR_POPUP)?'popup' : 'main'; ?>.php?page=calendario" method="post">
                                <input type="hidden" name="id" value="<?= $row['id']?>" />
                                <input hidden value="delete" name="op">
                                <input type="image"
                                       src="imgs/icons/erase.png"
                                       alt="<?= gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['erase']); ?>"
                                       title="<?=gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['erase']); ?>"/>
                            </form>
                        </div> <?php } //permessi ?>
                    </div>
                </td>
            </tr>
        <?php } //while ?>
    </table>
<!-- Paginatore elenco -->
<div class="pager" style="display: flex;">
    <?php
    if($totaleresults>$PARAMETERS['settings']['records_per_page_calendar']){
        echo gdrcd_filter('out',$MESSAGE['interface']['pager']['pages_name']);
        for($i=0;$i<=floor($totaleresults/$PARAMETERS['settings']['records_per_page_calendar']);$i++){
            if ($i!=gdrcd_filter('num',$_POST['offset'])){?>
            <form action="<?php echo (CALENDAR_POPUP)?'popup' : 'main'; ?>.php?page=calendario" method="post">
                <input hidden value="<?php echo $i; ?>" name="offset">
                <input hidden value="edit" name="op">
                <button type="submit"   class="btn-link" ><?php echo $i+1; ?></button>
            </form>
            <?php } else { echo ' '.($i+1).' '; }
        } //for
    }//if
    ?>

</div>

<?php
}
?>