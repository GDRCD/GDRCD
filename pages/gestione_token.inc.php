<div class="pagina_token">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if (($_SESSION['permessi'] < SUPERUSER) || ($PARAMETERS['mode']['spymessages'] != 'ON')){

        
        echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
    <div class="page_body">
        <?php
        switch($_REQUEST['op']) {
            case 'genera_token': // Generazione nuovo token
            case 'elimina_token': // Eliminazione token
                include('gestione/token/save.inc.php');
                break;

            default: //Lista pagine
                include('gestione/token/index.inc.php');
                break;
        }
    }
        ?>
    </div><!-- page_body -->
</div><!-- Pagina -->
