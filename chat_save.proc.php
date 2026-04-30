<?php

include('includes/required.php');

/* Eseguo la connessione al database */
$handleDBConnection = gdrcd_connect();

$typeOrder = ($PARAMETERS['mode']['chat_from_bottom'] == 'ON') ? 'DESC' : 'ASC';
//recupero il tempo di salvataggio delle chat
$tempo_salvataggio= gdrcd_configuration_get('salva_chat.tempo_salvataggio');
//recuper il parametro per controllare che il personaggio sia presente in giocata
$solo_autore= gdrcd_configuration_get('salva_chat.solo_autore');


if($solo_autore == 'si'){
    $check_pg=gdrcd_stmt_one("SELECT count(*) as conta 
    FROM chat 
    WHERE stanza = ".$_SESSION['luogo']." 
    AND DATE_SUB(NOW(), INTERVAL $tempo_salvataggio HOUR) < ora and id_personaggio_mittente = '".$_SESSION['id_personaggio']."' AND tipo !='S'");
    if(!$check_pg['conta']){
        echo $MESSAGE['chat']['error']['solo_autore'];
        exit; 
    }
}


if ($PARAMETERS['mode']['chatsavepvt'] == 'ON') {
    $query =  "SELECT c.id, 
                        c.imgs, 
                        c.id_personaggio_mittente, 
                        c.id_personaggio_destinatario, 
                        c.tipo, c.ora,
                        c.testo, 
                        pm.url_img_chat, 
                        pm.nome AS nome_mittente,
                        pd.nome AS nome_destinatario,
                        m.ora_prenotazione,
                        m.privata
                    FROM chat c
                    INNER JOIN mappa m ON m.id = c.stanza
                    LEFT JOIN personaggio pm 
                    ON pm.id_personaggio = c.id_personaggio_mittente
                     LEFT JOIN personaggio pd 
                                ON pd.id_personaggio = c.id_personaggio_destinatario
                    WHERE c.stanza = ? AND DATE_SUB(NOW(), INTERVAL ? HOUR) < c.ora ORDER BY c.id " . $typeOrder; 
                        
} else {
    $query = "	SELECT  c.id, 
                        c.imgs, 
                        c.id_personaggio_mittente, 
                        c.id_personaggio_destinatario, 
                        c.tipo, c.ora,
                        c.testo, 
                        pm.url_img_chat, 
                        pm.nome AS nome_mittente,
                        pd.nome AS nome_destinatario,
                        m.ora_prenotazione,
                        m.privata
                    FROM chat c
                    INNER JOIN mappa m ON m.id = c.stanza
                    LEFT JOIN personaggio pm ON pm.id_personaggio = c.id_personaggio_mittente
                     LEFT JOIN personaggio pd 
                                ON pd.id_personaggio = c.id_personaggio_destinatario
                    WHERE c.stanza = ? 
                    AND m.privata = 0 AND DATE_SUB(NOW(), INTERVAL ? HOUR) < c.ora 
                    AND c.ora > IFNULL(m.ora_prenotazione, '0000-00-00 00:00:00') ORDER BY c.id " . $typeOrder;
}

$do_query = gdrcd_stmt_all($query, [$_SESSION['luogo'], $tempo_salvataggio]);

/*Inizio a preparare il testo da inserire poi nel file da salvare.*/
$add_chat = '

        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
            <link rel="shortcut icon" href="imgs/favicon.ico" type="image/gif" />
            <link rel="stylesheet" href="' . $PARAMETERS['info']['site_url'] . '/css/homepage.css" type="text/css" />
            <link rel="stylesheet" href="' . $PARAMETERS['info']['site_url'] . '/themes/' . $PARAMETERS['themes']['current_theme'] . '/main.css" type="text/css" />
            <link rel="stylesheet" href="' . $PARAMETERS['info']['site_url'] . '/themes/' . $PARAMETERS['themes']['current_theme'] . '/chat.css" type="text/css" />
            <link rel="stylesheet" href="' . $PARAMETERS['info']['site_url'] . '/layouts/' . $PARAMETERS['themes']['kind_of_layout'] . '_frames.php?css=true" type="text/css" />
            </head>
            <body class="main_body" style="overflow:auto; text-align:justify;"> ';


$i = 0;
/* Eseguo la query  */

foreach($do_query as $row){
    $add_chat .= gdrcd_chat_message_handler($row);
}
 $add_chat .= '
            </body>
            </html>
            '; 

    /* Scrivo tutto in un file di testo */
    $start = gdrcd_format_datetime_cat($start_time);
    $end = gdrcd_format_datetime_cat($end_time);
    /* Scrivo tutto in un file di testo */
    $file = $start . "-" . $end . "-" . $_SESSION['id_personaggio'];
    $rand = rand(1, 10000);
    $file = md5($file . $rand);
    $file = $file . ".html";

    $byteLength = strlen($add_chat);

    /* Do le informazioni di download */
    header("Content-Disposition: attachment; filename=" . urlencode($file));
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Description: File Transfer");
    header("Content-Length: " . strlen($add_chat));

    $chunkSize = 4096;

    for ($bufferIndex = 0; $bufferIndex <= $byteLength; $bufferIndex += $chunkSize) {
        echo substr($add_chat, $bufferIndex, $chunkSize);
    }
    

