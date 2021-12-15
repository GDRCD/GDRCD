 <?php
$class =Meteo::getInstance();

$op = Filters::out($_REQUEST['op']);

?>
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo Filters::out( $MESSAGE['interface']['administration']['meteo_season']['page_name']); ?></h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <div class="panels_box">
            <?php
            if ($class->Visibility()) {
            # Richieste Post
            switch (Filters::out($_POST['op'])) {

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
                case 'add_condition': //Inserimento condizione
                    include('meteo/stagioni/save.inc.php');
                    break;
                case 'delete_condition': //Inserimento condizione
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