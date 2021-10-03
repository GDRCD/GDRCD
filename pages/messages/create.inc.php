<div class="panels_box">
    <form class="form_messaggi" action="main.php?page=messages_center" method="post">
        <!-- Destinatario -->
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['recipient']); ?>
        </div>
        <div class='form_field'>
            <input type="text" list="personaggi" name="destinatario" placeholder="Nome del personaggio" value="<?php echo gdrcd_filter('get', $_REQUEST['reply_dest']); ?>" />
        </div>
        <?php
        echo gdrcd_list('personaggi');
        if($_SESSION['permessi'] >= GUILDMODERATOR) { ?>
            <div class="form_field">
                <select name="multipli">
                    <option value="singolo" selected>
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['multiple']['single']); ?>
                    </option>
                    <option value="multiplo">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['multiple']['multiple']); ?>
                    </option>
                    <option value="presenti">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['multiple']['online']); ?>
                    </option>
                    <?php if($_SESSION['permessi'] >= MODERATOR) { ?>
                        <option value="broadcast">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['multiple']['all']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <?php
        } //if
        ?>
        <div class="form_info">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['multiple']['info']); ?>
        </div>
        <!-- Testo -->
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['body']); ?>
        </div>
        <div class='form_field'>
 	  	    <textarea type="textbox" name="testo"><?php
                /**    * Fix per evitare le parentesi quadre vuote quando si compone un nuovo messaggio
                 * @author Blancks
                 */
                if(isset($_POST['testo'])) {
                    echo "\n\n\n[".gdrcd_filter('out', trim($_POST['testo']))."]";
                }
                ?></textarea>
        </div>
        <!-- Submit -->
        <input type="hidden" name="op" value="send_message" />
        <input type="hidden" name="reply_attach" value="<?php echo gdrcd_filter('get', $_POST['reply_attach']); ?>" />
        <div class='form_submit'>
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
        </div>
    </form>
</div>
<div class="link_back">
    <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['go_back']); ?></a>
</div>