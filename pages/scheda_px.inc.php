<div class="pagina_scheda_px">
    <?php /*HELP: */

    //Se non e' stato specificato il nome del pg
    if (isset($_GET['pg']) === false) {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']) . '</div>';
    } else {
        /*Visualizzo la pagina*/
        /*Verifico l'esistenza del PG*/
        $query = "SELECT id_personaggio FROM personaggio WHERE personaggio.id_personaggio = '" . gdrcd_filter('in', $_GET['pg']) . "'";
        $result = gdrcd_query($query, 'result');
        //Se non esiste il pg
        if (gdrcd_query($result, 'num_rows') == 0) {
            echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']) . '</div>';
        } else {

            gdrcd_query($result, 'free');

            $num_logs = $PARAMETERS['settings']['view_logs'];
    ?>

            <!-- Riepilogo PX -->
            <div class="page_title">
                <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['px']['page_name']); ?></h2>
            </div>

            <?php /* Assegnamento PX*/
            if ($_POST['op'] == "assegna") {
                if ((is_numeric($_POST['px']) === true) && ($_SESSION['permessi'] >= GAMEMASTER)) {
                    gdrcd_query("UPDATE personaggio SET esperienza = esperienza + " . gdrcd_filter('in', $_POST['px']) . " WHERE id_personaggio = '" . gdrcd_filter('in', $_GET['pg']) . "' LIMIT 1 ");
                    //Recupero il nome del personaggio
                    $nome = gdrcd_query("SELECT nome FROM personaggio WHERE id_personaggio = '" . gdrcd_filter('in', $_GET['pg']) . "'");

                    /*Registro l'operazione */
                    gdrcd_log_notice(
                        'Assegnazione punti esperienza al personaggio',

                        [
                            'evento' => 'personaggio.assegna_px',
                            'id_autore' => $_SESSION['id_personaggio'],
                            'autore' => $_SESSION['login'],
                            'id_soggetto' => $_GET['pg'],
                            'soggetto' => $nome['nome'],
                            'px' => (int) $_POST['px'],
                            'causale' => $_POST['causale']
                        ],
                        $_GET['pg']
                    );
                     /*Registro l'operazione per chi ha effettuato l'operazione*/
                    gdrcd_log_notice(
                        'Assegnazione punti esperienza al personaggio',

                        [
                            'evento' => 'personaggio.assegna_px',
                            'id_autore' => $_SESSION['id_personaggio'],
                            'autore' => $_SESSION['login'],
                            'id_soggetto' => $_GET['pg'],
                            'soggetto' => $nome['nome'],
                            'px' => (int) $_POST['px'],
                            'causale' => $_POST['causale']
                        ],
                        $_SESSION['id_personaggio']
                    );
                    echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['done']) . '</div>';
                } else {
                    echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['camt_do']) . '</div>';
                }
            } ?>


            <div class="page_body">
                <div class="panels_box">

                    <!-- Intestazione tabella elenco -->
                    <div class="elenco_record_gioco">
                        <table>
                            <tr>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['px']['event']); ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['px']['date']); ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['px']['author']); ?>
                                    </div>
                                </td>
                            </tr>

                            <?php
                            $logs = gdrcd_extract_logs('personaggio.assegna_px', $num_logs, 0, $_GET['pg']);
                            foreach ($logs as $record) {
                                $contesto = gdrcd_extract_log_contesto($record);
                            ?>
                                <tr>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php
                                            echo  '(' . (int)($contesto['px'] ?? 0) . ' px) ' . ($contesto['causale'] ?? '');;
                                            ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_format_datetime($record['data']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo $contesto['autore']; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                    <?php if ($_SESSION['permessi'] >= GAMEMASTER) { ?>
                        <div class="form_gioco">
                            <form action="main.php?page=scheda_px&pg=<?php echo gdrcd_filter('url', $_GET['pg']); ?>" method="post">
                                <div class="form_label"><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['px']['why']); ?></div>
                                <div class="form_field"><input name="causale" /></div>
                                <div class="form_label"><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['px']['px']); ?></div>
                                <div class="form_field"><input name="px" value="0" /></div>
                                <div class="form_submit">
                                    <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                                    <input type="hidden" value="assegna" name="op" />
                                    <input type="hidden" value="<?php echo gdrcd_filter('get', $_REQUEST['pg']); ?>" name="pg" />
                                </div>
                            </form>
                        </div>
                    <?php } ?>

                </div>
                <!-- Link a piè di pagina -->
                <div class="link_back">
                    <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url', $_GET['pg']); ?>"><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['link']['back']); ?></a>
                </div>


        <?php
            /********* CHIUSURA SCHEDA **********/
        } //else

    } //else
        ?>
            </div>
</div><!-- Pagina -->