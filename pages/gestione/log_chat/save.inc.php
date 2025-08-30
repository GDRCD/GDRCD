<?php
if (($_SESSION['permessi'] < MODERATOR) || ($PARAMETERS['mode']['spymessages'] != 'ON')){
    echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
} else {
    switch ($_REQUEST['op']) {
        # Cancella account
        case 'view_user':

            //Determinazione pagina (paginazione)
            $pagebegin = (int) $_REQUEST['offset'] * $PARAMETERS['settings']['records_per_page'];
            $pageend = $PARAMETERS['settings']['records_per_page'];
            //Conteggio record totali
            $record_globale = gdrcd_query("SELECT COUNT(*) FROM chat WHERE mittente = '" . gdrcd_filter('get',
                    $_REQUEST['pg']) . "'");
            $totaleresults = $record_globale['COUNT(*)'];
             //Lettura record
            $result = gdrcd_query("SELECT chat.destinatario, chat.tipo, chat.ora, chat.testo, mappa.nome FROM chat JOIN mappa on chat.stanza=mappa.id WHERE chat.mittente = '" . $_REQUEST['pg'] . "' ORDER BY ora DESC LIMIT " . $pagebegin . ", " . $pageend . "",
                'result');
            $numresults = gdrcd_query($result, 'num_rows');

            /* Se esistono record */
            if ($numresults > 0) {
                $sender=gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['chat']['sender']);
                $date=gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['chat']['date']);
                $text=gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['chat']['text']);
                echo <<<HTML
<!-- Elenco dei record paginato -->
<div class="elenco_record_gestione">
    <table>
        <!-- Intestazione tabella -->
        <tr>
            <td class="casella_titolo">
                <div class="titoli_elenco">
                    {$sender}
                </div>
            </td>
            <td class="casella_titolo">
                <div class="titoli_elenco">
                    {$date}
                </div>
            </td>
            <td class="casella_titolo">
                <div class="titoli_elenco">
                    {$text}
                </div>
            </td>
        </tr>
        <!-- Record -->
HTML;
                while ($row = gdrcd_query($result, 'fetch')) {
                    $nome=gdrcd_filter('out', $row['nome']);
                    $ora= gdrcd_format_datetime($row['ora']);
                    if (empty($row['destinatario']) === false) {
                        $destinatario= '(-> ' . gdrcd_filter('out', $row['destinatario']) . ') ';
                    }
                    $testo= gdrcd_filter('out', $row['testo']);
                    echo <<<HTML
        <tr class="risultati_elenco_record_gestione">
            <td class="casella_elemento">
                <div class="elementi_elenco">
                    {$nome}
                </div>
            </td>
            <td class="casella_elemento">
                <div class="elementi_elenco">
                    {$ora}
                </div>
            </td>
            <td class="casella_elemento">
                <div class="elementi_elenco">
                    {$destinatario}
                    {$testo}
                </div>
            </td>
        </tr>
HTML;
                } //while
                echo <<<HTML
    </table>
</div>
HTML;
            }//if

            echo <<<HTML
<!-- Paginatore elenco -->
<div class="pager">
HTML;
            if ($totaleresults > $PARAMETERS['settings']['records_per_page']) {
                echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                for ($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['records_per_page']); $i++) {
                    if ($i != $_REQUEST['offset']) {
                        $page_num = $i + 1;
                        echo <<<HTML
                        <a href="main.php?page=log_chat&op=view_user&pg={$_REQUEST['pg']}&offset={$i}">{$page_num}</a>
HTML;
                    } else {
                        echo ' ' . ($i + 1) . ' ';
                    }
                } //for
            }//if
            echo <<<HTML
