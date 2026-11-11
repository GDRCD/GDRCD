<?php /*HELP: */
    /*Controllo permessi utente*/
if(($_SESSION['permessi'] < MODERATOR) || ($PARAMETERS['mode']['spymessages'] != 'ON')) {
    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    return;
}?>
<!-- Corpo della pagina -->
<div class="page_body">

    <!-- Form di inserimento/modifica -->
    <div class="panels_box">
        <div class="form_gestione">
            <form action="main.php?page=log_messaggi" method="post">
                <?php
                $result = gdrcd_stmt_all("SELECT nome, id_personaggio 
                                    FROM personaggio 
                                    WHERE permessi > ?  
                                    ORDER BY nome", [DELETED]); 
                ?>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['messages']['log_type']); ?>
                </div>
                <div class='form_field'>
                    <select name="pg">
                        <?php 
                        foreach($result as $row) { ?>
                            <option value="<?php echo gdrcd_filter('out', $row['id_personaggio']); ?>">
                                <?php echo gdrcd_filter('out', $row['nome']); ?>
                            </option>
                        <?php 
                        }//foreach
                        ?>
                    </select>
                </div>
                <!-- bottoni -->
                <div class='form_submit'>
                    <input type="hidden" value="view" name="op" />
                    <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                </div>
            </form>
        </div>
    </div>
    <div class="link_back">
        <a href="main.php?page=gestione">Torna indietro</a>
    </div>
</div>
