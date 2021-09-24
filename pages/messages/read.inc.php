<?php
/** * Bugfix: correzione di un bug che permetteva la visualizzazione di messaggi non inviati all'utente
 * semplicemente modificando l'id. Viene quindi aggiunta nella clausola where il controllo sulla proprietà
 * del messaggio. Nel caso in cui non venga trovato alcun messaggio verrà mostrato un errore.
 * @author Rhllor
 */
//$result=gdrcd_query("SELECT * FROM messaggi WHERE id = ".gdrcd_filter('num',$_REQUEST['id_messaggio'])." LIMIT 1", 'result');
$result = gdrcd_query("SELECT * FROM messaggi WHERE id = ".gdrcd_filter('num', $_REQUEST['id_messaggio'])." and ( destinatario = '".$_SESSION['login']."' or mittente = '".$_SESSION['login']."') LIMIT 1", 'result');
if(gdrcd_query($result, 'num_rows') == 0) { ?>
    <div class="warning">
        Impossibile visualizzare il messaggio richiesto, il messaggio potrebbe non esistere oppure non
        disponi delle autorizzazioni necessarie per poterlo visionare
    </div>
    <?php
} else {
    $record = gdrcd_query($result, 'fetch');
    gdrcd_query($result, 'free');
    //Leggi id messaggio
    //Formatta messaggio
    //Bottoni Rispondi, Rispondi e allega, cancella
    ?>
    <div class="read_message_box">
        <div class="infos">
            <span class="title"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['date']).": "; ?></span>
            <span class="body">
			   <?php $quando = explode(' ', $record['spedito']);
               echo gdrcd_format_date($quando[0]) ?>
 			</span>
            <span class="title">
 			       <?php echo ' '.gdrcd_filter('out', $MESSAGE['interface']['messages']['time']).' '; ?>
 			</span>
            <span class="body">
 				   <?php echo gdrcd_format_time($quando[1]); ?>
 		    </span>
        </div>
        <div class="infos">
 		    <span class="title"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['sender']).": "; ?>
 			</span>
            <span class="body"><?php echo gdrcd_filter('out', $record['mittente']); ?></span>
        </div>
        <?php if(($record['destinatario'] == $_SESSION['login']) && ($record['letto'] == 0)) {
            gdrcd_query("UPDATE messaggi SET letto = 1 WHERE id = ".gdrcd_filter('num', $_REQUEST['id_messaggio'])." LIMIT 1");
        } ?>
        <div class="read_message_box_text">
            <?php echo nl2br(gdrcd_bbcoder(gdrcd_filter('out', $record['testo']))); ?>
        </div>

        <div class="read_message_box_forms">

            <div class="read_message_box_form">
            </div>

            <div class="read_message_box_form">
                <!-- attach -->
                <form action="main.php?page=messages_center"
                      method="post">
                    <input type="hidden" name="reply_dest" value="<?php echo $record['mittente']; ?>" />
                    <input type="hidden" name="testo" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['attachment'].$record['testo']); ?>" />
                    <input type="hidden" name="op" value="attach" />
                    <input type="image" src="imgs/icons/attach.png" value="submit" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['attach']); ?>"
                           title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['attach']); ?>" />
                </form>
            </div>
            <div class="read_message_box_form">
                <!-- reply -->
                <form action="main.php?page=messages_center" method="post">
                    <input type="hidden" name="reply_dest" value="<?php echo $record['mittente']; ?>" />
                    <input type="hidden" name="op" value="reply" />
                    <input type="image" src="imgs/icons/reply.png" value="submit" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['reply']); ?>"
                           title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['reply']); ?>" />
                </form>
            </div>
        </div>
        <!-- read_message_box_form -->
    </div>
    <div class="link_back">
        <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['go_back']); ?></a>
    </div>
    <?php
} // Chiudo controllo paternità messaggio