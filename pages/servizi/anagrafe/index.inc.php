<?php

// Avvio l'operazione di ricerca nel caso sia stato inviato il form
if (gdrcd_filter('get', $_POST['action']) == "searchPersonaggio") {

    if (!empty($_REQUEST['nome']) || !empty($_REQUEST['genere']) || !empty($_REQUEST['razza'])) {
        // Ottengo i filtri inviati dal FORM
        if (gdrcd_filter('get', $_REQUEST['nome'])) {
            $whereFilters[] = "personaggio.nome LIKE '%" . gdrcd_filter('get', $_REQUEST['nome']) . "%'";
        }

        if (gdrcd_filter('get', $_REQUEST['genere'])) {
            $whereFilters[] = "personaggio.sesso = '" . gdrcd_filter('get', $_REQUEST['genere']) . "'";
        }

        if (gdrcd_filter('get', $_REQUEST['razza'])) {
            $whereFilters[] = "personaggio.id_razza = '" . gdrcd_filter('get', $_REQUEST['razza']) . "'";
        }

        $limit_val = gdrcd_filter('num', $_REQUEST['limit']);

        $limit = (isset($_REQUEST['limit']) && ($_REQUEST['limit'] > 0)) ? " LIMIT {$_REQUEST['limit']} " : '';

        // Costruisco la query
        $querySearch = "SELECT personaggio.url_img_chat, personaggio.nome, personaggio.cognome, personaggio.sesso, 
                               razza.nome_razza 
                        FROM personaggio 
                        LEFT JOIN razza ON personaggio.id_razza = razza.id_razza 
                        WHERE 1 " . (isset($whereFilters) ? ' AND ' . implode(' AND ', $whereFilters) : NULL) . '
                        ORDER BY nome DESC '.$limit;
        $resultSearch = gdrcd_query($querySearch, 'result');

        // Se ottengo dei risultati, costruisco la tabella
        if (gdrcd_query($resultSearch, 'num_rows') > 0) {

            // Costruisco le intestazioni
            $trs[] = '<tr>
                        <td class="casella_titolo"><div class="capitolo_elenco">' . $MESSAGE['interface']['pg_list']['search']['img'] . '</div></td>
                        <td class="casella_titolo"><div class="capitolo_elenco">' . $MESSAGE['interface']['pg_list']['search']['personaggio'] . '</div></td>
                        <td class="casella_titolo"><div class="capitolo_elenco">' . $MESSAGE['interface']['pg_list']['search']['sesso'] . '</div></td>
                        <td class="casella_titolo"><div class="capitolo_elenco">' . $MESSAGE['interface']['pg_list']['search']['razza'] . '</div></td>
                        <td class="casella_titolo"></td>
                     </tr>';

            // Scorro i risultati
            while ($rowSearch = gdrcd_query($resultSearch, 'fetch')) {
                // Aggiungo le celle con i dettagli del personaggio
                $tds[] = '<td class="casella_elemento">
                            <div class="elementi_elenco">
                                <img src="' . gdrcd_filter('out', $rowSearch['url_img_chat']) . '" class="chat_avatar" alt="Avatar chat di ' . gdrcd_filter('out', $rowSearch['nome']) . '"
                                     style="width:' . $PARAMETERS['settings']['chat_avatar']['width'] . 'px; height:' . $PARAMETERS['settings']['chat_avatar']['height'] . 'px;" />
                            </div>
                          </td>';
                $tds[] = '<td class="casella_elemento">
                            <div class="elementi_elenco">
                                <a href="main.php?page=scheda&pg=' . gdrcd_filter('out', $rowSearch['nome']) . '">' .
                                    gdrcd_filter('out', $rowSearch['nome']) . ' ' . gdrcd_filter('out', $rowSearch['cognome']) . '
                                </a>
                            </div>                        
                         </td>';
                $tds[] = '<td class="casella_elemento">
                            <div class="elementi_elenco">' .
                                gdrcd_filter('out', $MESSAGE['register']['fields']['gender_' . $rowSearch['sesso']]) . '
                            </div>
                         </td>';
                $tds[] = '<td class="casella_elemento">
                            <div class="elementi_elenco">' .
                                gdrcd_filter('out', $rowSearch['nome_razza']) . '
                            </div>                     
                         </td>';
                $tds[] = '<td class="casella_elemento">
                            <div class="controllo_elenco">
                                <form action="main.php?page=messages_center&op=create" method="post">
                                    <input type="hidden" name="destinatario" value="'.$rowSearch['nome'].'" />
                                    <input type="image" src="imgs/icons/reply.png" value="submit" alt="'.gdrcd_filter('out', $MESSAGE['interface']['messages']['reply']).'"
                                           title="'.gdrcd_filter('out', $MESSAGE['interface']['messages']['reply']).'" />
                                </form>
                            </div>
                           </td>';

                // Costruisco la riga
                $trs[] = '<tr>' . implode('', $tds) . '</tr>';

                // Rimuovo le celle
                unset($tds);
            }


            // Finalizzo la costruzione della tabella
            $tableSearch =
                '<div class="elenco_esteso">
                    <div class="elenco_record_gioco">
                        <table>
                            ' . implode('', $trs) . '
                        </table>
                    </div>
                </div>';
        }
    } else {
        echo '<div class="warning">Selezionare almeno un criterio di ricerca.</div>';
    }
}


