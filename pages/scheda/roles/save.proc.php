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

/* Eseguo la connessione al database */
$handleDBConnection = gdrcd_connect();


    # Recupero la giocata dall'id, dopo aver verificato che appartenga al pg
    $check = gdrcd_query("SELECT mittente, stanza, data_inizio, data_fine FROM segnalazione_role WHERE id = " . gdrcd_filter('num', $_GET['id']) . " 
        AND mittente = '" .gdrcd_filter('in', $_SESSION['login'] ). "'AND conclusa = 1 ", 'result');
    $num_check = gdrcd_query($check, 'num_rows');
    $check_f= gdrcd_query($check, 'fetch');
    if ($num_check == 0 || $check_f['mittente'] != $_SESSION['login'] || SAVE_ROLE === FALSE) {
        echo 'Non hai accesso a questo log chat';
    } else {

    $typeOrder = ($PARAMETERS['mode']['chat_from_bottom'] == 'ON') ? 'DESC' : 'ASC';

    /*Query per caricamento dati dalla chat corrente, carica le azioni degli ultimi 240 min - 4 ore !! NON SALVA LE CHAT PRIVATE !!*/


        $query = gdrcd_query("	SELECT chat.id, chat.imgs, chat.mittente, chat.destinatario, chat.tipo, chat.ora, 
                                chat.testo, personaggio.url_img_chat
                                FROM chat
                                INNER JOIN mappa ON mappa.id = chat.stanza
                                LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
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

        /** BEGIN "Icone di Chat by eLDiabolo"
         *
         * Modifica immagini di chat. Icone razza, genere e gilda.
         * Per farle apparire impostare i parametri relativi nel file config.inc.php
         * se impostato su On compaiono le icone di gilda, in automatico riempie gli spazi vuoti
         *    per chi non ha raggiunto il limite dei simboli possibili così da avere la chat più ordinata
         *
         * v 1.3
         * @author eLDiabolo
         */

        $add_icon = '';

        if ($PARAMETERS['mode']['chaticons'] == 'ON') {
            $add_icon .= '<span class="chat_icons">';

            $icone_chat = explode(";", gdrcd_filter('out', $row['imgs']));

            /*Aggiunta per rendere utilizzabile la chat anche in mancanza dell'installazione della patch Icone Chat
            * Save Chat HTML 1.3
            *@author eLDiabolo
            */
            if (isset($PARAMETERS['settings']['chat']['guilds'])) {

                if ($PARAMETERS['settings']['chat']['race'] == 'ON') {
                    $add_icon .= '<img class="presenti_ico"
                     src="' . $PARAMETERS['info']['site_url'] . '/themes/' . $PARAMETERS['themes']['current_theme'] . '/imgs/icons/races/' . $icone_chat[1] . '">';
                }
                if ($PARAMETERS['settings']['chat']['gender'] == 'ON') {
                    $add_icon .= '<img class="presenti_ico" src="' . $PARAMETERS['info']['site_url'] . '/imgs/icons/testamini' . $icone_chat[0] . '.png">';
                }
                if ($PARAMETERS['settings']['chat']['guilds'] == 'ON') {

                    $query_ruoli = "SELECT 	clgpersonaggioruolo.id_ruolo,	ruolo.nome_ruolo,	ruolo.immagine FROM clgpersonaggioruolo INNER JOIN ruolo ON ruolo.id_ruolo = clgpersonaggioruolo.id_ruolo WHERE clgpersonaggioruolo.personaggio='" . $row['mittente'] . "'";
                    $result_ruoli = gdrcd_query($query_ruoli, 'result');
                    $gilde = 0;

                    if (gdrcd_query($result_ruoli, 'num_rows') > 0) {
                        while ($ruoli = gdrcd_query($result_ruoli, 'fetch')) {
                            $gilde++;
                            $add_icon .= '<img class="presenti_ico" src="' . $PARAMETERS['info']['site_url'] . '/themes/' .
                                $PARAMETERS['themes']['current_theme'] . '/imgs/guilds/' . $ruoli['immagine'] . '" alt="' .
                                gdrcd_filter('out',
                                    $record3['nome_ruolo']) . '" title="' . gdrcd_filter('out',
                                    $ruoli['nome_ruolo']) . '" />';
                        }
                    }

                    for ($i = $PARAMETERS['settings']['guilds_limit']; $i > $gilde; $i--) {
                        $add_icon .= '<img class="presenti_ico" src="' . $PARAMETERS['info']['site_url'] . '/imgs/icons/guilds/null.png" alt="" title="" />';
                    }
                }
            } else {
                /*Aggiunta per rendere utilizzabile la chat anche in mancanza dell'installazione della patch Icone Chat
                * Save Chat HTML 1.3
                *@author eLDiabolo
                */
                $add_icon .= '<img class="presenti_ico" src="' . $PARAMETERS['info']['site_url'] . '/themes/' . $PARAMETERS['themes']['current_theme'] . '/imgs/icons/races/' . $icone_chat[1] . '">';
                $add_icon .= '<img class="presenti_ico" src="' . $PARAMETERS['info']['site_url'] . '/imgs/icons/testamini' . $icone_chat[0] . '.png">';
            }

            /*Corretta la svista riportata nel pacchetto "Icone Chat v 1.1"
            *@author eLDiabolo
            */
            $add_icon .= '</span>';

        }

        /** END "Icone di Chat by eLDiabolo"
         *
         * @author eLDiabolo
         */

        switch ($row['tipo']) {
            case 'P':

                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                /** * Avatar di chat
                 * @author Blancks
                 */
                if ($PARAMETERS['mode']['chat_avatar'] == 'ON' && !empty($row['url_img_chat'])) {
                    $add_chat .= '<img src="' . $row['url_img_chat'] . '" class="chat_avatar" style="width:' . $PARAMETERS['settings']['chat_avatar']['width'] . 'px; height:' . $PARAMETERS['settings']['chat_avatar']['height'] . 'px;" />';
                }


                $add_chat .= '<span class="chat_time">' . gdrcd_format_time($row['ora']) . '</span>';

                if ($PARAMETERS['mode']['chaticons'] == 'ON') {
                    $add_chat .= $add_icon;
                }

                $add_chat .= '<span class="chat_name"><a href="#" onclick="Javascript: document.getElementById(\'tag\').value=\'' . $row['mittente'] . '\'; document.getElementById(\'type\')[2].selected = \'1\'; document.getElementById(\'message\').focus();">' . $row['mittente'] . '</a>';

                if (empty ($row['destinatario']) === false) {
                    $add_chat .= '<span class="chat_tag"> [' . gdrcd_filter('out', $row['destinatario']) . ']</span>';
                }

                $add_chat .= ': </span> ';
                $add_chat .= '<span class="chat_msg">' . gdrcd_chatcolor(gdrcd_filter('out', $row['testo'])) . '</span>';

                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                if ($PARAMETERS['mode']['chat_avatar'] == 'ON') {
                    $add_chat .= '<br style="clear:both;" />';
                }

                $add_chat .= '</div>';

                break;


            case 'A':
                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                /** * Avatar di chat
                 * @author Blancks
                 */
                if ($PARAMETERS['mode']['chat_avatar'] == 'ON' && !empty($row['url_img_chat'])) {
                    $add_chat .= '<img src="' . $row['url_img_chat'] . '" class="chat_avatar" style="width:' . $PARAMETERS['settings']['chat_avatar']['width'] . 'px; height:' . $PARAMETERS['settings']['chat_avatar']['height'] . 'px;" />';
                }


                $add_chat .= '<span class="chat_time">' . gdrcd_format_time($row['ora']) . '</span>';

                if ($PARAMETERS['mode']['chaticons'] == 'ON') {
                    $add_chat .= $add_icon;
                }

                $add_chat .= '<span class="chat_name"><a href="#" onclick="Javascript: document.getElementById(\'tag\').value=\'' . $row['mittente'] . '\';  document.getElementById(\'type\')[2].selected = \'1\'; document.getElementById(\'message\').focus();">' . $row['mittente'] . '</a>';

                if (empty ($row['destinatario']) === false) {
                    $add_chat .= '<span class="chat_tag"> [' . gdrcd_filter('out', $row['destinatario']) . ']</span>';
                }
                $add_chat .= '</span> ';
                $add_chat .= '<span class="chat_msg">' . gdrcd_chatcolor(gdrcd_filter('out', $row['testo'])) . '</span>';

                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                if ($PARAMETERS['mode']['chat_avatar'] == 'ON') {
                    $add_chat .= '<br style="clear:both;" />';
                }

                $add_chat .= '</div>';

                break;


            case 'S':
                if ($_SESSION['login'] == $row['destinatario']) {
                    /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                     * @author eLDiabolo
                     */
                    $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                    $add_chat .= '<span class="chat_name">' . $row['mittente'] . ' ' . $MESSAGE['chat']['whisper']['by'] . ': </span> ';
                    $add_chat .= '<span class="chat_msg">' . gdrcd_filter('out', $row['testo']) . '</span>';

                    /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                     * @author eLDiabolo
                     */
                    $add_chat .= '</div>';

                } else {
                    if ($_SESSION['login'] == $row['mittente']) {
                        /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                         * @author eLDiabolo
                         */
                        $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                        $add_chat .= '<span class="chat_msg">' . $MESSAGE['chat']['whisper']['to'] . ' ' . gdrcd_filter('out',
                                $row['destinatario']) . ': </span>';
                        $add_chat .= '<span class="chat_msg">' . gdrcd_filter('out', $row['testo']) . '</span>';

                        /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                         * @author eLDiabolo
                         */
                        $add_chat .= '</div>';

                    } else {
                        if (($_SESSION['permessi'] >= MODERATOR) && ($PARAMETERS['mode']['spyprivaterooms'] == 'ON')) {
                            /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                             * @author eLDiabolo
                             */
                            $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                            $add_chat .= '<span class="chat_msg">' . $row['mittente'] . ' ' . $MESSAGE['chat']['whisper']['from_to'] . ' ' . gdrcd_filter('out',
                                    $row['destinatario']) . ' </span>';
                            $add_chat .= '<span class="chat_msg">' . gdrcd_filter('out', $row['testo']) . '</span>';

                            /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                             * @author eLDiabolo
                             */
                            $add_chat .= '</div>';

                        }
                    }
                }
                break;


            case 'N':
                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                $add_chat .= '<span class="chat_time">' . gdrcd_format_time($row['ora']) . '</span>';
                $add_chat .= '<span class="chat_name">' . $row['destinatario'] . '</span> ';
                $add_chat .= '<span class="chat_msg">' . gdrcd_chatcolor(gdrcd_filter('out', $row['testo'])) . '</span>';

                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '</div>';
                break;


            case 'M':
                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                $add_chat .= '<span class="chat_master">' . gdrcd_filter('out', $row['testo']) . '</span>';

                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '</div>';
                break;


            case 'I':
                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                $add_chat .= '<img class="chat_img" src="' . gdrcd_filter('out', $row['testo']) . '" />';

                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '</div>';
                break;


            case 'C':
                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                $add_chat .= '<span class="chat_time">' . gdrcd_format_time($row['ora']) . '</span>';
                $add_chat .= '<span class="chat_msg">' . gdrcd_filter('out', $row['testo']) . '</span>';

                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '</div>';
                break;


            case 'D':
                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                $add_chat .= '<span class="chat_time">' . gdrcd_format_time($row['ora']) . '</span>';
                $add_chat .= '<span class="chat_msg">' . gdrcd_filter('out', $row['testo']) . '</span>';

                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '</div>';
                break;


            case 'O':
                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                $add_chat .= '<span class="chat_time">' . gdrcd_format_time($row['ora']) . '</span>';
                $add_chat .= '<span class="chat_msg">' . gdrcd_filter('out', $row['testo']) . '</span>';

                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */
                $add_chat .= '</div>';
                break;
        }
        $i++;
        $add_chat .= '#stop#';
    }
    $add_chat .= '
            </body>
            </html>
            ';
    /* Scrivo tutto in un file di testo */
    $start = gdrcd_format_datetime_cat($start_time);
    $end = gdrcd_format_datetime_cat($end_time);
    /* Scrivo tutto in un file di testo */
    $file = $start . "-" . $end . "-" . $_SESSION['login'];
    $rand = rand(1, 10000);
    $file = md5($file . $rand);
    $file = $file . ".html";


        $fp = fopen($file, "wb");
        $message = str_replace("#stop#", "\r\n", $add_chat);
        fwrite($fp, $message, 65536);
        fclose($fp);

        /* Do le informazioni di download */
        header("Content-Disposition: attachment; filename=" . urlencode($file));
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Description: File Transfer");
        header("Content-Length: " . filesize($file));

        /* Passo le info del file al browser */
        $fp = fopen($file, "r");
        while (!feof($fp)) {
            print fread($fp, 65536);
            flush();
        }
        fclose($fp);

        /* Elimino il file temporaneo */
        unlink($file);

        /* Chiudo la finestra aperta */

    ?>
        <script language="JavaScript1.2">
            self.close();
        </script>
    <?php
    }

?>

