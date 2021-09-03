<?php

/**
 * Ottengo l'elenco completo dei Forum, compresivo di topic non letti dall'utente.
 * I Forum vengono raggruppati per id_araldo, in modo da evitare duplicamento
 */
$result = gdrcd_query(
        "SELECT id_araldo, nome, tipo, proprietari,
                     # Determino se l'utente ha dei messaggi non letti nel Forum
                       IF(
                           (SELECT COUNT(messaggioaraldo.id_messaggio) FROM messaggioaraldo WHERE messaggioaraldo.id_araldo = araldo.id_araldo AND messaggioaraldo.id_messaggio_padre = -1)
                               >
                           (SELECT COUNT(araldo_letto.id) FROM araldo_letto WHERE araldo_letto.araldo_id = araldo.id_araldo AND nome = '".$_SESSION['login']."'),
                           1,
                           0
                       ) as hasNewMessage 
                FROM araldo
                ORDER BY tipo, nome",
        'result'
);

/**
 * Scorro l'elenco dei Forum ottenuto per effettuare i controlli sui permessi
 * e verificare se sono presenti nuovi messaggi da leggere
 *
 * I Forum vengono salvati in un array così formato:
 * [TIPO_FORUM]
 *      [ID_FORUM]
 *          [DETTAGLI_FORUM]
 *
 * @author Kasa
 */
$forums = [];
while($row = gdrcd_query($result, 'fetch')) {
    // Controllo i permessi sulla bacheca
    if(($row['tipo'] <= PERTUTTI) || (($row['tipo'] == SOLORAZZA) && ($_SESSION['id_razza'] == $row['proprietari'])) || (($row['tipo'] == SOLOGILDA) && (strpos($_SESSION['gilda'],'*'.$row['proprietari'].'*') != false)) || (($row['tipo'] == SOLOMASTERS) && ($_SESSION['permessi'] >= GAMEMASTER)) || ($_SESSION['permessi'] >= MODERATOR)){
        // Convalido il Forum per la visualizzazione
        $forums[$row['tipo']][$row['id_araldo']] = $row;
    }
}

gdrcd_query($result, 'free');

// Se ho la visibilità di almeno un record, procedo con la creazione della tabella
if(!empty($forums)) {
    //
    $tr = [];

    // Scorro le tipologie di Forum
    foreach ($forums as $forumTipo => $forumRows) {
        // Divido i Forum per tipo
        $tr[] = '<tr>
                    <!-- Intestazione tabella -->
                    <td colspan="2">
                        <div class="capitolo_elenco">'.
                            gdrcd_filter('out', $PARAMETERS['names']['forum']['plur'].' '.strtolower($MESSAGE['interface']['forums']['type'][$forumTipo])).'
                        </div>
                    </td>
                </tr>';


        // Scorro i Forum
        foreach ($forumRows as $forumId => $forumRow) {
            // Costruisco le celle con le informazioni del Forum
            $td = [];

            // Mostro la nota in caso di nuovi topic nel Forum
            $td[] = '<td class="forum_main_post_author">
                        <div class="forum_date_big">'.
                            ( $forumRow['hasNewMessage'] == 1 ? $MESSAGE['interface']['forums']['topic']['new_posts_forum'] : NULL ).'   
                        </div>
                    </td>';

            // Costruisco la cella dedicata al nome del Forum
            $td[] = '<td class="casella_elemento">
                        <div class="elementi_elenco">
                            <a href="main.php?page=forum&op=visit&what='.gdrcd_filter('out', $forumRow['id_araldo']).'&name='.gdrcd_filter('out', $forumRow['nome']).'" >
                                '.gdrcd_filter('out', $forumRow['nome']).'    
                            </a>
                        </div>
                    </td>';

            $tr[] = '<tr>'.implode($td).'</tr>';

            // Svuoto le celle
            unset($td);
        }
    }

    // Costruisco la tabella, inserendo i Forum appena creati
    $forumTable = '<table>'.implode('', $tr).'</table>';
}
?>
<!-- Elenco forum -->
<div class="elenco_esteso">
    <div class="elenco_record_gioco">
        <?php // Mostro la tabella
            if(isset($forumTable) && !empty($forumTable)) {
                echo $forumTable;
            }
            ?>
        <?php //Pulsante segna tutto come letto ?>
        <div class="panels_box">
            <div class="form_gioco">
                <form action="main.php?page=forum" method="post">
                    <div class="form_submit">
                        <input type="hidden" name="op" value="readall" />
                        <input type="submit" name="dummy" value="Segna tutto come letto" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
