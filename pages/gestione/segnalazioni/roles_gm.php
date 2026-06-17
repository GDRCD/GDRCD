<div class="pagina_gestione_roleGM">
    <?php
    /*HELP: */
    /*Controllo permessi utente*/
    if ($_SESSION['permessi'] < ROLE_PERM) {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
    } else {
    ?>
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2>Giocate segnalate</h2>
    </div>
    <!-- Corpo della pagina -->
    <div class="page_body">
        <?php
        //Determinazione pagina (paginazione)
        $pagebegin = gdrcd_filter('int', $_REQUEST['offset']) * $PARAMETERS['settings']['posts_per_page'];
        $pageend = $pagebegin + $PARAMETERS['settings']['posts_per_page'];

        //Conteggio record totali
        $record_globale = gdrcd_stmt_one("SELECT COUNT(*) FROM send_GM ");
        $totaleresults = $record_globale['COUNT(*)'];
         $query = "SELECT send_GM.* , 
                    personaggio.nome
                    FROM send_GM
                    LEFT JOIN personaggio 
                    ON personaggio.id_personaggio = send_GM.id_personaggio
                    ORDER BY data DESC LIMIT " . $pagebegin . ", " . $PARAMETERS['settings']['posts_per_page'] . "";
        $result = gdrcd_stmt_all($query);

        if (empty($result)) {
            echo '<div class="fate_frame">';
            echo 'Nessuna segnalazione presente';
            echo '</div>';
            return; 
        }  ?>

        <!-- Paginatore elenco -->
        <div class="pager">
            <?php
            if ($totaleresults > $PARAMETERS['settings']['posts_per_page']) {
                echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                for ($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['posts_per_page']); $i++) {
                    if ($i != $_REQUEST['offset']) {
                        ?>
                        <a href="popup.php?page=esiti&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                        <?php
                    } else {
                        echo ' ' . ($i + 1) . ' ';
                    }
                } //for
            }//if
            ?>
        </div>
        <!-- Elenco dei record paginato -->
        <div class="elenco_record_gestione">
            <table>
                <!-- Intestazione tabella -->
                <tr>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">Data</div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">Autore</div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">Note</div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">Partecipanti</div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">Chat</div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">Tag quest</div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?>
                        </div>
                    </td>
                </tr>
                <!-- Record -->
                <?php 
                foreach ($result as $row) {
                    $roles = "SELECT sr.*, GROUP_CONCAT(p.nome SEPARATOR ', ') AS partecipanti_nomi
                                FROM segnalazione_role sr
                                JOIN personaggio p ON FIND_IN_SET(p.id_personaggio, sr.partecipanti)
                                WHERE sr.id = ? 
                                GROUP BY sr.id";
                    $roles_f = gdrcd_stmt_one($roles, [$row['role_reg']]);

                    ?>
                    <tr class="risultati_elenco_record_gestione">
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_format_date($row['data']); ?>
                            </div>
                        </td>
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', $row['nome']); ?>
                            </div>
                        </td>
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', $row['note']); ?>
                            </div>
                        </td>
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', $roles_f['partecipanti_nomi']); ?>
                            </div>
                        </td>
                        <?php
                        //
                        $quer = "SELECT * FROM mappa WHERE id = ? ";
                        $rec = gdrcd_stmt_one($quer, [$roles_f['stanza']]);
                        ?>
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', $rec['nome']); ?>
                            </div>
                        </td>

                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?php echo $roles_f['quest']; ?>
                            </div>
                        </td>

                        <td class="casella_controlli"><!-- Iconcine dei controlli -->
                            <!-- Vai a -->
                            <div class="controllo_elenco">
                                 <form action="main.php?page=gestione_segnalazioni&segn=log&pg=<?php echo gdrcd_filter('in', $row['id_personaggio']); ?>"  
                                      method="post">
                                    <input type="hidden" name="op" value="log"/>
                                    <input type="hidden" name="id" value="<?php echo gdrcd_filter('in', $row['role_reg']); ?>"/>
                                    <button type="submit" class="but_roles" name="submit">Apri log chat</button>
                                </form>
                            </div>

                        </td>
                    </tr>
                <?php } //foreach
                ?>
            </table>


           
        </div>
<?php
}