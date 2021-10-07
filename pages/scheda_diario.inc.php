<div class="pagina_scheda_diario">
    <?php /*HELP: */
    //Se non e' stato specificato il nome del pg
    if(isset($_REQUEST['pg']) === false) {
        echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
        exit();
    }

    ?>
    <style>
        .btn-link {
            border: none;
            outline: none;
            background: none;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
            font-family: inherit;
            font-size: inherit;
        }

    </style>
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['diary']); ?></h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <div class="panels_box">
        <?php
        /*
         * Richieste POST
         */
        switch(gdrcd_filter_get($_POST['op'])) {
            case 'view': //Inserimento nuova pagina
                include ('scheda/diario/view.inc.php');
                break;
            case 'edit': //Modifica modifica della pagina diario
                include ('scheda/diario/edit.inc.php');
                break;
            case 'new': //Inserimento nuova pagina
                include ('scheda/diario/new.inc.php');
                break;
            case 'save_edit':
            case 'save_new': //Inserimento nuova pagina
                include ('scheda/diario/save.inc.php');
                break;
            default:
                include ('scheda/diario/index.inc.php');
                break;
        }

        ?>
    </div>
    <!-- Box principale -->

</div><!-- Pagina -->
</div>