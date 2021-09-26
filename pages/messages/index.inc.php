<?php
//Determinazione pagina
if(isset($_REQUEST['offset']) === false) {
    $pagebegin = 0;
} else {
    $pagebegin = (int) $_REQUEST['offset'] * $PARAMETERS['settings']['messages_per_page'];
}
$pageend = $PARAMETERS['settings']['messages_per_page'];

//Conteggio messaggi totali
$record = gdrcd_query("SELECT COUNT(*) FROM messaggi WHERE destinatario = '".$_SESSION['login']."'");
$totaleresults = $record['COUNT(*)'];

//Elenco messaggi paginato
if($_GET['op'] == 'inviati') {
    $result = gdrcd_query("SELECT * FROM messaggi WHERE mittente = '".$_SESSION['login']."' AND mittente_del = 0 ORDER BY spedito DESC LIMIT ".$pagebegin.", ".$pageend."", 'result');
    $record = gdrcd_query("SELECT COUNT(*) FROM messaggi WHERE mittente = '".$_SESSION['login']."' AND mittente_del = 0");
    $delType = 'mittente_del';
    $totaleresults = $record['COUNT(*)'];

} else {
    $result = gdrcd_query("SELECT * FROM messaggi WHERE destinatario = '".$_SESSION['login']."' AND destinatario_del = 0 ".$extracond." ORDER BY spedito DESC LIMIT ".$pagebegin.", ".$pageend."", 'result');
    $record = gdrcd_query("SELECT COUNT(*) FROM messaggi WHERE destinatario = '".$_SESSION['login']."' AND destinatario_del = 0 ".$extracond."");
    $delType = 'destinatario_del';
    $totaleresults = $record['COUNT(*)'];
}

