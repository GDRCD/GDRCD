<?php

require_once(__DIR__ . '/../../../includes/required.php');

$quest = Quest::getInstance();


if (isset($_POST['op']) === FALSE) {
//Definisco la lista di quest accessibile all'account in base ai permessi

//Determinazione pagina (paginazione)
    $pagebegin = (int)$_REQUEST['offset'] * $PARAMETERS['settings']['records_per_page'];
    $pageend = $PARAMETERS['settings']['records_per_page'];

    $quests = $quest->getAllQuests($pagebegin, $pageend);

    $numresults = DB::rowsNumber($quests);
    ?>

    <!-- link crea nuovo -->
    <div class="link_back">
        <a href="main.php?page=gestione_quest&op=new_quest">
            Registra nuova quest
        </a>
    </div>
    <?php if ($quest->trameEnabled() && $quest->viewTramePermission()) { ?>
        <div class="link_back">
            <a href="main.php?page=gestione_quest&op=lista_trame">
                Lista delle trame
            </a>
        </div>
        <?php
    }
    /* Se esistono record */
    if ($numresults > 0) { ?>
        <!-- Elenco dei record paginato -->
        <div class="elenco_record_gestione">
            <table>
                <!-- Intestazione tabella -->
                <tr>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">Data</div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">Titolo</div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">Autore</div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">Partecipanti</div>
                    </td>
                    <?php if ($quest->viewTramePermission()) {
                        echo '<td class="casella_titolo"><div class="titoli_elenco">Trama</div></td>';
                    } ?>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">Ultima modifica</div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            <?= Filters::out($MESSAGE['interface']['administration']['ops_col']); ?>
                        </div>
                    </td>
                </tr>
                <!-- Record -->
                <?php foreach ($quests as $row) { ?>
                    <tr class="risultati_elenco_record_gestione">
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?= Filters::date($row['data'], 'd/m/Y'); ?>
                            </div>
                        </td>
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?= Filters::out($row['titolo']); ?>
                            </div>
                        </td>
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?= Filters::out($row['autore']); ?>
                            </div>
                        </td>
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?= Filters::out($row['partecipanti']); ?>
                            </div>
                        </td>
                        <?php if ($quest->viewTramePermission()) {
                            $data = $quest->getTrama(Filters::int($row['trama'])); ?>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?= (!empty($data['titolo'])) ? Filters::out($data['titolo']) : 'Nessuna'; ?>
                                </div>
                            </td>
                        <?php } ?>
                        <td class="casella_elemento">
                            <div class="elementi_elenco">
                                <?= (!empty($data['ultima_modifica'])) ? Filters::date($row['ultima_modifica'], 'd/m/Y') : 'Mai'; ?>
                            </div>
                        </td>

                        <td class="casella_controlli"><!-- Iconcine dei controlli -->
                            <!-- Modifica -->
                            <div class="controlli_elenco">
                                <div class="controllo_elenco">
                                    <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_quest"
                                          method="post">
                                        <input type="hidden" name="id_record" value="<?= Filters::int($row['id']); ?>"/>
                                        <input type="hidden" name="op" value="edit_quest"/>
                                        <input type="image"
                                               src="imgs/icons/edit.png"
                                               alt="<?= Filters::out($MESSAGE['interface']['administration']['ops']['edit']); ?>"
                                               title="<?= Filters::out($MESSAGE['interface']['administration']['ops']['edit']); ?>"/>
                                    </form>
                                </div>
                                <!-- Elimina -->
                                <div class="controllo_elenco">
                                    <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_quest"
                                          method="post">
                                        <input type="hidden" name="id_record" value="<?= Filters::int($row['id']) ?>"/>
                                        <input type="hidden" name="op" value="delete_quest"/>
                                        <input type="image"
                                               src="imgs/icons/erase.png"
                                               onclick="return confirm('Vuoi davvero cancellare questa quest? Non potrà più essere recuperata')"
                                               alt="<?= Filters::out($MESSAGE['interface']['administration']['ops']['erase']); ?>"
                                               title="<?= Filters::out($MESSAGE['interface']['administration']['ops']['erase']); ?>"/>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" class="casella_elemento">
                            <div class="elementi_elenco">
                                <?= Filters::html($row['descrizione']); ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>

    <!-- Paginatore elenco -->
    <div class="pager">
        <?= $quest->getPageNumbers(Filters::int($_REQUEST['offset'])); ?>
    </div>
    <?php
}
?>