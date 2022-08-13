<div class="pagina_scheda_roles">
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2>
            Registrazione role
        </h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <?php
        /*
         * Richieste POST
         */
        switch(Filters::get($_POST['op'])) {
            case 'search': //Ricerca role fra quelle registrate
                include('scheda/registrazioni/search.inc.php');
                break;
            case 'send_edit':
            case 'edit': //Modifica dati registrazione
                include('scheda/registrazioni/edit.inc.php');
                break;
            case 'register': //Inserimento nuova registrazione in scheda
                include('scheda/registrazioni/register.inc.php');
                break;
            case 'send_segn': //Inserimento nuova registrazione in scheda
                include('scheda/registrazioni/send_reg.inc.php');
                break;
            case 'log': //Apertura log
                include('scheda/registrazioni/log.inc.php');
                break;
            case 'segnala_send':
            case 'segnala': //Segnalazione ai Master
                include('scheda/registrazioni/segnala.inc.php');
                break;
            default:
                include('scheda/registrazioni/index.inc.php');
                break;
        }

        ?>
    </div>
    <!-- Box principale -->
    <!-- Link a piè di pagina -->
    <div class="link_back">
        <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',
            $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
                $MESSAGE['interface']['sheet']['link']['back']); ?></a>
    </div>
</div><!-- Pagina -->