<?php
$pagebegin=(int)gdrcd_filter('get',$_REQUEST['offset'])*$PARAMETERS['settings']['records_per_page'];
$pageend=$PARAMETERS['settings']['records_per_page'];
//Conteggio record totali
$record_globale = gdrcd_query("SELECT COUNT(*) as tot FROM eventi");
$totaleresults = $record_globale['tot'];
$today=date("Y-m-d");

$query= "SELECT * FROM eventi WHERE start > '{$today}' order by start ASC LIMIT {$pagebegin}, {$pageend}";
$result = gdrcd_query($query, 'result');

while ($row=gdrcd_query($result, 'fetch')){?>
    <div class="intestazione_eventi">
        <i><?=gdrcd_bbcoder(gdrcd_filter('out',$row['title'])); ?></i>
        - Data inizio: <?= gdrcd_filter('out',$row['start'])?>;
        Conclusione: <?=gdrcd_filter('out',$row['end'])?>
    </div>
    <div class="colore_evento  <?=gdrcd_filter('out',$row['backgroundColor'])?>">
        <i>Bollino Tipo:</i>
        <?=gdrcd_filter('out',$row['backgroundColor'])?>
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
    if($totaleresults>$PARAMETERS['settings']['records_per_page']){
        echo gdrcd_filter('out',$MESSAGE['interface']['pager']['pages_name']);
        for($i=0;$i<=floor($totaleresults/$PARAMETERS['settings']['records_per_page']);$i++){
            if ($i!=gdrcd_filter('num',$_REQUEST['offset'])){?>
                <a href="popup.php?page=calendario&offset=<?=$i; ?>"><?=$i+1; ?></a>
                <?php
            } else {
                echo ' '.($i+1).' ';
            }
        } //for
    }//if ?>
</div>