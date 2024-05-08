<div class="chat_azioni">
    <?php
    $elenco_azioni=gdrcd_query("SELECT chat.id, chat.imgs, chat.mittente, chat.destinatario, chat.tipo, chat.ora, chat.testo,   mappa.ora_prenotazione
						FROM chat
						INNER JOIN mappa ON mappa.id = chat.stanza
						LEFT JOIN personaggio ON personaggio.nome = chat.mittente
						WHERE  stanza = {$_REQUEST['dir']} AND chat.ora > IFNULL(mappa.ora_prenotazione, '0000-00-00 00:00:00') AND DATE_SUB(NOW(), INTERVAL 4 HOUR) < ora ORDER BY id ASC ", "result");
    foreach ($elenco_azioni as $azione) {
        $tipo=$azione['tipo'];
        $add_chat="";
        switch ($tipo) {
            case 'P':
            case 'A':
                //azione
                echo Azione($azione);
                break;

            case 'S':
               echo Sussurri($azione);
                break;
            case 'C':
                //visualizzazione lancio stat
              echo  Statistiche($azione);

                break;

            case 'M':
                echo Master($azione);
                break;
            case 'N':
                //PNG
               echo PNG($azione);
                break;
        }

        echo $add_chat;

    }

    $chat_id = gdrcd_filter("num",$_GET['dir']);
    //$conta_azioni=gdrcd_query("SELECT count(*) as conta from chat where stanza = '{$chat_id}' ");
    $last_message = $_SESSION['last_message'];
    if(empty($last_message)) $last_message = 0;
    $conta_azioni=gdrcd_query("SELECT count(*) as conta 
                        FROM chat
						INNER JOIN mappa ON mappa.id = chat.stanza
						LEFT JOIN personaggio ON personaggio.nome = chat.mittente
                where stanza = '{$chat_id}'	   AND chat.ora > IFNULL(mappa.ora_prenotazione, '0000-00-00 00:00:00') AND DATE_SUB(NOW(), INTERVAL 24 HOUR) < ora");

    ?>


</div>
<input type="hidden" id="countmessages" value="<?=$conta_azioni['conta']?>">