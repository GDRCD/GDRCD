<div class="pagina_info_location" id="info_location_box">
    <?php /* HELP: Il box delle informazioni carica l'immagine del luogo corrente, lo stato e la descrizione. Mostra, inoltre, il meteo */

    $record = gdrcd_stmt_one(
        'SELECT nome, descrizione, stato, immagine, stanza_apparente, scadenza FROM mappa WHERE id = ? LIMIT 1',
        [(int)$_SESSION['luogo']]
    ) ?: [];

    /** * Fix: quando non si è in una mappa visualizza il nome della chat
     * Quando si è in una mappa si visualizza il nome della mappa
     * @author Blancks
     */
    if (empty($record['nome'])) {
        $nome_mappa = gdrcd_stmt_one(
            'SELECT nome FROM mappa_click WHERE id_click = ? LIMIT 1',
            [(int)$_SESSION['mappa']]
        );
        $nome_luogo = $nome_mappa['nome'] ?? '';
    } else {
        $nome_luogo = $record['nome'];
    }
    ?>
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $nome_luogo); ?></h2>
    </div>
    <div class="page_body">
        <?php
        if (!empty($record) || $_SESSION['luogo'] == -1) {
            if (empty($record['nome']) === false) {
                $nome_luogo = $record['nome'];
            } elseif ($_SESSION['mappa'] >= 0) {
                $nome_luogo = $PARAMETERS['names']['maps_location'];
            } else {
                $nome_luogo = $PARAMETERS['names']['base_location'];
            }
            ?>
            <!--Immagine/descrizione -->
            <div class="info_image">
                <?php
                if (empty($record['immagine']) === false) {
                    $immagine_luogo = $record['immagine'];
                } else {
                    $immagine_luogo = 'standard_luogo.png';
                }
                ?>
                <img src="themes/<?php echo gdrcd_filter('out', $PARAMETERS['themes']['current_theme']); ?>/imgs/locations/<?php echo gdrcd_filter('out', $immagine_luogo); ?>"
                     class="immagine_luogo" alt="<?php echo gdrcd_filter('out', $record['descrizione'] ?? ''); ?>"
                     title="<?php echo gdrcd_filter('out', $record['descrizione'] ?? ''); ?>">
            </div>
            <?php if ((isset($record['stato']) === true) || (isset($record['descrizione']) === true)) {
                echo '<div class="box_stato_luogo"><marquee onmouseover="this.stop()" onmouseout="this.start()" direction="left" scrollamount="3" class="stato_luogo">&nbsp;' . $MESSAGE['interface']['maps']['Status'] . ': ' . gdrcd_filter('out', $record['stato'] ?? '') . ' -  ' . gdrcd_filter('out', $record['descrizione'] ?? '') . '</marquee></div>';
            } else {
                echo '<div class="box_stato_luogo">&nbsp;</div>';
            }

            /* Meteo: risolto dal nucleo (automatico, zona o chat), testo già filtrato per l'output */
            $now = gdrcd_meteo_now();
            $meteo = gdrcd_meteo_corrente((int)$_SESSION['luogo'], (int)$_SESSION['mappa']);

            # Ai GM mostro fino a quando resta attivo un meteo manuale
            $meteo_scadenza = '';

            if ((int)($_SESSION['permessi'] ?? 0) >= GAMEMASTER && $meteo['fonte'] !== 'auto') {
                $orario_scadenza = gdrcd_meteo_orario_scadenza($meteo['scadenza'], $now);

                if ($orario_scadenza !== null) {
                    $meteo_scadenza = gdrcd_filter('out', $MESSAGE['interface']['meteo']['expires_at']) . ' ' . $orario_scadenza;
                }
            }

            # Fase lunare, se abilitata (la costante MOON resta solo come ripiego)
            $fasi_lunari = (gdrcd_configuration_get('meteo.fasi_lunari') ?? ((defined('MOON') && MOON) ? 'si' : 'no')) === 'si';
            ?>
            <div class="page_title">
                <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['meteo']['title']); ?></h2>
            </div>
            <div class="meteo_date">
                <?php echo $now->format('d') . '/' . $now->format('m') . '/' . ((int)$now->format('Y') + (int)$PARAMETERS['date']['offset']); ?>
            </div>
            <div class="meteo_luna">
                <?php if ($fasi_lunari) {
                    $fase = gdrcd_meteo_fase_lunare($now);
                    $fase_immagine = gdrcd_meteo_fasi_lunari()[$fase];
                    $fase_titolo = gdrcd_filter('out', $MESSAGE['interface']['meteo']['moon'][$fase] ?? $fase_immagine);
                    echo '<img title="' . $fase_titolo . '" alt="' . $fase_titolo . '" src="themes/' . gdrcd_filter('out', $PARAMETERS['themes']['current_theme']) . '/imgs/luna/' . gdrcd_filter('out', $fase_immagine) . '.png">';
                } ?>
            </div>

            <div class="meteo">
                <span id="meteo_testo"><?php echo $meteo['testo']; ?></span>
                <?php if ($_SESSION['permessi'] >= METEO_PERM && $PARAMETERS['mode']['auto_meteo'] == 'OFF') { ?>
                    <a class="meteo_gestione" href="main.php?page=gestione/meteo#click-<?php echo (int)$_SESSION['mappa']; ?>" aria-label="<?php echo gdrcd_filter('out', $MESSAGE['interface']['meteo']['gm_link']); ?>">
                        <svg class="meteo_gestione_icona" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.49.49 0 0 0-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54a.48.48 0 0 0-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96a.49.49 0 0 0-.59.22L2.74 8.87a.47.47 0 0 0 .12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58a.49.49 0 0 0-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32a.47.47 0 0 0-.12-.61l-2.01-1.58zM12 15.6A3.6 3.6 0 1 1 12 8.4a3.6 3.6 0 0 1 0 7.2z"/></svg>
                        <span class="meteo_gestione_tooltip"><?php echo gdrcd_filter('out', $MESSAGE['interface']['meteo']['gm_link']); ?></span>
                    </a>
                <?php } ?>
                <?php if ($meteo_scadenza !== '') { ?>
                    <span class="meteo_scadenza"><?php echo $meteo_scadenza; ?></span>
                <?php } ?>
            </div>
            <?php
        } else {
            echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['location_doesnt_exist']) . '</div>';
        } ?>
    </div>
    <!-- page_body -->
</div><!-- Pagina -->
