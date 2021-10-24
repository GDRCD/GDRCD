<?php

/** Homepage
 * Markup e procedure della homepage
 * @author Blancks
 */

/*
 * Includo i Crediti
 */
require 'includes/credits.inc.php';

/*
 * Conteggio utenti online
 */
$users = gdrcd_query("SELECT COUNT(nome) AS online FROM personaggio WHERE ora_entrata > ora_uscita AND DATE_ADD(ultimo_refresh, INTERVAL 4 MINUTE) > NOW()");


?>
<div id="main">
    <div id="site_width">

        <div id="header">
            <div class="titlecontent">
                <h1><a href="index.php"><?php echo $MESSAGE['homepage']['main_content']['site_title']; ?></a></h1>
                <div class="subtitle"><?php echo $MESSAGE['homepage']['main_content']['site_subtitle']; ?></div>
            </div>
            <div class="logincontent">
                <div class="login_form">
                    <form action="login.php" id="do_login" method="post"
                        <?php if ($PARAMETERS['mode']['popup_choise'] == 'ON') { echo ' onsubmit="check_login(); return false;"';} ?>
                    >
                        <div>
                            <span class="form_label"><label for="username"><?php echo $MESSAGE['homepage']['forms']['username']; ?></label></span>
                            <input type="text" id="username" name="login1"/>
                        </div>
                        <div>
                            <span class="form_label"><label for="password"><?php echo $MESSAGE['homepage']['forms']['password']; ?></label></span>
                            <input type="password" id="password" name="pass1"/>
                        </div>
                        <?php if (!empty($PARAMETERS['themes']['available']) and count($PARAMETERS['themes']['available']) > 1): ?>
                            <div>
                                <span class="form_label"><label for="theme"><?= gdrcd_filter('out', $MESSAGE['homepage']['forms']['theme_choice']) ?></label></span>
                                <select name="theme" id="theme">
                                    <?php
                                    foreach ($PARAMETERS['themes']['available'] as $k => $name) {
                                        echo '<option value="' . gdrcd_filter('out', $k) . '"';
                                        if ($k == $PARAMETERS['themes']['current_theme']) {
                                            echo ' selected="selected"';
                                        }
                                        echo '>' . gdrcd_filter('out', $name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <?php if ($PARAMETERS['mode']['popup_choise'] == 'ON') { ?>
                            <div>
                                <span class="form_label"><label for="allow_popup"><?php echo $MESSAGE['homepage']['forms']['open_in_popup']; ?></label></span>
                                <input type="checkbox" id="allow_popup"/>
                                <input type="hidden" value="0" name="popup" id="popup">
                            </div>
                        <?php } ?>
                        <input type="submit" value="<?php echo $MESSAGE['homepage']['forms']['login']; ?>"/>
                    </form>
                </div>
            </div>
        </div>

        <div id="content">
            <div class="sidecontent">
                <ul>
                    <li>
                        <a href="index.php?page=homepage&content=iscrizione"><?php echo $MESSAGE['homepage']['registration']; ?></a>
                    </li>
                    <li>
                        <a href="index.php?page=homepage&content=user_regolamento"><?php echo $MESSAGE['homepage']['rules']; ?></a>
                    </li>
                    <li>
                        <a href="index.php?page=homepage&content=user_ambientazione"><?php echo $MESSAGE['homepage']['storyline']; ?></a>
                    </li>
                    <li>
                        <a href="index.php?page=homepage&content=user_razze"><?php echo $MESSAGE['homepage']['races']; ?></a>
                    </li>
                </ul>

                <div class="side_modules">
                    <strong><?php echo $users['online'], ' ', gdrcd_filter('out', $MESSAGE['homepage']['forms']['online_now']); ?></strong>
                </div>

                <div class="side_modules">
                    <?php
                        // Include il modulo di reset della password
                        include (__DIR__ . '/reset_password.inc.php');
                    ?>
                </div>

                <div class="side_modules">
                    <?php
                        // Include le statistiche del sito
                        include (__DIR__ . '/user_stats.inc.php');
                    ?>
                </div>
            </div>

            <div class="content_body">
                <?php
                    gdrcd_load_modules('homepage__'.$MODULE['content']);

                    ?>
            </div>
            <br class="blank"/>
        </div>

        <div id="footer">
            <div>
                <p><?=$REFERENCES?></p>
                <p><?=$CREDITS,' ',$LICENCE?></p>
            </div>
        </div>
    </div>
</div>
