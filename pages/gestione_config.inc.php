<div class="pagina_config">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if (($_SESSION['permessi'] < SUPERUSER)  ){

        
        echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
    <div class="page_title">
        <h2>Configurazioni</h2>
    </div>
    <div class="page_body">
        <?php
        switch($_REQUEST['op']) {
            case 'save_config': // Salvataggio modifiche
                include('gestione/config/save.inc.php');
                break;

            default: //Lista pagine
                include('gestione/config/index.inc.php');
                break;
        }
    }
        ?>
    </div><!-- page_body -->
</div><!-- Pagina -->
