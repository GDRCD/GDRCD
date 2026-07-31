<div class="pagina_config">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if (($_SESSION['permessi'] < MODERATOR)  ){

        
        echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
    <div class="page_title">
        <h2>Gilde</h2>
    </div>
    <div class="page_body">
        <?php
        switch($_REQUEST['op']) {
            case 'save_new': // Salvataggio nuovo
            case 'save_edit': // Salvataggio modifica
            case 'save_delete': // Eliminazione
        
                include('gestione/gilde/save.inc.php');

                break;
            case 'new': // Nuova gilde
                include('gestione/gilde/new.inc.php');
                break;
            case 'edit': // Modifica gilde
                include('gestione/gilde/edit.inc.php');
                break;
            
            default: //Lista pagine
                include('gestione/gilde/index.inc.php');
                break;
        }
    }
        ?>
    </div><!-- page_body -->
</div><!-- Pagina -->
