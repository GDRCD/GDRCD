<?php
require_once(__DIR__ . '/../includes/required.php');
require_once(__DIR__ . '/meteo/condizioni/condizioni.class.php');

$class = new Condizioni();

$op = gdrcd_filter('out', $_REQUEST['op']);
?>
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['meteo_condition']['page_name']); ?></h2>
    </div>
<?php
if ($class->Visibility()) {



            # Richieste Post
            switch (gdrcd_filter_get($_POST['op'])) {
                case 'view': //Lettura pagina
                    include('scheda/diario/view.inc.php');
                    break;

                case 'edit': //Form modifica pagina
                    include('scheda/diario/edit.inc.php');
                    break;

                case 'new': // Form nuova pagina
                    include('scheda/diario/new.inc.php');
                    break;

                case 'save_edit': // Salvataggio modifiche
                case 'delete': // Eliminazione
                case 'save_new': //Inserimento nuova pagina
                    include('scheda/diario/save.inc.php');
                    break;

                default: //Lista pagine
                    include('meteo/condizioni/index.inc.php');
                    break;
            } ?>



<?php
}
?>