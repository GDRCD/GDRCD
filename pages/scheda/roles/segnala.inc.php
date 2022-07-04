<?php
############################################

#SEGNALA A GM

############################################
if (SEND_GM) {
    #Inserimento modifiche edit
    if ($_POST['op'] == 'segnala') { ?>
        <form action="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('in', $_REQUEST['pg']); ?>" method="post">
            <div class="form_info">Segnala la role ai GM. Assicurati che i campi "Tag" e "Quest" siano correttamente
                completati ed associa una nota per il GM.</center></div>

            <div class="titolo_box"> Note aggiuntive</div>
            <input name="note" type="text" value=""/>
            <br>
            <div class="form_info">Scrivere la motivazione della segnalazione (esito, obiettivo, etc).</div>
            <br>

                <!--- modifica giocata ---->
                <div class="form_submit">
                    <input type="hidden"
                           name="op"
                           value="segnala_send"/>
                    <input type="hidden"
                           name="id"
                           value="<?php echo gdrcd_filter('num', $_POST['id']); ?>"/>
                    <input type="submit"
                           name="submit"
                           value="Segnala ai GM"/>
                </div>

        </form>
        <div class="link_back">
            <a href="main.php?page=scheda_roles&pg=<?=gdrcd_filter('in', $_REQUEST['pg']);?>">
                <?php echo gdrcd_filter('out',
                    $MESSAGE['interface']['sheet']['link']['back_roles']); ?>
            </a>
        </div>
    <?php }

    #Inserimento segnalazione GM
    if ($_POST['op'] == 'segnala_send') {
        gdrcd_query("INSERT INTO send_GM (data, autore, role_reg, note ) 
                                VALUES ( NOW(), '" . gdrcd_filter('in', $_SESSION['login']) . "', 
                                    " . gdrcd_filter('num', $_POST['id']) . ", 
                                    '" . gdrcd_filter('in', $_POST['note']) . "' )");

        /*Confermo l'operazione*/
        echo '<div class="warning">
            Segnalazione inviata con successo
            </div>
            <div class="link_back">
                <a href="main.php?page=scheda_roles&pg=' . gdrcd_filter('in', $_REQUEST['pg']) . '">'. gdrcd_filter('out',
            $MESSAGE['interface']['sheet']['link']['back_roles']).'
                </a>
            </div>';
    }
}