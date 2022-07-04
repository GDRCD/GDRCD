<?php
$pg = $_REQUEST['pg'];

#possibilità di inserire le role del mese attuale e di quello prima
$mydate = date('Y-m-d H:i:s');
$mesenow = date('m', strtotime($mydate));
$yearnow = date('Y', strtotime($mydate));


if (($_REQUEST['pg'] == $_SESSION['login']) || ($_SESSION['permessi'] >= ROLE_PERM)) { ?>


    <form action="main.php?page=scheda_roles&pg=<?=gdrcd_filter('in', $_REQUEST['pg']);?>" method="post">
        <input type="hidden"
               name="op"
               value="register"/>
        <input type="hidden"
               name="mese"
               value="<?php echo gdrcd_filter('num', $mesenow); ?>"/>
        <input type="hidden"
               name="anno"
               value="<?php echo gdrcd_filter('num', $yearnow); ?>"/>
        <input type="hidden"
               name="pg"
               value="<?php echo gdrcd_filter('in', $_REQUEST['pg']); ?>"/>
        <input type="submit"
               name="submit"
               value="Registra giocata"/>
    </form>

<?php }

################################################

#PANNELLO RICERCA ROLES

################################################
if (($_REQUEST['pg'] == $_SESSION['login']) || ($_SESSION['permessi'] >= ROLE_PERM)) { ?>

    <div class="search_bg">
        <form action="main.php?page=scheda_roles&pg=<?=gdrcd_filter('in', $_REQUEST['pg']);?>" method="post">
                <select name="type" class="searchsel">
                    <option value="0">Per personaggio</option>
                    <option value="1">Per tag</option>
                    <option value="2">Per quest</option>
                </select>
                <input name="search" class="searchbar" placeholder="Inserisci la chiave di ricerca appropriata."
                       type="text" value=""/>

                <input type="hidden"
                       name="op"
                       value="search"/>
                <input type="hidden"
                       name="pg"
                       value="<?php echo gdrcd_filter('in', $_REQUEST['pg']); ?>"/>
                <button type="submit" class="but_roles">Cerca</button>
        </form>
    </div>
<?php }


################################################

#ELENCO ROLES

################################################


