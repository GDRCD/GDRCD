<?php
include_once('../header.inc.php');
 if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        return;
    } 
?>
<div class="pagina_gestione_abilita">
    
  
    <div class="page_body">
        <?php
            switch(gdrcd_filter_get($_REQUEST['op'])) {

                case 'edit': //Form modifica pagina
                    include('gestione/ambientazione/edit.inc.php');
                    break;

                case 'new': // Form nuova pagina
                    include('gestione/ambientazione/new.inc.php');
                    break;

                case 'save_edit': // Salvataggio modifiche
                case 'save_delete': // Eliminazione
                case 'save_new': //Inserimento nuova pagina
                    include('gestione/ambientazione/save.inc.php');
                    break;

                default: //Lista pagine
                    include('gestione/ambientazione/index.inc.php');
                    break;
            }
       
        ?>
    </div><!-- page_body -->
</div><!-- Pagina -->
