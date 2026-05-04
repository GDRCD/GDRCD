<?php
if (($_SESSION['permessi'] < MODERATOR) || ($PARAMETERS['mode']['spymessages'] != 'ON')) {
    echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
    return;
}

$offset = gdrcd_filter('int', $_REQUEST['offset'] ?? 0);
$pagebegin = $offset * $PARAMETERS['settings']['records_per_page'];
$pageend = $PARAMETERS['settings']['records_per_page'];

$result = [];
$totaleresults = 0;
$link = '';

switch ($_REQUEST['op']) {
    case 'view_user':
        $pg = gdrcd_filter('int', $_REQUEST['pg'] ?? 0);

        $record_globale = gdrcd_stmt_one(
            "SELECT COUNT(*) AS numero_azioni FROM chat WHERE id_personaggio_mittente = ?",
            [$pg]
        );

        $totaleresults = $record_globale['numero_azioni'];

        $result = gdrcd_stmt_all(
            "SELECT
                mittente.nome AS nome_mittente,
                destinatario.nome AS nome_destinatario,
                chat.tipo,
                chat.ora,
                chat.testo
            FROM chat
            LEFT JOIN mappa ON chat.stanza = mappa.id
            LEFT JOIN personaggio AS mittente ON chat.id_personaggio_mittente = mittente.id_personaggio
            LEFT JOIN personaggio AS destinatario ON chat.id_personaggio_destinatario = destinatario.id_personaggio
            WHERE chat.id_personaggio_mittente = ?
            ORDER BY ora DESC
            LIMIT " . $pagebegin . ", " . $pageend,
            [$pg]
        );

        $link = 'main.php?page=log_chat&op=view_user&pg=' . $pg . '&offset=';
        break;

    case 'view_date':
        $luogo = gdrcd_filter('int', $_REQUEST['luogo'] ?? 0);
        $data_a = gdrcd_format_datetime_standard($_REQUEST['data_a'] ?? '');
        $data_b = gdrcd_format_datetime_standard($_REQUEST['data_b'] ?? '');

        $parametri_query = [
            $luogo,
            $data_a,
            $data_b,
        ];

        $record_globale = gdrcd_stmt_one(
            'SELECT COUNT(*) AS numero_azioni FROM chat WHERE stanza = ? AND ora >= ? AND ora <= ?',
            $parametri_query
        );

        $totaleresults = $record_globale['numero_azioni'];

        $query = "
            SELECT
                mittente.nome AS nome_mittente,
                destinatario.nome AS nome_destinatario,
                chat.tipo,
                chat.ora,
                chat.testo
            FROM chat
            LEFT JOIN personaggio AS mittente ON chat.id_personaggio_mittente = mittente.id_personaggio
            LEFT JOIN personaggio AS destinatario ON chat.id_personaggio_destinatario = destinatario.id_personaggio
            WHERE
                chat.stanza = ?
                AND ora >= ?
                AND ora <= ?
            ORDER BY ora DESC
            LIMIT {$pagebegin}, {$pageend}";

        $result = gdrcd_stmt_all($query, $parametri_query);

        $link = 'main.php?page=log_chat&op=view_date'
            . '&luogo=' . $luogo
            . '&data_a=' . gdrcd_filter('get', $data_a)
            . '&data_b=' . gdrcd_filter('get', $data_b)
            . '&offset=';
        break;

    default:
        echo '<div class="error">Operazione non riconosciuta.</div>';
        return;
    }
    if (!empty($result)) {

                $sender=gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['chat']['sender']);
                $date=gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['chat']['date']);
                $text=gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['chat']['text']);
               ?>
                    <!-- Elenco dei record paginato -->
                    <div class="elenco_record_gestione">
                        <table>
                            <!-- Intestazione tabella -->
                            <tr>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo $sender; ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo $date; ?>    
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo $text; ?>
                                    </div>
                                </td>
                            </tr>

                            <!-- Record -->
            <?php
                foreach ($result as $row) {
                    $destinatario = '';
                    $mittente=gdrcd_filter('out', $row['nome_mittente']);
                    $ora= gdrcd_format_datetime($row['ora']);
                    if (empty($row['nome_destinatario']) === false) {
                        $destinatario= '(-> ' . gdrcd_filter('out', $row['nome_destinatario']) . ') ';
                    }
                    $testo= gdrcd_filter('out', $row['testo']);
                    ?>
                        <tr class="risultati_elenco_record_gestione">
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo $mittente; ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo $ora; ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo $destinatario; ?>
                                    <?php echo $testo; ?>
                                </div>
                            </td>
                        </tr>
            <?php
                } //foreach
            ?>
                    </table>
                </div>
            <?php  
            }//if
            ?>
            <!-- Paginatore elenco -->
           <!-- Paginatore elenco -->
            <div class="pager">
            <?php
            if ($totaleresults > $PARAMETERS['settings']['records_per_page']) {
                echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);

                $total_pages = ceil($totaleresults / $PARAMETERS['settings']['records_per_page']);

                for ($i = 0; $i < $total_pages; $i++) {
                    if ($i != $offset) {
                        $page_num = $i + 1;
                        echo '<a href="' . gdrcd_filter('out', $link . $i) . '">' . $page_num . '</a> ';
                    } else {
                        echo ' ' . ($i + 1) . ' ';
                    }
                }
            }
            ?>
            </div>
            
            <!-- Link a piè di pagina -->
            <div class="link_back">
                <a href="main.php?page=log_chat">Torna indietro</a>
            </div>
 
<?php

?>
