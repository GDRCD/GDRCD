<?php

// Ottengo i dati del messaggio
$id_messaggio = gdrcd_filter('num', $_REQUEST['id_messaggio']);
$result = gdrcd_query("SELECT * FROM messaggi WHERE id = ".$id_messaggio." and ( destinatario = '".$_SESSION['login']."' or mittente = '".$_SESSION['login']."') LIMIT 1", 'result');

// Se non ottengo alcun risultato, allora mostro un messaggio di errore
if(gdrcd_query($result, 'num_rows') == 0) { ?>
    <div class="warning">
        Impossibile visualizzare il messaggio richiesto, il messaggio potrebbe non esistere oppure non
        disponi delle autorizzazioni necessarie per poterlo visionare
    </div>
    <?php
} else {
    // Preparo i dati
    $record = gdrcd_query($result, 'fetch');
    gdrcd_query($result, 'free');

    // Aggiorno lo stato letto del messaggio
    if(($record['destinatario'] == $_SESSION['login']) && ($record['letto'] == 0)) {
        gdrcd_query("UPDATE messaggi SET letto = 1 WHERE id = ".gdrcd_filter('num', $record['id'])." LIMIT 1");
    }

    // Suddivido la data di invio
    list($data_spedito, $ora_spedito) = explode(' ', $record['spedito']);

    ?>
    <div class="read_message_box">
        <div class="infos">
            <span class="title"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['date']).": "; ?></span>
            <span class="body"><?php echo gdrcd_format_date($data_spedito) ?></span>
            <span class="title"><?php echo ' '.gdrcd_filter('out', $MESSAGE['interface']['messages']['time']).' '; ?></span>
            <span class="body"><?php echo gdrcd_format_time($ora_spedito); ?>
 		    </span>
        </div>
        <div class="infos">
 		    <span class="title"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['sender']).": "; ?></span>
            <span class="body"><?php echo gdrcd_filter('out', $record['mittente']); ?></span>
        </div>
        <div class="infos">
            <span class="title"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['type']['title']).": "; ?></span>
            <span class="body"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['type']['options'][$record['tipo']]);?></span>
        </div>
        <div class="infos">
            <span class="title"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['subject']).": "; ?></span>
 		    <span class="body"><?php echo gdrcd_filter('out', $record['oggetto']);?></span>
        </div>
        <div class="read_message_box_text">
            <?php echo nl2br(gdrcd_bbcoder(gdrcd_filter('out', $record['testo']))); ?>
        </div>

        <div class="read_message_box_forms">
            <div class="read_message_box_form">
                <!-- attach -->
                <form action="main.php?page=messages_center&op=read&id_messaggio=<?php echo $record['id']; ?>" method="post">
                    <input type="hidden" name="reply_dest" value="<?php echo $record['mittente']; ?>" />
                    <input type="hidden" name="reply_subject" value="Re: <?php echo $record['oggetto']; ?>" />
                    <input type="hidden" name="reply_tipo" value="<?php echo $record['tipo']; ?>" />
                    <input type="hidden" name="testo" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['attachment'].$record['testo']); ?>" />
                    <input type="hidden" name="op" value="attach" />
                    <input type="image" src="imgs/icons/attach.png" value="submit" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['attach']); ?>"
                           title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['attach']); ?>" />
                </form>
            </div>
            <div class="read_message_box_form">
                <!-- reply -->
                <form action="main.php?page=messages_center&op=read&id_messaggio=<?php echo $record['id']; ?>" method="post">
                    <input type="hidden" name="reply_dest" value="<?php echo $record['mittente']; ?>" />
                    <input type="hidden" name="reply_subject" value="Re: <?php echo $record['oggetto']; ?>" />
                    <input type="hidden" name="reply_tipo" value="<?php echo $record['tipo']; ?>" />
                    <input type="hidden" name="op" value="reply" />
                    <input type="image" src="imgs/icons/reply.png" value="submit" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['reply']); ?>"
                           title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['reply']); ?>" />
                </form>
            </div>
        </div>
        <!-- read_message_box_form -->
    </div>
    <?php
}
?>
<div class="link_back">
    <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['go_back']); ?></a>
</div>
