<div class="pagina_config">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if (($_SESSION['permessi'] < MODERATOR)  ){       
        echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
    <div class="page_title">
        <h2>Bacheche</h2>
    </div>
    <div class="page_body">
        <?php
        switch($_REQUEST['op']) {
            case 'save_new': // Nuova
            case 'save_edit': // Modifica
            case 'save_delete': // Eliminazione
                include('gestione/bacheche/save.inc.php');
                break;
            case 'new': //Nuova
                include('gestione/bacheche/new.inc.php');
                break;
            case 'edit': //Modifica
                include('gestione/bacheche/edit.inc.php');
                break;
            default: //Lista pagine
                include('gestione/bacheche/index.inc.php');
                break;
        }
    }
        ?>
    </div><!-- page_body -->
</div><!-- Pagina -->
