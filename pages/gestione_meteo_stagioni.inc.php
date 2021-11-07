 <?php
$class =Stagioni::getInstance();

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
 <script src="https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.jquery.min.js"></script>
 <link href="https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.min.css" rel="stylesheet"/>
 <script>
     $(".chosen-select").chosen({
         no_results_text: "Inserire delle opzioni"
     })
 </script>