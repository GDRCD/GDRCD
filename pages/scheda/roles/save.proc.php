<?php
#################################################################################
#                                                                               ##
#                Save Chat HTML 1.3 - Author eLDiabolo                          ##
#                                                                               ##
#      e-mail: http://www.gdr-online.com/email.asp?email=eldiabolo              ##
#                                                                               ##
##################################################################################
#################################################################################

session_start();

/* Includo i file necessari */
include('../../../includes/constant_values.inc.php');
include('../../../config.inc.php');
include('../../../vocabulary/' . $PARAMETERS['languages']['set'] . '.vocabulary.php');
include('../../../includes/functions.inc.php');
include('../../../includes/functions.chat_read.inc.php');

if(file_exists("../../../includes/config-overrides.php")){
    include_once "../../../includes/config-overrides.php";
}

/* Eseguo la connessione al database */
$handleDBConnection = gdrcd_connect();


    # Recupero la giocata dall'id, dopo aver verificato che appartenga al pg
    $check = gdrcd_query("SELECT id_personaggio, stanza, data_inizio, data_fine FROM segnalazione_role WHERE id = " . gdrcd_filter('num', $_GET['id']) . " 
        AND id_personaggio = '" .gdrcd_filter('in', $_SESSION['id_personaggio'] ). "'AND conclusa = 1 ", 'result');
    $num_check = gdrcd_query($check, 'num_rows');
    $check_f= gdrcd_query($check, 'fetch');
    if ($num_check == 0 || $check_f['id_personaggio'] != $_SESSION['id_personaggio'] || SAVE_ROLE === FALSE) {
        echo 'Non hai accesso a questo log chat';
    } else {

    $typeOrder = ($PARAMETERS['mode']['chat_from_bottom'] == 'ON') ? 'DESC' : 'ASC';

    /*Query per caricamento dati dalla chat corrente, carica le azioni degli ultimi 240 min - 4 ore !! NON SALVA LE CHAT PRIVATE !!*/


        $query = gdrcd_query("SELECT 
                                c.id,
                                c.imgs,
                                c.id_personaggio_mittente,
                                pm.nome AS nome_mittente,
                                c.id_personaggio_destinatario,
                                pd.nome AS nome_destinatario,
                                c.tipo,
                                c.ora,
                                c.testo,
                                c.tag_posizione,
                                pm.url_img_chat AS url_img_chat
                            FROM chat c
                            LEFT JOIN personaggio pm 
                                ON pm.id_personaggio = c.id_personaggio_mittente
                            LEFT JOIN personaggio pd 
                                ON pd.id_personaggio = c.id_personaggio_destinatario
                                WHERE stanza = " . $check_f['stanza'] . " AND ora >= '" . gdrcd_filter('in', $check_f['data_inizio']) . "' 
                                AND ora <= '" . gdrcd_filter('in', $check_f['data_fine']) . "' 
                                ORDER BY ora ". $typeOrder, 'result');
                            

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
    
            <body class="main_body" style="overflow:auto; text-align:justify;">
            ';


    $i = 0;
    /* Eseguo la query e le formattazioni */
    while ($row = gdrcd_query($query, 'fetch')) {
        $add_chat.=gdrcd_chat_message_handler($row);
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
    }

