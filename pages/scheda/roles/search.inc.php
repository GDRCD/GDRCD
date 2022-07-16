<?php
$pg = $_REQUEST['pg'];

#Numero di risultati
if ($_POST['type'] == 0) {
    $total = gdrcd_query("SELECT * FROM segnalazione_role WHERE  mittente = '" . gdrcd_filter('in', $pg) . "' 
        AND partecipanti LIKE '%" . gdrcd_filter('in', $_POST['search']) . "%' AND conclusa = 1 ", "result");
    $type = 'personaggio';
} else if ($_POST['type'] == 1) {
    $total = gdrcd_query("SELECT * FROM segnalazione_role WHERE  mittente = '" . gdrcd_filter('in', $pg) . "' 
        AND tags LIKE '%" . gdrcd_filter('in', $_POST['search']) . "%' AND conclusa = 1 ", "result");
    $type = 'tag';
} else if ($_POST['type'] == 2) {
    $total = gdrcd_query("SELECT * FROM segnalazione_role WHERE  mittente = '" . gdrcd_filter('in', $pg) . "' 
        AND quest LIKE '%" . gdrcd_filter('in', $_POST['search']) . "%' AND conclusa = 1 ", "result");
    $type = 'quest';
}

$totale = gdrcd_query($total, 'num_rows');
?>
<div class="search_bg">
    Ricerca giocate - Ricerca per <b><?=$type;?> [ <?=gdrcd_filter('out', $_POST['search']);?> ]</b><br>
    <?='<b>' . $totale . '</b> giocate effettuate secondo questi criteri di ricerca'; ?>
</div>

<?php

$year = gdrcd_query("SELECT YEAR(data_inizio) as year FROM segnalazione_role 
                     WHERE mittente = '" . gdrcd_filter('in', $pg) . "' GROUP BY YEAR(data_inizio) ORDER BY YEAR(data_inizio) DESC", "result");

while ($ry = gdrcd_query($year, 'fetch')) {
    echo '<div class="page_title" ><h2 style="font-family: Aileron;">' . $ry['year'] . '</h2></div>';
    $month = gdrcd_query("SELECT MONTH(data_inizio) as month FROM segnalazione_role 
    WHERE mittente = '" . gdrcd_filter('in', $pg) . "' GROUP BY MONTH(data_inizio) ORDER BY  MONTH(data_inizio)  DESC", "result");
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
        } else if ($rm['month'] == 12) {
            $mese = 'Novembre';
        } else if ($rm['month'] == 12) {
            $mese = 'Dicembre';
        }

        #Inserisco le condizioni di ricerca

        # [0] Ricerca per giocatore
        if ($_POST['type'] == 0) {
            $query = gdrcd_query("SELECT * FROM segnalazione_role 
                WHERE YEAR(data_inizio) = '" . gdrcd_filter('num', $ry['year']) . "' 
                AND MONTH(data_inizio) = '" . gdrcd_filter('num', $rm['month']) . "' 
                AND mittente = '" . gdrcd_filter('in', $pg) . "' 
                AND partecipanti LIKE '%" . gdrcd_filter('in', $_POST['search']) . "%' 
                AND conclusa < 2 ORDER BY data_inizio, data_fine ", "result");
            $numbers = gdrcd_query("SELECT * FROM segnalazione_role 
                WHERE YEAR(data_inizio) = '" . gdrcd_filter('num', $ry['year']) . "' 
                AND MONTH(data_inizio) = '" . gdrcd_filter('num', $rm['month']) . "' 
                AND mittente = '" . gdrcd_filter('in', $pg) . "' 
                AND partecipanti LIKE '%" . gdrcd_filter('in', $_POST['search']) . "%' 
                AND conclusa = 1 ORDER BY data_inizio , data_fine ", "result");
        } # [1] Ricerca per abilita
        else if ($_POST['type'] == 1) {
            $query = gdrcd_query("SELECT * FROM segnalazione_role 
                WHERE YEAR(data_inizio) = '" . gdrcd_filter('num', $ry['year']) . "' 
                AND MONTH(data_inizio) = '" . gdrcd_filter('num', $rm['month']) . "' 
                AND mittente = '" . gdrcd_filter('in', $pg) . "' 
                AND tags LIKE '%" . gdrcd_filter('in', $_POST['search']) . "%' 
                AND conclusa < 2 ORDER BY data_inizio , data_fine  ", "result");
            $numbers = gdrcd_query("SELECT * FROM segnalazione_role 
                WHERE YEAR(data_inizio) = '" . gdrcd_filter('num', $ry['year']) . "' 
                AND MONTH(data_inizio) = '" . gdrcd_filter('num', $rm['month']) . "' 
                AND mittente = '" . gdrcd_filter('in', $pg) . "' 
                AND tags LIKE '%" . gdrcd_filter('in', $_POST['search']) . "%' 
                AND conclusa = 1 ORDER BY data_inizio , data_fine ", "result");
        } # [2] Ricerca per quest
        else if ($_POST['type'] == 2) {
            $query = gdrcd_query("SELECT * FROM segnalazione_role 
                WHERE YEAR(data_inizio) = '" . gdrcd_filter('num', $ry['year']) . "' 
                AND MONTH(data_inizio) = '" . gdrcd_filter('num', $rm['month']) . "' 
                AND mittente = '" . gdrcd_filter('in', $pg) . "' 
                AND quest LIKE '%" . gdrcd_filter('in', $_POST['search']) . "%' 
                AND conclusa < 2 ORDER BY data_inizio , data_fine ", "result");
            $numbers = gdrcd_query("SELECT * FROM segnalazione_role 
                WHERE YEAR(data_inizio) = '" . gdrcd_filter('num', $ry['year']) . "' 
                AND MONTH(data_inizio) = '" . gdrcd_filter('num', $rm['month']) . "' 
                AND mittente = '" . gdrcd_filter('in', $pg) . "' 
                AND quest LIKE '%" . gdrcd_filter('in', $_POST['search']) . "%' 
                AND conclusa = 1 ORDER BY data_inizio ASC, data_fine ASC ", "result");
        }
        ####

        $num = gdrcd_query($numbers, 'num_rows');
        if ($num > 0) {
            echo '<div class="titolo_box">' . $mese . '</div>'; ?>
            <div class="elenco_record_gioco" id="<?=$rm['month'] . '' . $ry['year'] ?>">
                <table>
                    <tr class="titles_table">
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo 'Data'; ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo 'Inizio'; ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo 'Fine'; ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo 'Partecipanti'; ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo 'Azioni tot'; ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo 'Chat'; ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo 'Tag'; ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo 'Note quest'; ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo 'Stato'; ?>
                            </div>
                        </td>
                        <?php if (($pg == $_SESSION['login'] && $row['conclusa'] == 1) || ($_SESSION['permessi'] >= MODERATOR)) { ?>
                            <td class="casella_titolo" style="width: 100px;">
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

                        $azioni = gdrcd_query("SELECT chat.id FROM chat INNER JOIN mappa ON mappa.id = chat.stanza 
                            LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
                            WHERE stanza = " . gdrcd_filter('num', $row['stanza']) . " 
                            AND ora >= '" . gdrcd_filter('in', $row['data_inizio']) . "' 
                            AND ora <= '" . gdrcd_filter('in', $row['data_fine']) . "' 
                            AND (tipo = 'A' || tipo = 'P') ORDER BY ora DESC", 'result');
                        $num_az = gdrcd_query($azioni, 'num_rows');

                        $parts = explode(',', $row['partecipanti']); //array
                        $listapart = join(', ', $parts);

                        ?>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', gdrcd_format_date($row['data_inizio'])); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_format_time($row['data_inizio']); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php if ($row['data_fine'] !== NULL) {
                                    echo gdrcd_format_time($row['data_fine']);
                                } ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php echo $listapart; ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php echo $num_az; ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php echo $r_chat['nome']; ?>
                            </div>
                        </td>
                        <td class="casella_titolo" style="max-width: 100px;text-align:justify;">
                            <div class="elementi_elenco">
                                <?php echo $row['tags']; ?>
                            </div>
                        </td>
                        <td class="casella_titolo" style="max-width: 100px;text-align:justify;">
                            <div class="elementi_elenco">
                                <?php echo $row['quest']; ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php if ($row['conclusa'] == 1) {
                                    echo 'Conclusa';
                                } else {
                                    echo 'In corso';
                                } ?>
                            </div>
                        </td>
                        <?php if (($pg == $_SESSION['login'] && $row['conclusa'] == 1) || ($_SESSION['permessi'] >= MODERATOR)) { ?>
                            <td class="casella_titolo" style="width: 100px;">
                                <div class="elementi_elenco">
                                    <form action="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('in', $_REQUEST['pg']); ?>"
                                          method="post">
                                        <input type="hidden"
                                               name="op"
                                               value="log"/>
                                        <input type="hidden"
                                               name="id"
                                               value="<?php echo $row['id']; ?>"/>
                                        <input type="submit" style="width: auto;"
                                               name="submit"
                                               value="Log chat"/>
                                    </form>
                                </div>
                            </td>
                        <?php } ?>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        <?php } #num rows
    }
} ?>

<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('url',
        $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
            $MESSAGE['interface']['sheet']['link']['back_roles']); ?>
    </a>
</div>
