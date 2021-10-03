<div class="panels_box">
    <form class="form_messaggi" action="main.php?page=messages_center" method="post">
        <!-- Destinatario -->
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['recipient']); ?>
        </div>
        <div class="form_info">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['multiple']['info']); ?>
        </div>
        <div class='form_field'>
            <input type="text" list="personaggi" name="destinatario" placeholder="Nome del personaggio" value="<?php echo gdrcd_filter('get', $_REQUEST['reply_dest']); ?>" />
        </div>
        <?php
            // Costruisco la lista dei Personaggio da cui attingere per il datalist
            echo gdrcd_list('personaggi');

            // Controllo sui permessi per gli invii particolari
            if($_SESSION['permessi'] >= GUILDMODERATOR) {
                ?>
                <div class="form_field">
                    <select name="multipli">
                        <option value="private" selected>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['multiple']['options']['private']); ?>
                        </option>
                        <option value="presenti">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['multiple']['options']['online']); ?>
                        </option>
                        <?php if($_SESSION['permessi'] >= MODERATOR) { ?>
                            <option value="broadcast">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['multiple']['options']['all']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <?php
            } //if
        ?>
        <!-- Oggetto -->
        <div class='form_label'><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['subject']); ?></div>
        <div class='form_field'>
            <!--Il placeholder è il testo che compare sul campo prima che l'utente vi scriva-->
            <input type="text" name="oggetto" placeholder="Oggetto o dettaglio ON/OFF"
                   value="<?php echo gdrcd_filter('out', trim($_REQUEST['reply_subject'])); ?>"/>
        </div>
        <!-- Testo -->
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['body']); ?>
        </div>
        <div class='form_field'>
 	  	    <textarea type="textbox" name="testo">
                <?php
                /**    * Fix per evitare le parentesi quadre vuote quando si compone un nuovo messaggio
                 * @author Blancks
                 */
                if(isset($_POST['testo'])) {
                    echo "\n\n\n[".gdrcd_filter('out', trim($_POST['testo']))."]";
                }
                ?>
            </textarea>
        </div>
        <div class="form_info">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
        </div>
        <!-- Submit -->
        <input type="hidden" name="op" value="send_message" />
        <input type="hidden" name="reply_attach" value="<?php echo gdrcd_filter('get', $_POST['reply_attach']); ?>" />
        <div class='form_submit'>
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
        </div>
    </form>
</div>