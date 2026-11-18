<?php
/*
 * Pannello meteo: elenco delle zone con le relative chat, il meteo in vigore
 * e un form in linea per impostarlo o ripristinarlo.
 * Con il meteo automatico attivo l'elenco è in sola lettura.
 */

/*Controllo permessi utente*/
if (!gdrcd_controllo_permessi($PARAMETERS['administration']['meteo']['access_level'])) {
    echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
    return;
}

$testiMeteo = $MESSAGE['interface']['meteo'];
$testiPannello = $MESSAGE['interface']['administration']['meteo'];

$pannello = gdrcd_meteo_pannello();
$modificabile = !$pannello['auto_mode'];

// Chat appena modificata: se è una stanza privata ne tengo aperto il gruppo
$idLuogoInviato = isset($_POST['id_luogo']) && is_scalar($_POST['id_luogo']) ? (int)$_POST['id_luogo'] : 0;

/**
 * Stampa una riga dell'elenco (zona o chat) con il form in linea.
 *
 * @param string $classe Classe della riga
 * @param string $ancora Id della riga, usato per i link diretti
 * @param string $nome Nome già filtrato per l'output HTML
 * @param array $meteo Meteo in vigore (testo e scadenza_testo sono già filtrati)
 * @param string $fonte Etichetta dell'origine del valore
 * @param bool $conScadenza Se false la scadenza non si riferisce a questa riga e non la mostro
 * @param string $bersaglio 'zona' oppure 'luogo'
 * @param int $id Id della zona o della chat
 * @param int $idClick Id della zona, per tornare alla zona dopo l'invio
 */
$stampaRiga = function (string $classe, string $ancora, string $nome, array $meteo, string $fonte, bool $conScadenza, string $bersaglio, int $id, int $idClick) use ($modificabile, $testiMeteo) {
    $scadenza = '';

    if ($conScadenza) {
        $scadenza = $meteo['scadenza_testo'] ?? gdrcd_filter('out', $testiMeteo['no_expiry']);
    }
    ?>
    <tr class="<?php echo $classe; ?>" id="<?php echo $ancora; ?>">
        <td class="casella_elemento meteo_nome">
            <div class="elementi_elenco"><?php echo $nome; ?></div>
        </td>
        <td class="casella_elemento">
            <div class="elementi_elenco"><?php echo $meteo['testo']; ?></div>
        </td>
        <td class="casella_elemento">
            <div class="elementi_elenco"><?php echo gdrcd_filter('out', $fonte); ?></div>
        </td>
        <td class="casella_elemento">
            <div class="elementi_elenco"><?php echo $scadenza; ?></div>
        </td>
        <?php if ($modificabile) { ?>
            <td class="casella_elemento meteo_azioni">
                <form method="post" action="main.php?page=gestione/meteo#click-<?php echo $idClick; ?>" class="meteo_pannello_form">
                    <input type="hidden" name="bersaglio" value="<?php echo $bersaglio; ?>" />
                    <input type="hidden" name="<?php echo $bersaglio === 'zona' ? 'id_click' : 'id_luogo'; ?>" value="<?php echo $id; ?>" />

                    <!-- Temperatura, condizione e durata -->
                    <?php echo gdrcd_meteo_form_campi($meteo, [
                        'prefisso' => ($bersaglio === 'zona' ? 'click_' : 'luogo_') . $id,
                        'bersaglio' => false,
                    ]); ?>

                    <div class="input_container meteo_pulsanti">
                        <button type="submit" name="op" value="save"><?php echo gdrcd_filter('out', $testiMeteo['set']); ?></button>
                        <button type="submit" name="op" value="reset" formnovalidate><?php echo gdrcd_filter('out', $testiMeteo['reset']); ?></button>
                    </div>
                </form>
            </td>
        <?php } ?>
    </tr>
    <?php
};

/**
 * Stampa le colonne della tabella: le larghezze sono fisse,
 * così le tabelle di ogni zona restano allineate tra loro.
 */
$stampaColonne = function () use ($modificabile) {
    ?>
    <colgroup>
        <col class="meteo_col_nome" />
        <col class="meteo_col_meteo" />
        <col class="meteo_col_fonte" />
        <col class="meteo_col_scadenza" />
        <?php if ($modificabile) { ?>
            <col class="meteo_col_azioni" />
        <?php } ?>
    </colgroup>
    <?php
};

/**
 * Stampa le righe delle chat di un gruppo (pubbliche o private).
 *
 * @param array $luoghi Chat da stampare
 * @param int $idClick Id della zona a cui appartengono
 */