$year = gdrcd_query("SELECT YEAR(data_inizio) as year FROM segnalazione_role 
    WHERE mittente = '" . gdrcd_filter('in', $pg) . "' GROUP BY YEAR(data_inizio) 
    ORDER BY YEAR(data_inizio)  DESC", "result");

#PULSANTI SELEZIONE ANNO

echo '<div style="display:flex; justify-content:center;margin-top:5px;overflow:auto;">';
while ($ry = gdrcd_query($year, 'fetch')) {
    echo '<a class="buts_year" 
    href="main.php?page=scheda_roles&pg=' . gdrcd_filter('url', $_REQUEST['pg']) . '&y=' . $ry['year'] . '" >' . $ry['year'] . '
    </a>';
}
echo '</div>';

if (isset($_GET['y'])) {
    $yearchosen = gdrcd_filter('in', $_GET['y']);
} else {
    $yearchosen = $yearnow;
}

#Container mesi (default= ultimo anno)
echo '<div class="container_months">';
echo '<div class="page_title" ><h2>' . $yearchosen . '</h2></div>';
$month = gdrcd_query("SELECT MONTH(data_inizio) as month FROM segnalazione_role 
    WHERE mittente = '" . gdrcd_filter('in', $pg) . "' AND YEAR(data_inizio) = '" . gdrcd_filter('num', $yearchosen) . "'
    GROUP BY MONTH(data_inizio) ORDER BY MONTH(data_inizio) DESC", "result");
while ($rm = gdrcd_query($month, 'fetch')) {
    if ($rm['month'] == 1) {
        $mese = 'Gennaio';
    } else if ($rm['month'] == 2) {
        $mese = 'Febbraio';
    } else if ($rm['month'] == 3) {
        $mese = 'Marzo';
    } else if ($rm['month'] == 4) {
        $mese = 'Aprile';
    } else if ($rm['month'] == 5) {
        $mese = 'Maggio';
    } else if ($rm['month'] == 6) {
        $mese = 'Giugno';
    } else if ($rm['month'] == 7) {
        $mese = 'Luglio';
    } else if ($rm['month'] == 8) {
        $mese = 'Agosto';
    } else if ($rm['month'] == 9) {
        $mese = 'Settembre';
    } else if ($rm['month'] == 10) {
        $mese = 'Ottobre';
    } else if ($rm['month'] == 11) {
        $mese = 'Novembre';
    } else if ($rm['month'] == 12) {
        $mese = 'Dicembre';
    }


    $query = gdrcd_query("SELECT * FROM segnalazione_role 
        WHERE YEAR(data_inizio) = '" . gdrcd_filter('num', $yearchosen) . "' 
        AND MONTH(data_inizio) = '" . gdrcd_filter('num', $rm['month']) . "' AND mittente = '" . gdrcd_filter('in', $pg) . "' 
        AND conclusa < 2 ORDER BY data_inizio ASC, data_fine ASC ", "result");

    $numbers = gdrcd_query("SELECT * FROM segnalazione_role WHERE YEAR(data_inizio) = '" . gdrcd_filter('num', $yearchosen) . "' 
        AND MONTH(data_inizio) = '" . gdrcd_filter('num', $rm['month']) . "' AND mittente = '" . gdrcd_filter('in', $pg) . "' 
        AND conclusa = 1 ORDER BY data_inizio ASC, data_fine ASC ", "result");
    $num = gdrcd_query($numbers, 'num_rows');
    $totals = gdrcd_query($query, 'num_rows');
    if ($totals > 0) { ?>

        <div class="titolo_box"><?php echo gdrcd_filter('out', $mese); ?></div>
        <div id="<?php echo $rm['month'] . '' . $yearchosen; ?>">
            <div class="list_roles">

                <div class="elenco_record_gioco">
                    <table>
                        <tr class="titles_table">
                            <td></td>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    Data
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    Partecipanti
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    Azioni
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    Chat
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    Tag
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    Note quest
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    Stato
                                </div>
                            </td>
                            <?php if (($pg == $_SESSION['login'] && $row['conclusa'] == 1) || ($_SESSION['permessi'] >= ROLE_PERM)) { ?>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">

                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <?php
                            //
                            while ($row = gdrcd_query($query, 'fetch')){
                            $chat = gdrcd_query("SELECT nome FROM mappa 
                                WHERE id = " . gdrcd_filter('num', $row['stanza']) . " ", "result");
                            $r_chat = gdrcd_query($chat, 'fetch');

                            if ($row['conclusa'] == 0) {
                                $num_az = 0;
                            } else {
                                $azioni = gdrcd_query("SELECT chat.id FROM chat INNER JOIN mappa 
                                    ON mappa.id = chat.stanza LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
									WHERE stanza = " . gdrcd_filter('num', $row['stanza']) . " 
									AND ora >= '" . gdrcd_filter('in', $row['data_inizio']) . "' 
									AND ora <= '" . gdrcd_filter('in', $row['data_fine']) . "' 
									AND (tipo = 'A' || tipo = 'P') ORDER BY ora DESC", 'result');
                                $num_az = gdrcd_query($azioni, 'num_rows');
                            }

                            $parts = explode(',', $row['partecipanti']); //array
                            $listapart = join(', ', $parts);

                            $new_time = date("Y-m-d H:i:s", strtotime("+30 days", strtotime($row['data_fine'])));
                            $mydate = date('Y-m-d H:i:s');
                            ?>
                            <td>
                                <?php if ((($new_time > $mydate) && $pg == $_SESSION['login'] && $row['conclusa'] == 1)
                                    || ($_SESSION['permessi'] >= EDIT_PERM && $row['conclusa'] == 1)) { ?>
                                    <form action="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('in', $_REQUEST['pg']); ?>"
                                          method="post">
                                        <input type="hidden"
                                               name="op"
                                               value="edit"/>
                                        <input type="hidden"
                                               name="id"
                                               value="<?php echo $row['id']; ?>"/>
                                        <button type="submit" class="but_roles">Modifica registrazione</button>
                                    </form>
                                <?php }
                                ?>
                            </td>
                            <td class="casella_titolo">
                                <div class="elementi_elencoroles">
                                    <?php echo gdrcd_filter('out', gdrcd_format_date($row['data_inizio']));
                                    echo '<br>' . gdrcd_format_time($row['data_inizio']) . ' - ' . gdrcd_format_time($row['data_fine']);
                                    ?>
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="elementi_elencoroles">
                                    <?php echo $listapart; ?>
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="elementi_elencoroles">
                                    <?php echo $num_az; ?>
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="elementi_elencoroles">
                                    <?php echo $r_chat['nome']; ?>
                                </div>
                            </td>
                            <td class="casella_titolo" style="max-width: 100px;text-align:justify;">
                                <div class="elementi_elencorolesquest">
                                    <?php echo $row['tags']; ?>
                                </div>
                            </td>
                            <td class="casella_titolo" style="max-width: 100px;text-align:justify;">
                                <div class="elementi_elencorolesquest">
                                    <?php echo $row['quest']; ?>
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="elementi_elencoroles">
                                    <?php if ($row['conclusa'] == 1) {
                                        echo 'Conclusa';
                                    } else {
                                        echo 'In corso';
                                    } ?>
                                </div>
                            </td>
                            <?php if (($pg == $_SESSION['login'] && $row['conclusa'] == 1) || ($_SESSION['permessi'] >= ROLE_PERM && $row['conclusa'] == 1)) { ?>
                                <td>
                                    <?php  if ($_SESSION['permessi'] >= LOG_PERM) { ?>
                                        <form action="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('in', $_REQUEST['pg']); ?>"
                                              method="post">
                                            <input type="hidden"
                                                   name="op"
                                                   value="log"/>
                                            <input type="hidden"
                                                   name="id"
                                                   value="<?php echo $row['id']; ?>"/>
                                            <button type="submit" class="but_roles">Log chat</button>

                                        </form>
                                    <?php  }
                                    if (SEND_GM) { ?>
                                        <form action="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('in', $_REQUEST['pg']); ?>"
                                              method="post">
                                            <input type="hidden"
                                                   name="op"
                                                   value="segnala"/>
                                            <input type="hidden"
                                                   name="id"
                                                   value="<?php echo $row['id']; ?>"/>
                                            <button type="submit" class="but_roles">Segnala ai Master</button>

                                        </form>
                                    <?php  }
                                        if ($pg == $_SESSION['login'] && $row['conclusa'] == 1 && SAVE_ROLE) { ?>
                                            <a href="pages/scheda/roles/save.proc.php?id=<?php echo $row['id']; ?>" target="_blank">
                                                Scarica giocata
                                            </a>
                                    <?php  } ?>
                                </td>
                            <?php } ?>
                        </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    <?php } #num rows
} ?>
    </div>
