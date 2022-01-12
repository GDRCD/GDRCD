<div class="pagina_scheda_px">
    <?php /*HELP: */ ?>

    <?php

    //Se non e' stato specificato il nome del pg
    if (isset($_REQUEST['pg']) === false)
    {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']) . '</div>';
    } else
    {
    /*Visualizzo la pagina*/
    /*Verifico l'esistenza del PG*/
    $query = "SELECT nome FROM personaggio WHERE personaggio.nome = '" . gdrcd_filter('in', $_REQUEST['pg']) . "'";
    $result = gdrcd_query($query, 'result');
    //Se non esiste il pg
    if (gdrcd_query($result, 'num_rows') == 0)
    {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']) . '</div>';
    }
    else
    {

    gdrcd_query($result, 'free');

    $num_logs = $PARAMETERS['settings']['view_logs'];
    ?>

    <!-- Riepilogo PX -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['px']['page_name']); ?></h2>
    </div>

    <?php /* Assegnamento PX*/
    if ($_POST['op'] == "assegna")
    {
        if ((is_numeric($_POST['px']) === true) && ($_SESSION['permessi'] >= GAMEMASTER))
        {
            gdrcd_query("UPDATE personaggio SET esperienza = esperienza + " . gdrcd_filter('in', $_POST['px']) . " WHERE nome = '" . gdrcd_filter('in', $_REQUEST['pg']) . "' LIMIT 1 ");

            /*Registro l'operazione*/
            gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) 
                                VALUES ('" . gdrcd_filter('in', $_REQUEST['pg']) . "', '" . $_SESSION['login'] . "', NOW(), " . PX . ", '(" . gdrcd_filter('in',$_POST['px']) . ' px) ' . gdrcd_filter('in', $_POST['causale']) . "')");
            echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['done']) . '</div>';
        } else
        {
            echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['camt_do']) . '</div>';
        }
    } ?>


    <div class="page_body">
        <div class="panels_box">
            <?php /*Seleziono le ultime 20 assegnamzioni px*/

            $query = "SELECT  descrizione_evento, autore, data_evento FROM log WHERE nome_interessato = '" . gdrcd_filter('in',
                    $_REQUEST['pg']) . "' AND codice_evento = " . PX . " ORDER BY data_evento DESC LIMIT " . $num_logs . "";
            $result = gdrcd_query($query, 'result');

            $query = "SELECT esperienza FROM personaggio WHERE nome = '" . gdrcd_filter('in', $_REQUEST['pg']) . "'";
            ?>
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


                    <?php while ($record = gdrcd_query($result, 'fetch'))
                    { ?>

                        <tr>
                            <!-- Oggetto, immagine, quantità -->
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out',
                                        $record['descrizione_evento']); ?></div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out',
                                        gdrcd_format_date($record['data_evento'])); ?></div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out', $record['autore']); ?></div>
                            </td>
                        </tr>
                    <?php }//while

                    gdrcd_query($result, 'free');
                    ?>
                </table>

            </div>

            <?php if ($_SESSION['permessi'] >= GAMEMASTER)
            { ?>
                <div class="form_gioco">
                    <form action="main.php?page=scheda_px"
                          method="post">
                        <div class="form_label"><?php echo gdrcd_filter('out',
                                $MESSAGE['interface']['sheet']['px']['why']); ?></div>
                        <div class="form_field"><input name="causale"/></div>
                        <div class="form_label"><?php echo gdrcd_filter('out',
                                $MESSAGE['interface']['sheet']['px']['px']); ?></div>
                        <div class="form_field"><input name="px" value="0"/></div>
                        <div class="form_submit">
                            <input type="submit" value="<?php echo gdrcd_filter('out',
                                $MESSAGE['interface']['forms']['submit']); ?>"/>
                            <input type="hidden" value="assegna" name="op"/>
                            <input type="hidden"
                                   value="<?php echo gdrcd_filter('get', $_REQUEST['pg']); ?>"
                                   name="pg"/>
                        </div>
                    </form>
                </div>
            <?php } ?>

        </div>
        <!-- Link a piè di pagina -->
        <div class="link_back">
            <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',
                $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
                    $MESSAGE['interface']['sheet']['link']['back']); ?></a>
        </div>


        <?php
        /********* CHIUSURA SCHEDA **********/
        }//else

        }//else
        ?>
    </div>
</div><!-- Pagina -->