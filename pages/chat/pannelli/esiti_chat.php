<div class="titolo_box">
    Esiti master
</div>
<?php if (isset($_SESSION['login'])) { ?>
    <div class="esiti_container">
        <br>
        <div class="form_info">
            In questa sezione sono elencati tutti gli esiti disponibili per il pg all'interno della presente chat.
            Gli esiti riportati sono gli esiti che ancora devono essere "scoperti", tramite l'apposito tiro in chat.
            Una volta ottenuto l'esito, questi spariscono.
        </div>

        <?php $luogo = gdrcd_filter('num', $_SESSION['luogo']);
        $query = gdrcd_query("SELECT * FROM esiti WHERE chat = '{$luogo}' 
            AND pg = '" . gdrcd_filter('in', $_SESSION['login']) . "' 
            AND sent = 0 ORDER BY data DESC", 'result');

        ?>
        <div class="elenco_record_gioco">
            <table>
                <tr class="titles_table">
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            Data
                        </div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            Titolo
                        </div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            <?php echo 'Tiro'; ?>
                        </div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">

                        </div>
                    </td>
                </tr>
                <?php $num = gdrcd_query($query, 'num_rows');
                if ($num == 0) {
                    echo '<tr>
                            <td></td>
                            <td>Nessun esito attualmente disponibile in questa chat per te</td>
                         </tr>';
                } else {
                    while ($rec = gdrcd_query($query, 'fetch')) {
                        $ab = gdrcd_query("SELECT nome FROM abilita WHERE id_abilita = " . $rec['id_ab'] . " ");
                        ?>
                        <tr>
                            <td class="casella_titolo">
                                <div class="elementi_elencoroles">
                                    <?php echo gdrcd_filter('out', gdrcd_format_date($rec['data'])); ?>
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="elementi_elencoroles">
                                    <?php echo gdrcd_filter('out', $rec['titolo']); ?>
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="elementi_elencoroles">
                                    <?php echo $ab['nome']; ?>
                                </div>
                            </td>
                            <td style="width: 100px;">
                                <form method="POST" action="pages/chat/chat_ajax.php" class="chat_form_ajax">
                                    <input type="hidden"
                                           name="action"
                                           value="send_esito"/>
                                    <input type="hidden"
                                           name="id"
                                           value="<?php echo $rec['id']; ?>"/>
                                    <input type="submit" name="submit" class="submitroles" value="Tira"/>
                                </form>
                            </td>
                        </tr>
                    <?php } #Fine blocco
                } ?>
            </table>
        </div>
    </div>

<?php } else {

    die('<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>');

}

?>

<script src="/pages/chat/JS/chat_dadi.js"></script>