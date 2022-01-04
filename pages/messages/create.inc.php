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
            <input type="text" list="personaggi" name="destinatario" placeholder="Nome del personaggio" value="<?=gdrcd_filter('get', ($_POST['destinatario'] ?: $_GET['destinatario']));?>" required />
        </div>
        <?php
            // Costruisco la lista dei Personaggio da cui attingere per il datalist
            echo gdrcd_list('personaggi');

            // Controllo sui permessi per gli invii particolari
            if($_SESSION['permessi'] >= GUILDMODERATOR) {
                ?>
                <div class="form_field">
                    <select name="multipli" required>
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
        <!-- Tipo -->
        <div class='form_label'><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['type']['title']); ?></div>
        <div class='form_field'>
            <select name="tipo" required>
                <?php
                // Costruisco le opzioni per la tipologia di messaggio
                foreach($MESSAGE['interface']['messages']['type']['options'] AS $tipoID => $tipoNome) {
                    // Determino se il tipo che sto costruendo è da impostare come selezionato di default
                    $isSelected = ($_POST['reply_tipo'] == $tipoID ? 'selected' : NULL);
                    echo '<option value="'.$tipoID.'" '.$isSelected.'>'.gdrcd_filter('out', $tipoNome).'</option>';
                }
                ?>
            </select>
        </div>
        <!-- Oggetto -->
        <div class='form_label'><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['subject']); ?></div>
        <div class='form_field'>
            <!--Il placeholder è il testo che compare sul campo prima che l'utente vi scriva-->
            <input type="text" name="oggetto" placeholder="Oggetto del messaggio" value="<?php echo gdrcd_filter('out', trim($_POST['oggetto'])); ?>" required/>
        </div>
        <!-- Testo -->
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['body']); ?>
        </div>
        <div class='form_field'>
 	  	    <textarea type="textbox" name="testo" required><?php
                /**    * Fix per evitare le parentesi quadre vuote quando si compone un nuovo messaggio
                 * @author Blancks
                 */
                if(isset($_POST['testo'])) {
                    echo "\n\n\n[".gdrcd_filter('out', trim($_POST['testo']))."]";
                }
                ?></textarea>
        </div>
        <div class="form_info">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
        </div>
        <!-- Submit -->
        <input type="hidden" name="op" value="send_message" />
        <div class='form_submit'>
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
        </div>
    </form>
</div>
<div class="link_back">
    <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['go_back']); ?></a>
</div>