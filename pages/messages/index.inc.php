<?php
// Determinazione pagina
$pagebegin = isset($_REQUEST['offset']) === false ? 0 : (int)$_REQUEST['offset'] * $PARAMETERS['settings']['messages_per_page'];
$pageend = $PARAMETERS['settings']['messages_per_page'];

// Determino se la pagina prevede la visualizzazione dei messaggi inviati
$isSentMessage = $_GET['op'] == 'inviati';

// Costruisco i campi determinanti per la selezione dei messaggi da visualizzare
$msgType = $isSentMessage  ? 'mittente' : 'destinatario';
$delType = $msgType.'_del';

// Costruisco la query per i messaggi
$sqlMessages = "
    SELECT * 
    FROM messaggi 
    WHERE   ".$msgType." = '".$_SESSION['login']."' 
        AND ".$delType." = 0 
    ORDER BY spedito DESC";
$result = gdrcd_query($sqlMessages." LIMIT ".$pagebegin.", ".$pageend, 'result');
$numresults = gdrcd_query($result, 'num_rows');

// Conteggio i record totali per l'impaginazione
$totaleresults = gdrcd_query(gdrcd_query($sqlMessages, 'result'), 'num_rows');

?>
<div class="elenco_record_gioco">
    <div class="link_back">
        [
            <?php
               if(!$isSentMessage) {
                   echo '<u>Ricevuti</u>';
               }
               else {
                   echo '<a href="main.php?page=messages_center">Ricevuti</a>';
               }
            ?>
        ] -
        [
            <?php
                if($isSentMessage) {
                    echo '<u>Inviati</u>';
                }
                else {
                    echo '<a href="main.php?page=messages_center&op=inviati">Inviati</a>';
                }
            ?>
        ]
    </div>
    <?php

        // Se ho superato il limite massio dei messaggi, lo segnalo
        if($totaleresults > $PARAMETERS['settings']['messages_limit']) {
            echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['messages']['please_erase']).'</div>';
        }

        // Se sono presenti record, avvio la costruzione della tabella
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
                            <?php if($isSentMessage) {
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
                            echo ($isSentMessage)
                                ? "Inviato il"
                                : gdrcd_filter('out', $MESSAGE['interface']['messages']['date']);
                            ?>
                        </span>
                    </td>
                    <td>
                        <span class="titoli_elenco">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['type']['title']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="titoli_elenco">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['subject']); ?>
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

                    // Suddivido la data di invio
                    list($data_spedito, $ora_spedito) = explode(' ', $row['spedito']);

                    ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="message_check" value="<?php echo (int) $row['id'] ?>" />
                        </td>
                        <td>
                            <div class="elementi_elenco">
                                <?php
                                if($row['letto'] == 0) { ?>
                                    <img src="imgs/icons/mail_new.png" class="colonna_elenco_messaggi_icon">
                                    <?php
                                } else { ?>
                                    <img src="imgs/icons/mail_read.png" class="colonna_elenco_messaggi_icon">
                                    <?php
                                } ?>
                            </div>
                        </td>
                        <td>
                            <div class="elementi_elenco">
                                <?php
                                if($isSentMessage) {
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
                                <?php echo gdrcd_format_date($data_spedito).'<br/>'.gdrcd_filter('out', $MESSAGE['interface']['messages']['time']).' '.gdrcd_format_time($ora_spedito); ?>
                            </div>
                        </td>
                        <td>
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['type']['options'][$row['tipo']]); ?>
                            </div>
                        </td>
                        <td>
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', $row['oggetto']); ?>
                            </div>
                        </td>
                        <td>
                            <div class="elementi_elenco">
                                <a href="main.php?page=messages_center&op=read&id_messaggio=<?php echo $row['id'] ?>"><?php echo gdrcd_filter('out', substr(nl2br(gdrcd_bbcoder($row['testo'])), 0, 40)); ?>
                                    ...
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="controlli_elenco">
                                <div class="controllo_elenco">
                                    <!-- reply -->
                                    <form action="main.php?page=messages_center<?php echo $isSentMessage ? '&op=inviati' : ''; ?>" method="post">
                                        <input type="hidden" name="reply_dest" value="<?php echo $isSentMessage ? $row['destinatario'] : $row['mittente']; ?>" />
                                        <input type="hidden" name="reply_subject" value="Re: <?php echo $row['oggetto']; ?>" />
                                        <input type="hidden" name="reply_tipo" value="<?php echo $row['tipo']; ?>" />
                                        <input type="hidden" name="op" value="reply" />
                                        <input type="image" src="imgs/icons/reply.png" value="submit" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['reply']); ?>"
                                               title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['reply']); ?>" />
                                    </form>
                                </div>
                                <div class="controllo_elenco">
                                    <!-- reply -->
                                    <form action="main.php?page=messages_center<?php echo $isSentMessage ? '&op=inviati' : ''; ?>" method="post">
                                        <input type="hidden" name="id_messaggio" value="<?php echo $row['id']; ?>" />
                                        <input type="hidden" name="type" value="<?php echo $delType; ?>" />
                                        <input type="hidden" name="op" value="erase" />
                                        <input type="image" src="imgs/icons/erase.png" value="submit" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['erase']); ?>"
                                               title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['erase']); ?>" />
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php

                    // Salvo l'id dell'ultimo messaggio ricevuto per consentire le notifiche in caso di nuovi messaggi
                    if(!$isSentMessage) {
                        // Se non ho ancora stabilito un ultimo id o quello che sto scrivendo ha un id più alto rispetto a quello già salvato, allora lo salvo
                        if(!isset($lastMessageReceived) || $row['id'] > $lastMessageReceived) {
                            $lastMessageReceived = $row['id'];
                        }
                    }

                }//while

                gdrcd_query($result, 'free');


                ?>
            </table>
            <div class="pulsanti_elenco">
                <!-- //Pulsante elimina messaggi selezionati-->
                <form id="multiple_delete" method="post" action="main.php?page=messages_center<?php echo $isSentMessage ? '&op=inviati' : ''; ?>" onSubmit="return checkedDelete();">
                    <input type="hidden" name="op" value="erase_checked" />
                    <input type="hidden" name="type" value="<?php echo $delType; ?>" />
                    <input type="submit" value="Cancella Messaggi Selezionati">
                </form>
                <!-- //Pulsante elimina messaggi letti-->
                <form id="viewed_delete" action="main.php?page=messages_center<?php echo $isSentMessage ? '&op=inviati' : ''; ?>" method="post">
                    <div class="form_submit">
                        <input type="hidden" name="op" value="eraseall" />
                        <input type="hidden" name="type" value="<?php echo $delType; ?>" />
                        <input type="submit" value="Cancella tutti i Messaggi Letti" />
                    </div>
                </form>
            </div>
        <?php
        } else {
            echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['messages']['no_message']).'</div>';
        }
        ?>
    <div class="pager">
        <?php if($totaleresults > $PARAMETERS['settings']['messages_per_page']) {
            echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
            for($i = 0; $i <= ceil($totaleresults / $PARAMETERS['settings']['messages_per_page']) - 1; $i++) {
                if($i != $_REQUEST['offset']) { ?>
                    <a href="main.php?page=messages_center&offset=<?php echo $i; ?>"><?php echo ($i+1); ?></a>
                <?php } else {
                    echo ' '.($i+1).' ';
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
<script type="text/javascript">
    /**
     * Metodo per la creazione della lista dei messaggi da eliminare con checkbox
     * Quando viene inviata l'operazione sul submit, vengono presi tutti i checkbox message_check selezionati
     * e vengono copiati sotto al form multiple_delete e solo poi si passa alla cancellazione
     */
    function checkedDelete() {
        let form = document.getElementById('multiple_delete');
        let messages = document.getElementsByClassName('message_check');
        let n_msg = messages.length;
        let checked = false;

        let i;
        for (i = 0; i < n_msg; i++) {
            if (messages[i].checked) {
                checked = true;
                let el = document.createElement('input');
                el.setAttribute('type', 'hidden');
                el.setAttribute('name', 'ids[]');
                el.setAttribute('value', messages[i].getAttribute('value'));
                form.appendChild(el);
            }
        }

        return checked;
    }
</script>