$stampaLuoghi = function (array $luoghi, int $idClick) use ($stampaRiga, $testiMeteo, $testiPannello, $modificabile) {
    foreach ($luoghi as $luogo) {
        $idLuogo = (int)$luogo['id'];
        $nome = gdrcd_filter('out', $luogo['nome']);

        if ($luogo['privata']) {
            $nome .= ' <span class="meteo_privata">' . gdrcd_filter('out', $testiPannello['private']) . '</span>';
        }

        // La chat ha un valore proprio, altrimenti eredita quello della zona
        $manuale = $luogo['meteo']['fonte'] === 'luogo';

        if ($manuale) {
            $fonte = $testiMeteo['manual'];
        } elseif (!$modificabile) {
            $fonte = $testiMeteo['auto'];
        } else {
            $fonte = $testiMeteo['inherit_zone'];
        }

        $stampaRiga('meteo_luogo', 'luogo-' . $idLuogo, $nome, $luogo['meteo'], $fonte, $manuale, 'luogo', $idLuogo, $idClick);
    }
};

$colonne = $modificabile ? 5 : 4;

?>
<div id="GestioneMeteoView" class="elenco_record_gestione meteo_pannello<?php echo $modificabile ? '' : ' meteo_sola_lettura'; ?>">
    <?php
    // Meteo automatico: nessun form, solo il valore in vigore
    if (!$modificabile) {
        echo '<div class="warning">' . gdrcd_filter('out', $testiPannello['auto_mode_notice']) . '</div>';
    }

    if (empty($pannello['zone'])) {
        echo '<div class="warning_low">' . gdrcd_filter('out', $testiPannello['no_maps']) . '</div>';
    } else {
        ?>
        <!-- Intestazione elenco -->
        <table class="meteo_tabella meteo_intestazione">
            <?php $stampaColonne(); ?>
            <tr>
                <td class="casella_titolo">
                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $testiPannello['map_col'] . ' / ' . $testiPannello['chat_col']); ?></div>
                </td>
                <td class="casella_titolo">
                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $testiPannello['current_col']); ?></div>
                </td>
                <td class="casella_titolo">
                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $testiPannello['source_col']); ?></div>
                </td>
                <td class="casella_titolo">
                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $testiPannello['expires_col']); ?></div>
                </td>
                <?php if ($modificabile) { ?>
                    <td class="casella_titolo">
                        <div class="titoli_elenco"><?php echo gdrcd_filter('out', $testiPannello['actions_col']); ?></div>
                    </td>
                <?php } ?>
            </tr>
        </table>

        <?php
        foreach ($pannello['zone'] as $zona) {
            $idClick = (int)$zona['id_click'];

            // Separo le chat pubbliche dalle stanze private, che restano raccolte
            $pubbliche = [];
            $private = [];

            foreach ($zona['luoghi'] as $luogo) {
                if ($luogo['privata']) {
                    $private[] = $luogo;
                } else {
                    $pubbliche[] = $luogo;
                }
            }

            $zonaManuale = $zona['meteo']['fonte'] === 'zona';
            ?>
            <div class="meteo_zona_blocco">
                <table class="meteo_tabella">
                    <?php
                    $stampaColonne();

                    $stampaRiga(
                        'meteo_zona',
                        'click-' . $idClick,
                        gdrcd_filter('out', $zona['nome']),
                        $zona['meteo'],
                        $zonaManuale ? $testiMeteo['manual'] : $testiMeteo['auto'],
                        $zonaManuale,
                        'zona',
                        $idClick,
                        $idClick
                    );

                    $stampaLuoghi($pubbliche, $idClick);

                    if (empty($zona['luoghi'])) {
                        ?>
                        <tr class="meteo_vuoto">
                            <td class="casella_elemento" colspan="<?php echo $colonne; ?>">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out', $testiPannello['no_chats']); ?></div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>

                <?php
                // Stanze private: raccolte di default, aperte se ne è appena stata modificata una
                if (!empty($private)) {
                    $aperto = $idLuogoInviato > 0 && in_array($idLuogoInviato, array_map('intval', array_column($private, 'id')), true);
                    ?>
                    <details class="meteo_privati"<?php echo $aperto ? ' open' : ''; ?>>
                        <summary><?php echo gdrcd_filter('out', sprintf($testiPannello['private_toggle'], count($private))); ?></summary>
                        <table class="meteo_tabella">
                            <?php
                            $stampaColonne();
                            $stampaLuoghi($private, $idClick);
                            ?>
                        </table>
                    </details>
                    <?php
                }
                ?>
            </div>
            <?php
        }
    }
    ?>
</div>
