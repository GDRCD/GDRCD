<div class="pagina_forum">
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2>
            <?=gdrcd_filter('out', $PARAMETERS['names']['forum']['plur']);?>
        </h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <?php
        /*
         * Richieste POST
         */
        switch(gdrcd_filter_get($_POST['op'])) {
            case 'delete': //Cancellazione messaggio o topic
                include('delete.inc.php');
                break;
            case 'edit': //Modifica messaggio o topic
                include('edit.inc.php');
                break;
            case 'insert': //Inserimento messaggio o topic
                include('insert.inc.php');
                break;
            case 'readall': //Funzione segna tutto come letto
                include('readall.inc.php');
                break;
            default:
                break;
        }
        /*
         * Richieste GET
         */
        switch(gdrcd_filter_get($_GET['op'])) {
            case 'composer': //Creazione nuovi messaggi e topic
                include('composer.inc.php');
                break;
            case 'delete_conf':
                include('delete_conf.inc.php');
                break;
            case 'modifica': //Form modifica
                include('modifica.inc.php');
                break;
            case 'read': //Visualizzazione topic
                include('read.inc.php');
                break;
            case 'visit': //Visualizzazione dei topic
                include('visit.inc.php');
                break;
            case false: //Visualizzazione di base (Elenco forum)
            default:
                include('list.inc.php');
                break;
        }
        ?>
    </div>
    <!-- Box principale -->

</div><!-- Pagina -->
