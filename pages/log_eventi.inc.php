<div class="pagina_gestione_razze">
    <?php if ($_SESSION['permessi'] < SUPERUSER): ?>
        <div class="error"><?php echo gdrcd_filter('out', $MESSAGE['error']['not_allowed']); ?></div>
    <?php else: ?>

        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['page_name']); ?></h2>
        </div>

        <div class="page_body">

            <?php
            // --- Lettura filtri ---
            $whichLog = isset($_REQUEST['which_log']) && is_numeric($_REQUEST['which_log'])
                ? (int)$_REQUEST['which_log']
                : null;   // null = tutti i log

            $offset  = isset($_REQUEST['offset']) ? max(0, (int)$_REQUEST['offset']) : 0;
            $limit   = (int)$PARAMETERS['settings']['records_per_page'];
            $pagebegin = $offset * $limit;

            // Risolve gli eventi dal codice (null se nessun filtro)
            $eventi = $whichLog !== null
                ? gdrcd_log_group_from_code($whichLog)
                : null;

            // Se è stato scelto un tipo ma non corrisponde a nessun gruppo → errore
            $gruppoNonTrovato = ($whichLog !== null && empty($eventi));

            // --- Lettura personaggi ---
            $id_personaggio = isset($_REQUEST['id_personaggio']) && is_numeric($_REQUEST['id_personaggio'])
                ? (int)$_REQUEST['id_personaggio']
                : null;   // null = tutti i personaggi
                
            $personaggi=gdrcd_stmt_all("SELECT id_personaggio, nome FROM personaggio ORDER BY nome");
            ?>

            <!-- ===== FILTRI ===== -->
            <div class="panels_box">
                <div class="form_gestione">
                    <form action="main.php?page=log_eventi" method="get" style="display: inline-flex;">
                        <input type="hidden" name="page" value="log_eventi" />
                        <div class="form_field">
                            <select name="which_log">
                                <option value=""><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['log_type']); ?></option>
                                <?php foreach ($MESSAGE['event'] as $eventKey => $eventLabel): ?>
                                    <option value="<?php echo (int)$eventKey; ?>"
                                        <?php echo ($whichLog === (int)$eventKey) ? 'selected' : ''; ?>>
                                        <?php echo gdrcd_filter('out', $eventLabel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form_field">
                            <select name="id_personaggio">
                                <option value=""><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['personaggio']); ?></option>
                                <?php foreach ($personaggi as $personaggio){ ?>
                                    <option value="<?php echo $personaggio['id_personaggio']; ?>"
                                        <?php echo ($id_personaggio === $personaggio['id_personaggio']) ? 'selected' : ''; ?>>
                                        <?php echo gdrcd_filter('out', $personaggio['nome']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form_submit">
                            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                        </div>
                    </form>
                </div>
            </div>

            <!-- ===== TABELLA ===== -->
            <?php if ($gruppoNonTrovato): ?>
                <div class="error"><?php echo gdrcd_filter('out', $MESSAGE['error']['unknown_operation']); ?></div>
            <?php else:
                $totaleresults = gdrcd_count_logs($eventi, $id_personaggio);
                $logs          = gdrcd_extract_logs($eventi, $id_personaggio, $limit, $pagebegin);
                $numresults    = count($logs);
            ?>

                <?php if ($numresults > 0): ?>
                    <div class="elenco_record_gestione">
                        <table>
                            <tr>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['date']); ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['author']); ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['sogg'] ); ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['dest'] ); ?>
                                    </div>
                                </td>
                                
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['descr']); ?>
                                    </div>
                                </td>
                            </tr>

                            <?php foreach ($logs as $row):
                                $presentazione = gdrcd_present_log_row($whichLog, $row);
                              //  gdrcd_debug($row); 
                            ?>
                                <tr class="risultati_elenco_record_gestione">
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_format_date($row['data']) . ' ' . gdrcd_format_time($row['data']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php
                                            if ($presentazione['id_autore'] !== null) {
                                                echo '<a href="main.php?page=scheda&pg=' . $presentazione['id_autore'] . '">' . gdrcd_filter('out', $presentazione['autore']) . '</a>';
                                                
                                            } else {
                                                echo $presentazione['autore'];
                                            }
                                            ?>
 
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php
                                            if ($presentazione['id_sogggetto'] !== null) {
                                                echo '<a href="main.php?page=scheda&pg=' . $presentazione['id_sogggetto'] . '">' . gdrcd_filter('out', $presentazione['soggetto']) . '</a>';
                                                
                                            } else {
                                                echo $presentazione['soggetto'];
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php
                                            if ($presentazione['id_destinatario'] !== null) {
                                                echo '<a href="main.php?page=scheda&pg=' . $presentazione['id_destinatario'] . '">' . gdrcd_filter('out', $presentazione['destinatario']) . '</a>';
                                                
                                            } else {
                                                echo $presentazione['destinatario'];
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out', $presentazione['descrizione']); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>

                <?php else: ?>
                    <div class="warning">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['no_results'] ?? 'Nessun log trovato.'); ?>
                    </div>
                <?php endif; ?>

                <!-- ===== PAGER ===== -->
                <?php if ($totaleresults > $limit): ?>
                    <div class="pager">
                        <?php
                        echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                        $totalPages = (int)ceil($totaleresults / $limit);

                        for ($i = 0; $i < $totalPages; $i++):
                            $queryString = http_build_query([
                                'page'      => 'log_eventi',
                                'which_log' => $whichLog ?? '',
                                'id_personaggio' => $id_personaggio ?? '',
                                'limit'     => $limit,
                                'offset'    => $i,
                            ]);
                        ?>
                            <?php if ($i !== $offset): ?>
                                <a href="main.php?<?php echo $queryString; ?>"><?php echo $i + 1; ?></a>
                            <?php else: ?>
                                <?php echo ' ' . ($i + 1) . ' '; ?>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    <?php endif; ?>
</div>
<div class="link_back">
    <a href="main.php?page=gestione">Torna indietro</a>
</div>