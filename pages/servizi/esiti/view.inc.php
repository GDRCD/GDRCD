<?php

    // Ottengo l'identificativo della serie di esiti
    $id = gdrcd_filter('num', $_POST['id']);

    // Ottengo tutti gli esiti relativi alla serie scelta
    $query=gdrcd_query("SELECT * 
                                FROM blocco_esiti 
                                WHERE id = ".$id." 
                                AND pg = '".gdrcd_filter('in',$_SESSION['login'])."' ORDER BY id ", 'result');
    $result=gdrcd_query($query, 'fetch');

    $tit = gdrcd_filter('out', $result['titolo']);
    $pg = gdrcd_filter('out', $result['pg']);

    gdrcd_query("UPDATE esiti SET letto_pg = 1 WHERE id_blocco = ".$id." ");

    ?>
<div class="fate_frame">
    <div class="titolo_box">
        <h2 >
            <?php echo $tit; ?>
        </h2>
    </div>

    <?php $quer="SELECT * FROM esiti WHERE id_blocco = ".$id." AND chat = 0 
                        AND pg = '".gdrcd_filter('in',$_SESSION['login'])."' ORDER BY data DESC";
    $res=gdrcd_query($quer, 'result'); ?>

    <?php if ($tit['closed']==0) { ?>
        <div class="titolo_box">
            <a class="link_new"
               href='main.php?page=servizi_esitinew&op=new&blocco=<?php echo gdrcd_filter('num',$id);?>'
               target="_blank">
                Invia una nuova richiesta di esito
            </a>
        </div>
    <?php } ?>
    <?php while  ($row=gdrcd_query($res, 'fetch')) {
        $chat=gdrcd_query("SELECT nome FROM mappa WHERE id = ".$row['chat']." ");	?>

        <div class="title_esi">Autore:<b><?php echo $row['autore'].'</b> | 
                            Creato il: '.gdrcd_format_date($row['data']).' alle '.gdrcd_format_time($row['data']);?></div>

        <div class="fate_title">Titolo: <b><?php echo $row['titolo'];?></b>
            <?php if ($row['dice_face']>0 && $row['dice_num']>0 && TIRI_ESITO) { ?>
                <br> Risultato tiro di <?php echo $row['dice_num'].'d'.$row['dice_face'];?>: <b><?php echo $row['dice_results'] ?></b>
            <?php } ?>
        </div>
        <div class="fate_cont">
            <?php echo $row['contenuto']; ?>
        </div>

        <b>Note OFF:</b> <?php echo $row['noteoff']; ?>
    <?php } # Singolo esito ?>
</div>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=servizi_esiti">
        <?php echo $MESSAGE['interface']['esiti']['link']['back']; ?>
    </a>
</div>
