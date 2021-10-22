<div class="pagina_scheda_diario">
    <?php
require_once(__DIR__ . '/../includes/required.php');
require_once(__DIR__ . '/meteo/condizioni/condizioni.class.php');

$class = new Condizioni();

$op = gdrcd_filter('out', $_REQUEST['op']);
?>
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['meteo_condition']['page_name']); ?></h2>
    </div>gestione_meteo_condizioni.inc.php
    <!-- Box principale -->
    <div class="page_body">
        <div class="panels_box">
<?php
if ($class->Visibility()) {
    # Richieste Post
            switch (gdrcd_filter_get($_POST['op'])) {
                case 'view': //Lettura pagina
                    include('meteo/condizioni/view.inc.php');
                    break;

                case 'edit': //Form modifica pagina
                    include('meteo/condizioni/edit.inc.php');
                    break;

                case 'new': // Form nuova pagina
                    include('meteo/condizioni/new.inc.php');
                    break;

                case 'save_edit': // Salvataggio modifiche
                case 'delete': // Eliminazione
                case 'save_new': //Inserimento nuova pagina
                    include('meteo/condizioni/save.inc.php');
                    break;

                default: //Lista pagine
                    include('meteo/condizioni/index.inc.php');
                    break;
            } ?>
        </div>
    </div>


<?php
}
?>
</div>
<script src="https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.jquery.min.js"></script>
<link href="https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.min.css" rel="stylesheet"/>
<script>
    $(".chosen-select").chosen({
        no_results_text: "Inserire delle opzioni"
    })
</script>