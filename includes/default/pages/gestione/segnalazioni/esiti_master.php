<?php

#TODO - Da omologare le sezioni di segnalazione

$esiti_chat = Functions::get_constant('ESITI_CHAT');
$esiti = Functions::get_constant('ESITI_ENABLE');
$perm = Permissions::permission('MANAGE_ESITI');
$perm_all = Permissions::permission('MANAGE_ALL_ESITI');
$esiti_tiri = Functions::get_constant('ESITI_TIRI');

if ( $perm && $esiti ) {
    ?>
    <div class="page_title">
        <h2>Gestione esiti</h2>
    </div>

    <div class="form_info">
        <?php echo $MESSAGE['interface']['esiti']['gm_page']; ?>
    </div>
    <a class="but_newd" href='main.php?page=gestione_segnalazioni&segn=esito_index&op=first' target="_blank">
        Apri una nuova serie di esiti
    </a>

    <?php
    # Lista di tutti i blocchi di esiti
    if ( $_POST['op'] == 'list' ) {
        $id = gdrcd_filter('num', $_POST['id']);
        if ( $perm_all ) {
            $query = gdrcd_query("SELECT * FROM blocco_esiti WHERE id = " . $id . " 
            AND (master = '0' || master ='" . gdrcd_filter('in', $_SESSION['login']) . "') 
            ORDER BY id ", 'result');
        } else {
            $query = gdrcd_query("SELECT * FROM blocco_esiti WHERE id = " . $id . " 
            ORDER BY id ", 'result');
        }
        $blocco = gdrcd_query($query, 'fetch');

        $tit = gdrcd_filter('out', $blocco['titolo']);
        $pg = gdrcd_filter('out', $blocco['pg']);

        gdrcd_query("UPDATE esiti SET letto_master = 1 WHERE id_blocco = " . gdrcd_filter('num', $blocco['id']) . " ");

        ?>
        <div class="fate_frame">
            <div class="titolo_box">
                <h2>
                    <b><?php echo $tit; ?> - <?php echo $pg; ?></b>
                    <a class="link_new"
                       href='main.php?page=gestione_segnalazioni&segn=newesito&op=edit&id=<?php echo gdrcd_filter('num', $blocco['id']); ?>'
                       target="_blank">
                        Modifica serie di esiti
                    </a>
                </h2>
            </div>

            <?php $quer = "SELECT * FROM esiti WHERE id_blocco = " . gdrcd_filter('num', $blocco['id']) . " ORDER BY data DESC";
            $res = gdrcd_query($quer, 'result'); ?>

            <?php if ( $tit['closed'] == 0 ) { ?>
                <div class="titolo_box">
                    <a class="link_new"
                       href='main.php?page=gestione_segnalazioni&segn=esito_index&op=edit&id=<?php echo gdrcd_filter('num', $blocco['id']); ?>'
                       target="_blank">
                        Modifica
                    </a> |
                    <a class="link_new"
                       href='main.php?page=gestione_segnalazioni&segn=esito_index&op=new&blocco=<?php echo gdrcd_filter('num', $blocco['id']); ?>'
                       target="_blank">
                        Invia un nuovo esito
                    </a>
                    <?
                    if ( $esiti_chat ) {
                        ?>
                        | <a class="link_new"
                             href='main.php?page=gestione_segnalazioni&segn=esito_index&op=newchat&blocco=<?php echo gdrcd_filter('num', $blocco['id']); ?>'
                             target="_blank">
                            Invia un esito in chat
                        </a>
                    <?php } ?>
                </div>
            <?php }
            while ( $row = gdrcd_query($res, 'fetch') ) {
                $abilita = gdrcd_query("SELECT nome FROM abilita WHERE id_abilita = " . $row['id_ab'] . " ");
                $chat = gdrcd_query("SELECT nome FROM mappa WHERE id = " . $row['chat'] . " "); ?>
                <div class="title_esi">
                    Autore:<b><?php echo $row['autore'] . '</b> | Creato il: ' . gdrcd_format_date($row['data']) . ' alle
                     ' . gdrcd_format_time($row['data']); ?>
                </div>

                <div class="fate_title">
                    Titolo: <b><?php echo $row['titolo']; ?></b>
                    <?php if ( $row['chat'] > 0 ) {
                        echo '- <b><u>Esito in chat</u></b>
                         (Chat: ' . $chat['nome'] . ' | Skill: ' . gdrcd_filter('out', $abilita['nome']) . ')';
                    } ?>
                    <br>
                    <?php if ( $row['dice_face'] > 0 && $row['dice_num'] > 0 && $esiti_tiri ) { ?>
                        Risultato tiro di <?php echo $row['dice_num'] . 'd' . $row['dice_face']; ?>:
                        <b><?php echo $row['dice_results'] ?></b>
                    <?php } ?>
                </div>
                <div class="fate_cont">
                    <?php if ( $row['chat'] > 0 ) {
                        echo 'Esito per il Fallimento critico: ' . $row['CD_1'] . '<br> 
                        Esito per il Fallimento: ' . $row['CD_2'] . '<br> Esito per il Successo: ' . $row['CD_3'] . '<br>
                        Esito per il Successo critico: ' . $row['CD_4'];
                    } else {
                        echo $row['contenuto'];
                    } ?>
                </div>
                <b>Note OFF:</b> <?php echo $row['noteoff']; ?>
            <?php } # Singolo esito ?>
        </div><br>
        <!-- Link a piè di pagina -->
        <div class="link_back">
            <a href="main.php?page=gestione_segnalazioni&segn=esiti_master">Torna alla lista</a>
        </div>
    <?php } else if ( isset($_POST['op']) === false ) {
        //Determinazione pagina (paginazione)
        $pagebegin = (int)$_REQUEST['offset'] * $PARAMETERS['settings']['posts_per_page'];
        $pageend = $pagebegin + $PARAMETERS['settings']['posts_per_page'];

        //Conteggio record totali
        $record_globale = gdrcd_query("SELECT COUNT(*) FROM blocco_esiti ");
        $totaleresults = $record_globale['COUNT(*)'];

        if ( $perm_all ) {    #seleziono la lista di esiti sulla base dei permessi
            $query = "SELECT * FROM blocco_esiti WHERE (master = '0' || master ='" . gdrcd_filter('in', $_SESSION['login']) . "') 
          ORDER BY closed, data DESC, pg LIMIT " . $pagebegin . ", " . $PARAMETERS['settings']['posts_per_page'] . "";
        } else {
            $query = "SELECT * FROM blocco_esiti ORDER BY closed, data DESC, pg 
          LIMIT " . $pagebegin . ", " . $PARAMETERS['settings']['posts_per_page'] . "";
        }

        $blocco = gdrcd_query($query, 'result');

    }
}
?>