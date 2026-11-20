<?php
/* HELP: Pannello del meteo: per ogni zona e per ogni sua chat mostra il meteo
 * in vigore e permette ai master di impostarlo o ripristinarlo.
 */

$testi_pannello = $MESSAGE['interface']['administration']['meteo'];

/* Controllo permessi utente */
if (!gdrcd_controllo_permessi($PARAMETERS['administration']['meteo']['access_level'])) {
    echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
} else {
    ?>
    <div class="gestione_pagina">

        <!-- Titolo della pagina -->
        <div class="gestione_incipit">
            <div class="title"><?php echo gdrcd_filter('out', basename($_SERVER['SCRIPT_NAME'] ?? '') === 'popup.php' ? $testi_pannello['title_zone'] : $testi_pannello['page_name']); ?></div>
        </div>

        <!-- Corpo della pagina -->
        <div class="gestione_body">
            <?php
            /*
             * Richieste POST: imposta o ripristina il meteo di una zona o di una chat
             */
            if (in_array($_POST['op'] ?? null, ['save', 'reset'], true)) {
                include __DIR__ . '/save.inc.php';
            }

            /*
             * Elenco di zone e chat con i form
             */
            include __DIR__ . '/view.inc.php';
            ?>
        </div>

        <!-- Link a piè di pagina: nella modale (popup.php) non serve -->
        <?php if (basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'popup.php') { ?>
            <div class="link_back">
                <a href="main.php?page=gestione"><?php echo gdrcd_filter('out', $testi_pannello['link']['back']); ?></a>
            </div>
        <?php } ?>
    </div><!-- pagina -->
    <?php
}