// Ottengo le razze per costruire le opzioni
$result = gdrcd_query("SELECT id_razza, nome_razza FROM razza ORDER BY nome_razza", 'result');
// Scorro i risultati e inserisco le opzioni
$optionsRazze = [];
while ($razza = gdrcd_query($result, 'fetch')) {
    $isSelected = gdrcd_filter('get', $_REQUEST['razza']) == $razza['id_razza'] ? 'selected' : NULL;
    $optionsRazze[] = '<option value="' . $razza['id_razza'] . '" ' . $isSelected . '>' . gdrcd_filter('out', $razza['nome_razza']) . '</option>';
}

// Ottengo i generi per costruire le opzioni
$genders = ['m', 'f'];
// Scorro i risultati e inserisco le opzioni
$optionsGenders = [];
foreach ($genders as $gender) {
    $isSelected = gdrcd_filter('get', $_REQUEST['genere']) == $gender ? 'selected' : NULL;
    $optionsGenders[] = '<option value="' . $gender . '" ' . $isSelected . '>' . gdrcd_filter('out', $MESSAGE['register']['fields']['gender_' . $gender]) . '</option>';
}

?>
    <!-- INIZIO FILTRI -->
    <div id="FiltriAnagrafe" class="servizi_form_container">

        <div class="servizi_form_title"><?= gdrcd_filter('out', $MESSAGE['interface']['pg_list']['search']['title']); ?></div>

        <form method="POST" id="FiltriAnagrafeForm" class="servizi_form" action="main.php?page=servizi_anagrafe">

            <!-- NOME -->
            <div class="single_input">
                <div class="label"><?= $MESSAGE['interface']['pg_list']['search']['personaggio']; ?></div>
                <input type="text" name="nome" value="<?= gdrcd_filter('out', $_REQUEST['nome']); ?>"/>
            </div>

            <!-- GENERE -->
            <div class="single_input">
                <div class="label"><?= $MESSAGE['interface']['pg_list']['search']['sesso']; ?></div>
                <select name="genere">
                    <option value=""></option>
                    <?php echo implode('', $optionsGenders); ?>
                </select>
            </div>

            <!-- RAZZA -->
            <div class="single_input">
                <div class="label"><?= $MESSAGE['interface']['pg_list']['search']['razza']; ?></div>
                <select name="razza">
                    <option value=""></option>
                    <?php echo implode('', $optionsRazze); ?>
                </select>
            </div>

            <!-- LIMITE PG -->
            <div class="single_input">
                <div class="label"><?= $MESSAGE['interface']['pg_list']['search']['limit']; ?></div>
                <input type="number" name="limit"
                       value="<?= isset($_REQUEST['limit']) ? gdrcd_filter('out', $_REQUEST['limit']) : 0; ?>"/>
            </div>

            <!-- SUBMIT + EXTRA -->
            <div class="single_input split-50">
                <input type="hidden" name="action" value="searchPersonaggio" required>
                <input type="submit"
                       value="<?= gdrcd_filter('out', $MESSAGE['interface']['pg_list']['search']['submit']); ?>">
            </div>

        </form>
    </div>
    <!-- FINE FILTRI -->

    <!-- RISULTATO -->
<?php if (isset($tableSearch)) {
    echo $tableSearch;
}