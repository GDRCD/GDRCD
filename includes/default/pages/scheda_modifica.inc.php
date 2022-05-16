<div class="pagina_schedam_odifica">
    <?php /*HELP: */

    if (isset($_REQUEST['pg']) === false)
    {
        echo gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']);
    } else
    {
    /*Se ho ricevuto informazioni per modificare*/


    $confirm_updating = true;


    /** * Controllo sul file audio
     * @author Blancks
     */
    if ($PARAMETERS['mode']['allow_audio'] == 'ON')
    {

        if ( ! empty($_POST['modifica_url_media']) && ! isset($PARAMETERS['settings']['audiotype']['.' . strtolower(end(explode('.',
                    $_POST['modifica_url_media'])))])
        )
        {
            echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['media_not_allowed']) . '</div>';
            $confirm_updating = false;
        }


    } elseif ($_POST['op'] == 'modify')
    {
        $_POST['modifica_url_media'] = '';
    }


    /** * Se non sono occorsi errori durante i controlli precedenti
     * @author Blancks
     */
    if ($confirm_updating)
    {

        if (isset($_POST['op']) === true)
        {

            /** * Controllo sul bloccaggio dei suoni per l'utente
             * @author Blancks
             */
            $blocca_media = (strtolower($_POST['blocca_media']) == 'on') ? 1 : 0;

            if ($_SESSION['login'] == $_REQUEST['pg'])
            {
                $_SESSION['blocca_media'] = $blocca_media;
            }


            /*Se l'utente ha richiesto di modificare la propria scheda*/
            if ((gdrcd_filter('get', $_REQUEST['pg']) == $_SESSION['login']) && (gdrcd_filter('get',
                        $_POST['op']) == 'modify')
            )
            {
                /** * Html, BBcode or both ?
                 * @author Blancks
                 */
                $modifica_affetti = gdrcd_filter('in', $_POST['modifica_affetti']);
                $modifica_background = gdrcd_filter('in', $_POST['modifica_background']);
                $modifica_storia = gdrcd_filter('in', $_POST['modifica_storia']);


                if ($PARAMETERS['mode']['user_bbcode'] == 'OFF' || ($PARAMETERS['mode']['user_bbcode'] == 'ON' && $PARAMETERS['settings']['forum_bbcode']['type'] == 'bbd' && $PARAMETERS['settings']['bbd']['free_html'] == 'ON'))
                {
                    $modifica_affetti = gdrcd_filter('addslashes', $_POST['modifica_affetti']);
                    $modifica_background = gdrcd_filter('addslashes', $_POST['modifica_background']);
                    $modifica_storia = gdrcd_filter('addslashes', $_POST['modifica_storia']);
                }

                /** * Online status allowed ?
                 * @author Blancks
                 */
                $online_state = '';

                if ($PARAMETERS['mode']['user_online_state'] == 'ON')
                {
                    $online_state = gdrcd_filter('in', $_POST['online_state']);
                }


                gdrcd_query("UPDATE personaggio SET cognome = '" . gdrcd_filter('in',
                        $_POST['modifica_cognome']) . "', storia = '" . $modifica_storia . "',  affetti = '" . $modifica_affetti . "', descrizione = '" . $modifica_background . "', url_media = '" . gdrcd_filter('in',
                        gdrcd_filter('fullurl',
                            $_POST['modifica_url_media'])) . "', blocca_media = " . (int) $blocca_media . ", url_img = '" . gdrcd_filter('in',
                        gdrcd_filter('fullurl',
                            $_POST['modifica_url_img'])) . "', url_img_chat = '" . gdrcd_filter('in',
                        gdrcd_filter('fullurl',
                            $_POST['modifica_url_img_chat'])) . "', online_status = '" . $online_state . "' WHERE nome = '" . gdrcd_filter('in',
                        $_REQUEST['pg']) . "'");

                echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['modified']) . '</div>';


                /*Se un master o superiore ha richiesto di modificare lo status del pg*/
            } elseif (($_SESSION['permessi'] >= GUILDMODERATOR) && (gdrcd_filter('get',
                        $_POST['op']) == 'modify_status')
            )
            {
                gdrcd_query("UPDATE personaggio SET stato = '" . gdrcd_filter('in',
                        $_POST['modifica_status']) . "', salute = " . gdrcd_filter('num',
                        $_POST['modifica_salute']) . " WHERE nome = '" . gdrcd_filter('in', $_REQUEST['pg']) . "'");

                echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['modified']) . '</div>';

                /*Se un master o superiore ha richiesto l'arresto del pg*/
            } elseif (($_SESSION['permessi'] >= GAMEMASTER) && (gdrcd_filter('get', $_POST['op']) == 'arrest'))
            {
                /** * Da implementare */


                /*Se un admin o superiore ha richiesto l'esilio dell'utente*/
            } elseif (($_SESSION['permessi'] >= GAMEMASTER) && (gdrcd_filter('get', $_POST['op']) == 'exile'))
            {
                gdrcd_query("UPDATE personaggio SET esilio = '" . gdrcd_filter('num',
                        $_POST['year']) . '-' . gdrcd_filter('num', $_POST['month']) . '-' . gdrcd_filter('num',
                        $_POST['day']) . "', data_esilio=NOW(), autore_esilio = '" . $_SESSION['login'] . "', motivo_esilio = '" . gdrcd_filter('in',
                        $_POST['causale']) . "' WHERE nome = '" . gdrcd_filter('in',
                        $_REQUEST['pg']) . "' AND permessi <=" . $_SESSION['permessi'] . "");

                echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['done']) . '</div>';

            } else
            {
                echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknown_operation']) . '</div>';

            }

        } else
        {
            /*Carico le informazioni del PG*/
            $record = gdrcd_query("SELECT descrizione, storia, affetti, cognome, online_status, url_img, url_img_chat, url_media, blocca_media, stato, salute FROM personaggio WHERE nome='" . gdrcd_filter('get',
                    $_REQUEST['pg']) . "'");
        }

    }
    ?>

    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['page_name']); ?></h2>
    </div>

    <div class="page_body">
        <?php if (isset($_POST['op']) === false)
        { ?>
            <div class="panels_box">
                <?php
                if ($_SESSION['login'] == $_REQUEST['pg'])
                {
                    ?>
                    <div class="form_gioco">
                        <!-- Form utente modifica -->
                        <form action="main.php?page=scheda_modifica" method="post">

                            <div class='form_label'>
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['modify_form']['last_name']); ?>
                            </div>
                            <div class='form_field'>
                                <input type="text" name="modifica_cognome"
                                       value="<?php echo gdrcd_filter('out', $record['cognome']); ?>"
                                       class="form_input"/>
                            </div>

                            <div class='form_label'>
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['modify_form']['url_img']); ?>
                            </div>
                            <div class='form_field'>
                                <input type="text" name="modifica_url_img"
                                       value="<?php echo gdrcd_filter('out', $record['url_img']); ?>"
                                       class="form_input"/>
                            </div>
                            <?php
                            /** * Avatar di chat
                             * @author Blancks
                             */
                            if ($PARAMETERS['mode']['chat_avatar'] == 'ON')
                            {
                                ?>
                                <div class='form_label'>
                                    <?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['modify_form']['url_img_chat']); ?>
                                </div>
                                <div class='form_field'>
                                    <input type="text" name="modifica_url_img_chat"
                                           value="<?php echo gdrcd_filter('out', $record['url_img_chat']); ?>"
                                           class="form_input"/>
                                </div>
                                <?php
                            }
                            ?>


                            <?php
                            if ($PARAMETERS['mode']['user_online_state'] == 'ON')
                            {
                                ?>
                                <div class='form_label'>
                                    <?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['modify_form']['online_state']); ?>
                                </div>
                                <div class='form_field'>
                                    <input type="text" name="online_state" class="form_input"
                                           value="<?php echo gdrcd_filter('out', $record['online_status']); ?>"
                                           maxlength="100"/>
                                </div>
                                <?php
                            }
                            ?>

                            <div class='form_label'>
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['menu']['description']); ?>
                            </div>
                            <div class='form_field'>
                                <textarea type="textbox" name="modifica_background"
                                          class="form_textarea"><?php echo gdrcd_filter('out',
                                        $record['descrizione']); ?></textarea>
                            </div>
                            <div class="form_info">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
                            </div>
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['modify_form']['background']); ?>
                            </div>
                            <div class='form_field'>
                                <textarea type="textbox" name="modifica_storia"
                                          class="form_textarea"><?php echo gdrcd_filter('out',
                                        $record['storia']); ?></textarea>
                            </div>
                            <div class="form_info">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
                            </div>

                            <div class='form_label'>
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['modify_form']['relationships']); ?>
                            </div>
                            <div class='form_field'>
                                <textarea type="textbox" name="modifica_affetti"
                                          class="form_textarea"><?php echo gdrcd_filter('out',
                                        $record['affetti']); ?></textarea>
                            </div>
                            <div class="form_info">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
                            </div>

                            <div class='form_label'>
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['modify_form']['block_media']); ?>
                            </div>
                            <div class='form_field'>
                                <input type="checkbox"
                                       name="blocca_media" <?php echo ($record['blocca_media']) ? 'checked="checked"' : ''; ?>
                                       class="form_input"/>
                            </div>

                            <?php
                            if ($PARAMETERS['mode']['allow_audio'] == 'ON')
                            {
                                ?>
                                <div class='form_label'>
                                    <?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['modify_form']['url_media']); ?>
                                </div>
                                <div class='form_field'>
                                    <input type="text" name="modifica_url_media"
                                           value="<?php echo gdrcd_filter('out', $record['url_media']); ?>"
                                           class="form_input"/>
                                </div>
                                <?php
                            }
                            ?>
                            <input type="hidden" name="op" value="modify"/>

                            <div class='form_submit'>
                                <input type="submit" value="<?php echo $MESSAGE['interface']['forms']['submit']; ?>"
                                       class="form_submit"/>
                                <input type="hidden"
                                       value="<?php echo gdrcd_filter('get', $_REQUEST['pg']); ?>"
                                       name="pg"/>
                            </div>

                        </form>
                    </div>

                    <?php
                }//if
                if ($_SESSION['permessi'] >= GUILDMODERATOR)
                {
                    ?>

                    <div class='form_gioco'>

                        <!-- Form master status -->
                        <form action="main.php?page=scheda_modifica" method="post">

                            <input type="hidden" name="op" value="modify_status"/>

                            <div class='form_label'>
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['modify_form']['status']); ?>
                            </div>
                            <div class='form_field'>
                                <textarea type="textbox" name="modifica_status"
                                          class="form_textarea"><?php echo gdrcd_filter('out',
                                        $record['stato']); ?></textarea>
                            </div>

                            <div class='form_label'>
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['modify_form']['healt']); ?>
                            </div>
                            <div class='form_field'>
                                <input class="healt_input" name="modifica_salute"
                                       value="<?php echo $record['salute']; ?>"/>/100
                            </div>

                            <div class='form_submit'>
                                <input type="submit" value="<?php echo $MESSAGE['interface']['forms']['submit']; ?>"/>
                                <input type="hidden"
                                       value="<?php echo gdrcd_filter('get', $_REQUEST['pg']); ?>"
                                       name="pg"/>
                            </div>

                        </form>
                        <!-- Form master esilio -->
                        <form action="main.php?page=scheda_modifica" method="post">


                            <input type="hidden" name="op" value="exile"/>

                            <div class='form_label'>
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['modify_form']['exile']); ?>
                            </div>
                            <div class='form_field'>
                                <!-- Giorno -->
                                <select name="day" class="day">
                                    <?php for ($i = 1; $i <= 31; $i++)
                                    { ?>
                                        <option value="<?php echo $i; ?>" <?php if (strftime('%d') == $i)
                                        {
                                            echo 'selected';
                                        } ?>><?php echo $i; ?></option>
                                    <?php }//for
                                    ?>
                                </select>
                                <!-- Mese -->
                                <select name="month" class="month">
                                    <?php for ($i = 1; $i <= 12; $i++)
                                    { ?>
                                        <option value="<?php echo $i; ?>" <?php if (strftime('%m') == $i)
                                        {
                                            echo 'selected';
                                        } ?>><?php echo $i; ?></option>
                                    <?php }//for
                                    ?>
                                </select>
                                <!-- Anno -->
                                <select name="year" class="year">
                                    <?php for ($i = strftime('%Y'); $i <= strftime('%Y') + 20; $i++)
                                    { ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php }//for
                                    ?>
                                </select>
                            </div>
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['modify_form']['why_exiled']); ?>
                            </div>
                            <div class='form_field'>
                                <input name="causale"/>
                            </div>

                            <div class='form_submit'>
                                <input type="submit" value="<?php echo $MESSAGE['interface']['forms']['submit']; ?>"/>
                                <input type="hidden"
                                       value="<?php echo gdrcd_filter('get', $_REQUEST['pg']); ?>"
                                       name="pg"/>
                            </div>

                        </form>


                    </div>
                    <?php
                }//if
                ?>
            </div>

            <?php
        }//else

        }//if?>


    </div>
    <!-- Link a piè di pagina -->
    <div class="link_back">
        <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',
            $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['link']['back']); ?></a>
    </div>

</div><!-- pagina -->