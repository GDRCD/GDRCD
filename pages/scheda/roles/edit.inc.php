<?php
if ($_SESSION['permessi'] >= EDIT_PERM || $_REQUEST['pg'] == $_SESSION['login']) {
    #Inserimento modifiche edit
    if ($_POST['op'] == 'send_edit') {
        gdrcd_query("UPDATE segnalazione_role SET tags = '" . gdrcd_filter('in', $_POST['ab']) . "', 
        quest = '" . gdrcd_filter('in', $_POST['quest']) . "' WHERE id = " . gdrcd_filter('num', $_POST['id']) . " ");

        /*Confermo l'operazione*/
        echo '<div class="warning">Registrazione modificata con successo</div>
        <div class="link_back">
            <a href="main.php?page=scheda_roles&pg=' . gdrcd_filter('in', $_REQUEST['pg']) . '">
            '. gdrcd_filter('out',
            $MESSAGE['interface']['sheet']['link']['back_roles']).'
            </a>
        </div>';
    }

    #FORM di modifica dei campi quest e tag
    if ($_POST['op'] == 'edit') {
        $query = gdrcd_query("SELECT * FROM segnalazione_role WHERE id = " . $_POST['id'] . " ", "result");
        $row = gdrcd_query($query, 'fetch');
        ?>
        <div class="form_info">In questo pannello puoi modificare i campi "Tag" e "Quest" delle tue giocate. La modifica
            è possibile
            solo entro<b> 30 giorni</b> dalla giocata.
        </div><br>
        <!-- Chat -->
        <form action="main.php?page=scheda_roles&pg=<?=gdrcd_filter('in', $_REQUEST['pg']);?>" method="post">

            <div class='form_field'>
                <div class="titolo_box">Inserisci dei <b>tag</b> che riassumano la giocata:</div>
                <input name="ab" type="text" value="<?=$row['tags'];?>"/></div>
            <div class="form_info">I tag possono essere utili per ritrovare rapidamente una role.</div>

            <div class="titolo_box"> Note quest</div>
            <input name="quest" type="text" value="<?=$row['quest'];?>"/>
            <div class="form_info">Compilare con un brevissimo riassunto di cosa fatto in giocata, focalizzandosi sulle
                interazioni con eventuali spunti di trama.<br>
                <b>N.B.</b> In assenza di una segnalazione, un GM non riceve alcuna notifica e non può pertanto
                intervenire.
            </div>
            <br>
            <!--- modifica giocata ---->
            <div class="form_submit">
                <input type="hidden"
                       name="op"
                       value="send_edit"/>
                <input type="hidden"
                       name="id"
                       value="<?php echo gdrcd_filter('num', $_POST['id']); ?>"/>
                <input type="submit"
                       name="submit"
                       value="Modifica la giocata"/>
            </div>
        </form>
        <div class="link_back">
            <a href="main.php?page=scheda_roles&pg=<?=gdrcd_filter('in', $_REQUEST['pg']);?>">
                <?php echo gdrcd_filter('out',
                    $MESSAGE['interface']['sheet']['link']['back_roles']); ?>
            </a>
        </div>
        <?php
    }

} else {
    echo '<div class="warning">Non hai i permessi adatti per modificare una registrazione</div>';
} ?>