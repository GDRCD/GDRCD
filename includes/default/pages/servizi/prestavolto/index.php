<?php

Router::loadRequired();

$prestavolto = PersonaggioPrestavolto::getInstance();

if ( $prestavolto->prestavoltiEnabled() ) { ?>
    <div class="groups_page">
        <!-- Titolo della pagina -->
        <div class="general_title">
            Elenco prestavolti
        </div>

        <div class="page_body">
            <?php Router::loadPages('servizi/prestavolto/' . $prestavolto->loadServicePage(Filters::out($_GET['op']))); ?>
        </div>
    </div>
<?php } ?>