$numresults = gdrcd_query($result, 'num_rows');
?>
<div class="elenco_record_gioco">
    <div class="link_back">
        [<a href="main.php?page=messages_center">
            Ricevuti
        </a>] -
        [<a href="main.php?page=messages_center&op=inviati">
            Inviati
        </a>]
    </div>
    <?php
    if($numresults > 0) { ?>
        <table>
            <tr>
                <td>
                    <!-- Checkbox -->
                </td>
                <td>
                    <!-- Icona -->
                </td>
                <td>
                    <span class="titoli_elenco">
                        <?php if($_GET['op'] == 'inviati') {
                            echo "Destinatario";
                        } else {
                            echo gdrcd_filter('out', $MESSAGE['interface']['messages']['sender']);
                        }
                        ?>
                    </span>
                </td>
                <td>
                    <span class="titoli_elenco">
                        <?php
                        echo ($_GET['op'] == 'inviati')
                            ? "Inviato il"
                            : gdrcd_filter('out', $MESSAGE['interface']['messages']['date']);
                        ?>
                    </span>
                </td>
                <td>
                    <span class="titoli_elenco">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['preview']); ?>
                    </span>
                </td>
                <td>
                    <!-- Controlli -->
                </td>
            </tr>
            <?php
            while($row = gdrcd_query($result, 'fetch')) {
                ?>
                <tr>
                    <td>
                        <input type="checkbox" class="message_check" value="<?php echo (int) $row['id'] ?>" />
                    </td>
                    <td>
                        <div class="elementi_elenco">
                            <?php
                            if($row['letto'] == 0) { ?>
                                <img src="imgs/icons/mail_new.png" class="colonna_elengo_messaggi_icon">
                                <?php
                            } else { ?>
                                <img src="imgs/icons/mail_read.png" class="colonna_elengo_messaggi_icon">
                                <?php
                            } ?>
                        </div>
                    </td>
                    <td>
                        <div class="elementi_elenco">
                            <?php
                            if($_GET['op'] == 'inviati') {
                                echo '<a href="main.php?page=scheda&pg='.$row['destinatario'].'">'.$row['destinatario'].'</a>';
                            } elseif(is_numeric($row['mittente']) == true) {
                                echo gdrcd_filter('out', $MESSAGE['interface']['messages']['to_guild']);
                            } else {
                                echo '<a href="main.php?page=scheda&pg='.$row['mittente'].'">'.$row['mittente'].'</a>';
                            } ?>
                        </div>
                    </td>
                    <td>
                        <div class="elementi_elenco">
                            <?php
                            $quando = explode(" ", $row['spedito']);

                            echo gdrcd_format_date($quando[0]).'<br/>'.gdrcd_filter('out', $MESSAGE['interface']['messages']['time']).' '.gdrcd_format_time($quando[1]);
                            ?>
                        </div>
                    </td>
                    <td>
                        <div class="elementi_elenco">
                            <a href="main.php?page=messages_center&op=read&id_messaggio=<?php echo $row['id'] ?>"><?php echo gdrcd_filter('out', substr($row['testo'], 0, 40)); ?>
                                ...
                            </a>
                        </div>
                    </td>
                    <td>
                        <?php
                        if($_GET['op'] != 'inviati') { ?>
                            <div class="controlli_elenco">
                                <div class="controllo_elenco">
                                    <!-- reply -->
                                    <form action="main.php?page=messages_center" method="post">
                                        <input type="hidden" name="reply_dest" value="<?php echo $row['mittente']; ?>" />
                                        <input type="hidden" name="genitore" value="<?php echo $row['id']; ?>" />
                                        <input type="hidden" name="op" value="reply" />
                                        <input type="submit" value="Rispondi" />
                                    </form>
                                </div>
                            </div>
                            <?php
                        } else { ?>
                            <div class="controlli_elenco">
                                <div class="controllo_elenco">
                                    <!-- reply -->
                                    <form action="main.php?page=messages_center" method="post">
                                        <input type="hidden" name="reply_dest" value="<?php echo $row['destinatario']; ?>" />
                                        <input type="hidden" name="genitore" value="<?php echo $row['id']; ?>" />
                                        <input type="hidden" name="op" value="reply" />
                                        <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['reply']); ?>" />
                                    </form>
                                </div>
                            </div>
                            <?php
                        } ?>
                    </td>
                </tr>
                <?php
                $_SESSION['last_istant_message'] = $row['id'];
            }//while

            gdrcd_query($result, 'free');
            gdrcd_query("UPDATE personaggio SET ultimo_messaggio = ".$_SESSION['last_istant_message']." WHERE nome='".$_SESSION['login']."'");
            ?>
        </table>
        <?php
        echo '<div>
          <form id="multiple_delete" method="post" action="main.php?page=messages_center" onSubmit="return checked_copy();">
            <input type="hidden" name="op" value="erase_checked" />
            <input type="hidden" name="type" value="'.$delType.'" />
            <input type="submit" value="Cancella Messaggi Selezionati">
          </form>
        </div>';
    } else {
        if($totaleresults > $PARAMETERS['settings']['messages_limit']) {
            echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['messages']['please_erase']).'</div>';
        }
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['messages']['no_message']).'</div>';
    }
    ?>
    <div class="pager">
        <?php if($totaleresults > $PARAMETERS['settings']['messages_per_page']) {
            echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
            for($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['messages_per_page']); $i++) {
                if($i != $_REQUEST['offset']) { ?>
                    <a href="main.php?page=messages_center&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                <?php } else {
                    echo ' '.($i + 1).' ';
                }
            }
        } ?>
    </div>
</div>
<!-- link scrivi messaggio -->
<div class="link_back">
    <a href="main.php?page=messages_center&op=create">
        <?php echo $MESSAGE['interface']['messages']['new']; ?>
    </a>
</div>
<!-- link scrivi messaggio -->
<div class="link_back">
    <a href="main.php?page=messages_center&op=eraseall">
        <?php echo $MESSAGE['interface']['messages']['erase_all']; ?>
    </a>
</div>
<script type="text/javascript">
    function checked_copy() {
        console.log('call');
        var messages = document.getElementsByClassName('message_check');
        var form = document.getElementById('multiple_delete');
        var n_msg = messages.length;
        var i;
        var checked = false;

        for (i = 0; i < n_msg; i++) {
            if (messages[i].checked) {
                checked = true;
                var el = document.createElement('input');
                el.setAttribute('type', 'hidden');
                el.setAttribute('name', 'ids[]');
                el.setAttribute('value', messages[i].getAttribute('value'));
                form.appendChild(el);
            }
        }

        if (checked) {
            return true;
        } else {
            return false;
        }
    }
</script>
