<div class="pagina_log_messaggi">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if (($_SESSION['permessi'] < MODERATOR) || ($PARAMETERS['mode']['spymessages'] != 'ON')){
        
        echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
    <div class="page_title">
        <h2>Log messaggi</h2>
    </div>
    <div class="page_body">
        <?php
        switch($_REQUEST['op']) {
            case 'view': // Visualizzazione log messaggi
            
                include('gestione/log_messaggi/action.inc.php');
                break;

            default: //Lista pagine
                include('gestione/log_messaggi/index.inc.php');
                break;
        }
    }
        ?>
    </div><!-- page_body -->
</div><!-- Pagina -->
