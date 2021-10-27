<div class="pagina_gestione_manutenzione">
    <?php
    /*HELP: */
    /*Controllo permessi utente*/
    if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else {
        ?>
        <!-- Titolo della pagina -->
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['page_name']); ?></h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php
            /*
             * Richieste POST
             */
            switch(Filters::get($_POST['op'])) {
                case 'blacklisted': //Elimina blacklist
                    include ('gestione/manutenzione/blacklisted.inc.php');
                    break;
                case 'deleted': //Elimina personaggi che non si loggano più
                    include ('gestione/manutenzione/deleted.inc.php');
                    break;
                case 'old_chat': //Elimina vecchi log
                    include ('gestione/manutenzione/old_chat.inc.php');
                    break;
                case 'old_log': //Elimina vecchi log
                    include ('gestione/manutenzione/old_log.inc.php');
                    break;
                case 'old_messages': //Elimina vecchi messaggi
                    include ('gestione/manutenzione/old_messages.inc.php');
                    break;
                case 'missing': //Elimina personaggi che non si loggano più
                    include ('gestione/manutenzione/missing.inc.php');
                    break;
                default: //Form di manutenzione
                    include ('gestione/manutenzione/index.inc.php');
                    break;
            }
            echo '</div>'; //<!-- page_body -->
    } //else
    ?>
</div><!-- pagina -->
