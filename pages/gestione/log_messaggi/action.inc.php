<?php
if (($_SESSION['permessi'] < MODERATOR) || ($PARAMETERS['mode']['spymessages'] != 'ON')) {
    echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
    return;
}
switch ($_REQUEST['op']) {
    case 'view':
        //Determinazione pagina (paginazione)
                $pagebegin = (int) $_REQUEST['offset'] * $PARAMETERS['settings']['records_per_page'];
                $pageend = $PARAMETERS['settings']['records_per_page'];
                //Conteggio record totali
                $record_globale = gdrcd_stmt_one("SELECT COUNT(*) FROM backmessaggi WHERE id_personaggio_mittente = ?",
                    [$REQUEST['pg']]);
                $totaleresults = $record_globale['COUNT(*)'];
                //Lettura record
                $result = gdrcd_stmt_all(
                    "SELECT IFNULL(personaggio.nome, ?) AS destinatario,
                            backmessaggi.spedito,
                            backmessaggi.testo
                    FROM backmessaggi
                    LEFT JOIN personaggio ON backmessaggi.id_personaggio_destinatario = personaggio.id_personaggio
                    WHERE backmessaggi.id_personaggio_mittente = ? ORDER BY backmessaggi.spedito DESC LIMIT ?, ?",
                    [$GLOBALS['MESSAGE']['interface']['user']['cancelled'], $_REQUEST['pg'], $pagebegin, $pageend]);
        break;
        default:
           echo '<div class="error">Operazione non riconosciuta.</div>';
            return;
        break;
}
if(!empty($result)) { ?>
    <div class="elenco_record_gestione">
            <table>
                <!-- Intestazione tabella -->
                <tr>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['messages']['dest']); ?>
                        </div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['messages']['date']); ?>
                        </div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['messages']['text']); ?>
                        </div>
                    </td>
                </tr>
                <!-- Record -->
                <?php 
                foreach($result as $row) { ?>
                    <tr class="risultati_elenco_record_gestione">
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', $row['destinatario']); ?>
                            </div>
                        </td>
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_format_date($row['spedito']).' '.gdrcd_format_time($row['spedito']); ?>
                            </div>
                        </td>
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', $row['testo']); ?>
                            </div>
                        </td>
                    </tr>
                <?php
                 } //foreach
                ?>
            </table>
        </div>
        <div class="pager">
            <?php 
            if($totaleresults > $PARAMETERS['settings']['records_per_page']) {
                echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                for($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['records_per_page']); $i++) {
                    if($i != $_REQUEST['offset']) {
                        ?>
                        <a href="main.php?page=log_messaggi&op=view&pg=<?php echo $_REQUEST['pg']; ?>&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                    <?php 
                    } else {
                        echo ' '.($i + 1).' ';
                    }
                } //for
            }//if
            ?>
        </div>
                          
<?php } //if(!empty($result)) ?>

<div class="link_back">
    <a href="main.php?page=log_messaggi">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['messages']['link']['back']); ?>
    </a>
</div>