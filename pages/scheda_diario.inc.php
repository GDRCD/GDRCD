<div class="pagina_scheda_diario">
    <?php
    //Se non e' stato specificato il nome del pg
    if (isset($_REQUEST['pg']) === false) {
        echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
        exit();
    } ?>

    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['diary']); ?></h2>
    </div>

    <!-- Box principale -->
    <div class="page_body">
        <div class="panels_box">
            <?php

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
                    include('scheda/diario/index.inc.php');
                    break;
            } ?>

        </div>
    </div>
</div>