</div>
HTML;
            break;

        case 'view_date':
            // Input della richiesta
            $luogo = $_REQUEST['luogo'];
            $data_a = gdrcd_format_datetime_standard($_REQUEST['data_a']);
            $data_b = gdrcd_format_datetime_standard($_REQUEST['data_b']);

            //Determinazione pagina (paginazione)
            $pagebegin = gdrcd_filter('in', (int) $_REQUEST['offset'] * $PARAMETERS['settings']['records_per_page']);
            $pageend = gdrcd_filter('in', $PARAMETERS['settings']['records_per_page']);

            // Parametri di ricerca per le query al database
            $parametri_query = [
                'iss',
                $luogo,
                $data_a,
                $data_b,
            ];

            //Conteggio record totali
            $stmt = gdrcd_stmt(
                'SELECT COUNT(*) AS numero_azioni FROM chat WHERE stanza = ? AND ora >= ? AND ora <= ?',
                $parametri_query
            );

            $record_globale = gdrcd_query($stmt, 'fetch');
            gdrcd_query($stmt, 'free');

            $totaleresults = $record_globale['numero_azioni'];

            // RQuery per il recupero della pagina di azioni richiesta
            $query = <<<SQL
                SELECT
                    chat.mittente,
                    chat.destinatario,
                    chat.tipo,
                    chat.ora,
                    chat.testo

                FROM chat

                WHERE
                    chat.stanza = ?
                    AND ora >= ?
                    AND ora <= ?

                ORDER BY ora DESC

                LIMIT {$pagebegin}, {$pageend}
                SQL;

            $result = gdrcd_stmt($query, $parametri_query);
            $numresults = gdrcd_query($result, 'num_rows');

            /* Se esistono record */
            if ($numresults > 0) {

                 $sender=gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['chat']['sender']);
                $date=gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['chat']['date']);
                $text=gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['chat']['text']);
                echo <<<HTML
<!-- Elenco dei record paginato -->
<div class="elenco_record_gestione">
    <table>
        <!-- Intestazione tabella -->
        <tr>
            <td class="casella_titolo">
                <div class="titoli_elenco">
                    {$sender}
                </div>
            </td>
            <td class="casella_titolo">
                <div class="titoli_elenco">
                    {$date}
                </div>
            </td>
            <td class="casella_titolo">
                <div class="titoli_elenco">
                    {$text}
                </div>
            </td>
        </tr>

        <!-- Record -->
HTML;
                while ($row = gdrcd_query($result, 'fetch')) {
                    $mittente=gdrcd_filter('out', $row['mittente']);
                    $ora= gdrcd_format_datetime($row['ora']);
                    if (empty($row['destinatario']) === false) {
                        $destinatario= '(-> ' . gdrcd_filter('out', $row['destinatario']) . ') ';
                    }
                    $testo= gdrcd_filter('out', $row['testo']);
                    echo <<<HTML
        <tr class="risultati_elenco_record_gestione">
            <td class="casella_elemento">
                <div class="elementi_elenco">
                    {$mittente}
                </div>
            </td>
            <td class="casella_elemento">
                <div class="elementi_elenco">
                    {$ora}               
                </div>
            </td>
            <td class="casella_elemento">
                <div class="elementi_elenco">
                    {$destinatario}
                    {$testo}
                </div>
            </td>
        </tr>
HTML;
                } //while
                gdrcd_query($result, 'free');
                echo <<<HTML
                    </table>
                </div>
                HTML;
            }//if
            
            echo <<<HTML
            <!-- Paginatore elenco -->
            <div class="pager">
            HTML;
            if ($totaleresults > $PARAMETERS['settings']['records_per_page']) {
                echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                for ($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['records_per_page']); $i++) {
                    if ($i != $_REQUEST['offset']) {
                        $luogo_filtered = gdrcd_filter('get', $_REQUEST['luogo']);
                        $page_num = $i + 1;
                        echo <<<HTML
<a href="main.php?page=log_chat&op=view_date&luogo={$luogo_filtered}&data_a={$data_a}&data_b={$data_b}&offset={$i}">{$page_num}</a>
HTML;
                    } else {
                        echo ' ' . ($i + 1) . ' ';
                    }
                } //for
            }//if
            echo <<<HTML
            </div>
            HTML;
            break;
        default:
            die('Operazione non riconosciuta.');
    }
}
echo <<<HTML
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=log_chat">Torna indietro</a>
</div>
HTML;
?>
