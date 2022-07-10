<?php
if($_SESSION['permessi'] >= LOG_PERM) {
    $pg = $_REQUEST['pg'];
} else {
    $pg = $_SESSION['login'];
}
$typeOrder = ($PARAMETERS['mode']['chat_from_bottom'] == 'ON') ? 'DESC' : 'ASC';

$check = gdrcd_query("SELECT * FROM segnalazione_role WHERE id = " . gdrcd_filter('num', $_POST['id']) . " 
    AND mittente = '" .gdrcd_filter('in', $pg ). "' AND conclusa = 1", 'result');
$num_check = gdrcd_query($check, 'num_rows');
$check_f= gdrcd_query($check, 'fetch');
if ($num_check == 0) {
    echo 'Non hai accesso a questo log chat';
} else {
    ?>

    <div class="page_title">
        <h2>Log chat</h2>
    </div>
    <div class="log_roles">
        <?php
        //
        $name = gdrcd_query(" SELECT nome FROM mappa WHERE id = " . $check_f['stanza'] . "", 'result');
        $r_nam = gdrcd_query($name, 'fetch');

        $query = gdrcd_query("	SELECT chat.id, chat.imgs, chat.mittente, chat.destinatario, chat.tipo, chat.ora, 
	                        chat.testo, personaggio.url_img_chat
							FROM chat
							INNER JOIN mappa ON mappa.id = chat.stanza
							LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
							WHERE stanza = " . $check_f['stanza'] . " AND ora >= '" . gdrcd_filter('in', $check_f['data_inizio']) . "' 
							AND ora <= '" . gdrcd_filter('in', $check_f['data_fine']) . "' 
							ORDER BY ora " . $typeOrder, 'result');


        $num = gdrcd_query($query, 'num_rows');

        //Recupero dei partecipanti -> pg che hanno giocato in quella chat alla stessa ora.
        /* Se esistono record */
        if ($num > 0) {

            echo '<div style="text-align:center;">' . gdrcd_format_date($_POST['inizio']) . '</div>';
            //Titolo del log
            echo '<div class="titolo_box">' . $r_nam['nome'] . '</div>';
            /* Eseguo la query e le formattazioni */
            while ($row = gdrcd_query($query, 'fetch')) {

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
                            $add_chat .= '<img src="' . $row['url_img_chat'] . '" class="chat_avatar" 
                            style="width:' . $PARAMETERS['settings']['chat_avatar']['width'] . 'px; 
                            height:' . $PARAMETERS['settings']['chat_avatar']['height'] . 'px;" />';
                        }


                        $add_chat .= '<span class="chat_time">' . gdrcd_format_time($row['ora']) . '</span>';

                        if ($PARAMETERS['mode']['chaticons'] == 'ON') {
                            $add_chat .= $add_icon;
                        }

                        $add_chat .= '<span class="chat_name">
                            <a href="#" onclick="Javascript: document.getElementById(\'tag\').value=\'' . $row['mittente'] . '\'; document.getElementById(\'type\')[2].selected = \'1\'; document.getElementById(\'message\').focus();">
                                ' . $row['mittente'] . '
                            </a>';

                        if (empty ($row['destinatario']) === FALSE) {
                            $add_chat .= '<span class="chat_tag"> [' . gdrcd_filter('out', $row['destinatario']) . ']</span>';
                        }

                        $add_chat .= ': </span> ';
                        $add_chat .= '<span class="chat_msg">' . gdrcd_chatcolor(gdrcd_filter('out', $row['testo'])) . '</span>';

                        /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                         * @author eLDiabolo
                         */
                        if ($PARAMETERS['mode']['chat_avatar'] == 'ON')
                            $add_chat .= '<br style="clear:both;" />';

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
                            $add_chat .= '<img src="' . $row['url_img_chat'] . '" class="chat_avatar" 
                            style="width:' . $PARAMETERS['settings']['chat_avatar']['width'] . 'px; 
                            height:' . $PARAMETERS['settings']['chat_avatar']['height'] . 'px;" />';
                        }


                        $add_chat .= '<span class="chat_time">' . gdrcd_format_time($row['ora']) . '</span>';

                        if ($PARAMETERS['mode']['chaticons'] == 'ON') {
                            $add_chat .= $add_icon;
                        }

                        $add_chat .= '<span class="chat_name"><a href="#" onclick="Javascript: document.getElementById(\'tag\').value=\'' . $row['mittente'] . '\';  document.getElementById(\'type\')[2].selected = \'1\'; document.getElementById(\'message\').focus();">' . $row['mittente'] . '</a>';

                        if (empty ($row['destinatario']) === FALSE) {
                            $add_chat .= '<span class="chat_tag"> [' . gdrcd_filter('out', $row['destinatario']) . ']</span>';
                        }
                        $add_chat .= '</span> ';
                        $add_chat .= '<span class="chat_msg">' . gdrcd_chatcolor(gdrcd_filter('out', $row['testo'])) . '</span>';

                        /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                         * @author eLDiabolo
                         */
                        if ($PARAMETERS['mode']['chat_avatar'] == 'ON')
                            $add_chat .= '<br style="clear:both;" />';

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

                        } else if ($_SESSION['login'] == $row['mittente']) {
                            /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                             * @author eLDiabolo
                             */
                            $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                            $add_chat .= '<span class="chat_msg">' . $MESSAGE['chat']['whisper']['to'] . ' ' . gdrcd_filter('out', $row['destinatario']) . ': </span>';
                            $add_chat .= '<span class="chat_msg">' . gdrcd_filter('out', $row['testo']) . '</span>';

                            /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                             * @author eLDiabolo
                             */
                            $add_chat .= '</div>';

                        } else if (($_SESSION['permessi'] >= MODERATOR) && ($PARAMETERS['mode']['spyprivaterooms'] == 'ON')) {
                            /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                             * @author eLDiabolo
                             */
                            $add_chat .= '<div class="chat_row_' . $row['tipo'] . '">';

                            $add_chat .= '<span class="chat_msg">' . $row['mittente'] . ' ' . $MESSAGE['chat']['whisper']['from_to'] . ' ' . gdrcd_filter('out', $row['destinatario']) . ' </span>';
                            $add_chat .= '<span class="chat_msg">' . gdrcd_filter('out', $row['testo']) . '</span>';

                            /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                             * @author eLDiabolo
                             */
                            $add_chat .= '</div>';

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
            }
            echo $add_chat;
        } else {
            echo 'Nessun record';
        } ?>
    </div>

    <div class="link_back">
        <a href="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('in', $_REQUEST['pg']); ?>">
            <?php echo gdrcd_filter('out',
                $MESSAGE['interface']['sheet']['link']['back_roles']); ?>
        </a>
    </div>
<?php }