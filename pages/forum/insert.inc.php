<?php
$cond = '';
$join = '';
$fields = '';
if($_POST['padre'] == -1) {
    $cond = ' araldo.id_araldo = '.gdrcd_filter('num', $_POST['araldo']);
} else {
    $fields = ', messaggioaraldo.chiuso';
    $join = ' INNER JOIN messaggioaraldo ON messaggioaraldo.id_araldo = araldo.id_araldo ';
    $cond = " messaggioaraldo.id_messaggio=".gdrcd_filter('num', $_POST['padre'])." AND id_messaggio_padre=-1";
}

$thread = gdrcd_query("SELECT araldo.id_araldo, araldo.tipo, araldo.proprietari".$fields." FROM araldo ".$join.(! empty($cond) ? ' WHERE '.$cond : ''),'result');

if(gdrcd_query($thread, 'num_rows')) {
    $araldoData = gdrcd_query($thread, 'fetch');

    // Se il testo del messaggio è vuoto, evitare di proseguire
    if ( !empty(trim($_POST['messaggio'])) ) {
        if( gdrcd_controllo_permessi_forum($araldoData['tipo'], $araldoData['proprietari']) ){
            //Solo se il thread non è chiuso
            gdrcd_query("INSERT INTO messaggioaraldo (id_messaggio_padre, id_araldo, titolo, messaggio, id_personaggio, data_messaggio, data_ultimo_messaggio ) VALUES (".gdrcd_filter('num', $_POST['padre']).", ".gdrcd_filter('num', $araldoData['id_araldo']).", '".gdrcd_filter('in', $_POST['titolo'])."', '".gdrcd_filter('in', $_POST['messaggio'])."', '".gdrcd_filter('in', $_SESSION['id_personaggio'])."', NOW(), NOW())");

            if($_POST['padre'] == -1) {
                $_POST['padre'] = gdrcd_query('', 'last_id');
            } else {
                gdrcd_query("UPDATE messaggioaraldo SET data_ultimo_messaggio = NOW() WHERE id_messaggio = ".gdrcd_filter_num($_POST['padre']));
            }
            ?>
            <div class="warning">
                <?php echo gdrcd_filter('out', $MESSAGE['warning']['inserted']); ?>
            </div>
            <?php
            gdrcd_query("DELETE FROM araldo_letto WHERE thread_id = ".gdrcd_filter('num', $_POST['padre'])." AND id_personaggio != '".gdrcd_filter('in', $_SESSION['id_personaggio'])."'");

            gdrcd_redirect('main.php?page=forum&op=read&what='.gdrcd_filter('num', $_POST['padre']).'&where='.$araldoData['id_araldo']);
        } else {
            echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        }
    }
    else {
        gdrcd_redirect('main.php?page=forum&op=read&what='.gdrcd_filter('num', $_POST['padre']).'&where='.$araldoData['id_araldo']);
    }
} else {
    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['not_exists']).'</div>';
}