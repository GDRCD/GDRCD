<div class="pagina_scheda_diario">
    <?php
    require_once(__DIR__ . '/../includes/required.php');
    require_once(__DIR__ . '/meteo/stagioni/stagioni.class.php');

    $class = new Stagioni();

    $op = gdrcd_filter('out', $_REQUEST['op']);
    ?>
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['meteo_season']['page_name']); ?></h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <div class="panels_box">
            <?php
            if ($class->Visibility()) {
            # Richieste Post
            switch (gdrcd_filter_get($_POST['op'])) {
                case 'view': //Lettura pagina
                    include('meteo/stagioni/view.inc.php');
                    break;

                case 'edit': //Form modifica pagina
                    include('meteo/stagioni/edit.inc.php');
                    break;

                case 'new': // Form nuova pagina
                    include('meteo/stagioni/new.inc.php');
                    break;

                case 'save_edit': // Salvataggio modifiche
                case 'delete': // Eliminazione
                case 'save_new': //Inserimento nuova pagina
                    include('meteo/stagioni/save.inc.php');
                    break;

                default: //Lista pagine
                    include('meteo/stagioni/index.inc.php');
                    break;
            } ?>
        </div>
    </div>
<?php
}
?>
</div>