<div class="pagina_log_chat">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if (($_SESSION['permessi'] < MODERATOR) || ($PARAMETERS['mode']['spymessages'] != 'ON')){
        
        echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
    <div class="page_title">
        <h2>Log chat</h2>
    </div>
    <div class="page_body">
        <?php
        switch($_REQUEST['op']) {
            case 'view_user': // Salvataggio modifiche
            case 'view_date': // Salvataggio modifiche
                include('gestione/log_chat/save.inc.php');
                break;

            default: //Lista pagine
                include('gestione/log_chat/index.inc.php');
                break;
        }
    }
        ?>
    </div><!-- page_body -->
</div><!-- Pagina -->
