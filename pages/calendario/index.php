<?php
    $pagebegin=(int)gdrcd_filter('get',$_REQUEST['offset'])*$PARAMETERS['settings']['records_per_page_calendar'];
    $pageend=$PARAMETERS['settings']['records_per_page_calendar'];
    //Conteggio record totali
    $record_globale = gdrcd_query("SELECT COUNT(*) as tot FROM eventi");
    $totaleresults = $record_globale['tot'];
    $today=date("Y-m-d");

    $query= "SELECT eventi.id, titolo, descrizione , start, end,eventi_tipo.title, eventi_colori.backgroundColor, eventi_colori.textColor, eventi_colori.colore  FROM eventi 
                LEFT JOIN eventi_tipo ON eventi.title = eventi_tipo.id
                LEFT  JOIN eventi_colori ON eventi.colore = eventi_colori.id
                WHERE start > '{$today}' order by start ASC LIMIT {$pagebegin}, {$pageend}";
    $result = gdrcd_query($query, 'result');

    while ($row=gdrcd_query($result, 'fetch')){

 ?>
    <div class="colore_evento  <?=gdrcd_filter('out',$row['backgroundColor'])?>">
        <i>Bollino Tipo:</i>
        <?=gdrcd_filter('out',$row['colore'])?>
    </div>
    <div class="intestazione_eventi">
        <?=gdrcd_bbcoder(gdrcd_filter('out',$row['title'])); ?> - Data inizio: <?= gdrcd_filter('out',$row['start'])?>;
        Conclusione: <?=gdrcd_filter('out',$row['end'])?>
    </div>

    <div class="intestazione_eventi">
        <i>Titolo:</i> <?= gdrcd_filter('out',$row['titolo'])?>
    </div>
    <div class="descrizione_eventi">
        <?=bbdecoder(gdrcd_filter('out',$row['descrizione'])); ?>
    </div>
    <?php
}//while
?>
<div class="pager">
    <?php
    if($totaleresults>$PARAMETERS['settings']['records_per_page_calendar']){
        echo gdrcd_filter('out',$MESSAGE['interface']['pager']['pages_name']);
        for($i=0;$i<=floor($totaleresults/$PARAMETERS['settings']['records_per_page_calendar']);$i++){
            if ($i!=gdrcd_filter('num',$_REQUEST['offset'])){?>
                <a href="<?php echo (CALENDAR_POPUP)?'popup' : 'main'; ?>.php?page=calendario&offset=<?=$i; ?>"><?=$i+1; ?></a>
                <?php
            } else {
                echo ' '.($i+1).' ';
            }
        } //for
    }//if ?>
</div>