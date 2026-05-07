<div class="pagina_gestione_abilita">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        return; 
    } elseif($PARAMETERS['mode']['skillsystem'] == 'OFF') {
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['unactive']).'</div>';
        return;
    }
?>

<div class="pagina_gestione_abilita">
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['page_name']); ?></h2>
    </div>
    <div class="page_body">
        <?php
        switch($_REQUEST['op']) {
            case 'save_new':
            case 'save_edit':
            case 'save_delete':
                include('gestione/abilita/save.inc.php');
                break;
            case 'new':
                include('gestione/abilita/new.inc.php');
                break;
            case 'edit':
                include('gestione/abilita/edit.inc.php');
                break;
            default: //Lista pagine
                include('gestione/abilita/index.inc.php');
                break;
        }
        ?>
    </div><!-- page_body -->
</div><!-- Pagina -->

