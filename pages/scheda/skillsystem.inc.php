<?php
//carico le sole abilità del pg
$abilita = gdrcd_query("SELECT id_abilita, grado FROM clgpersonaggioabilita WHERE nome='".gdrcd_filter('in', $_REQUEST['pg'])."'", 'result');

$px_spesi = 0;
while($row = gdrcd_query($abilita, 'fetch')) {
    /*Costo in px della singola abilità*/
    $px_abi = $PARAMETERS['settings']['px_x_rank'] * (($row['grado'] * ($row['grado'] + 1)) / 2);
    /*Costo totale*/
    $px_spesi += $px_abi;
    $ranks[$row['id_abilita']] = $row['grado'];
}

$personaggio=gdrcd_query("SELECT id_razza, esperienza FROM personaggio WHERE nome='".gdrcd_filter('in', $_REQUEST['pg'])."'", 'query');


$px_totali_pg = gdrcd_filter('int', $personaggio['esperienza']) ;


?>
<div class="elenco_abilita"><!-- Elenco abilità -->
    <div class="titolo_box">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['box_title']['skills']); ?>
    </div>
    <?php
    //Incremento skill
    if((gdrcd_filter('get', $_REQUEST['op']) == 'addskill') && (($_SESSION['login'] == gdrcd_filter('out', $_REQUEST['pg'])) || ($_SESSION['permessi'] >= MODERATOR))) {
        $px_necessari = $PARAMETERS['settings']['px_x_rank'] * ($ranks[$_REQUEST['what']] + 1);
        if(($px_totali_pg - $px_spesi) >= $px_necessari) {
            $px_spesi += $px_necessari;
            if($px_necessari == $PARAMETERS['settings']['px_x_rank']) {
                $query = "INSERT INTO clgpersonaggioabilita (id_abilita, nome, grado) VALUES (".gdrcd_filter('num', $_REQUEST['what']).", '".gdrcd_filter('in', $_REQUEST['pg'])."', 1)";
                $ranks[$_REQUEST['what']] = 1;
            } else {
                $ranks[$_REQUEST['what']]++;
                $query = "UPDATE clgpersonaggioabilita SET grado = ".$ranks[$_REQUEST['what']]." WHERE id_abilita = ".gdrcd_filter('num', $_REQUEST['what'])." AND nome = '".gdrcd_filter('in', $_REQUEST['pg'])."'";
            }//else
            gdrcd_query($query);
            echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['modified']).'</div>';
        }
    }//Fine incremento skill
    //Decremento skill
    if((gdrcd_filter('get', $_REQUEST['op']) == 'subskill') && ($_SESSION['permessi'] >= MODERATOR)) {
        if($ranks[$_REQUEST['what']] == 1) {
            $query = "DELETE FROM clgpersonaggioabilita WHERE id_abilita = ".$_REQUEST['what']." AND nome = '".gdrcd_filter('in', $_REQUEST['pg'])."' LIMIT 1";
            $ranks[$_REQUEST['what']] = 0;
        } else {
            $ranks[$_REQUEST['what']]--;
            $query = "UPDATE clgpersonaggioabilita SET grado = ".$ranks[$_REQUEST['what']]." WHERE id_abilita = ".$_REQUEST['what']." AND nome = '".gdrcd_filter('in', $_REQUEST['pg'])."'";
        }//else
        gdrcd_query($query);
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['modified']).'</div>';
    }//Fine decremento skill
    //conteggio le abilità
    $row = gdrcd_query("SELECT COUNT(*) FROM abilita WHERE id_razza=-1 OR id_razza= ".$personaggio['id_razza']."");
    $num = $row['COUNT(*)'];

    //carico l'elenco delle abilità
    $result = gdrcd_query("SELECT nome, car, id_abilita FROM abilita WHERE id_razza=-1 OR id_razza= ".$personaggio['id_razza']." ORDER BY id_razza DESC, nome", 'result');
    $count = 0;
    $total = 0;
    ?>
    <div class="form_info">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['avalaible_xp']).': '.($px_totali_pg - $px_spesi); ?>
    </div>
    <div class="div_colonne_abilita_scheda">
        <table class="colonne_abilita_scheda">
            <tr>
                <?php while($row = gdrcd_query($result, 'fetch')) {
                    if($count == 0) {
                        echo '<td><table>';
                    } ?>
                    <tr>
                        <td>
                            <div class="abilita_scheda_nome">
                                <?php echo gdrcd_filter('out', $row['nome']); ?>
                            </div>
                        </td>
                        <td>
                            <div class="abilita_scheda_car">
                                <?php echo '('.gdrcd_filter('out', $PARAMETERS['names']['stats']['car'.$row['car']]).')'; ?>
                            </div>
                        </td>
                        <td>
                            <div class="abilita_scheda_tank">
                                <?php echo 0 + gdrcd_filter('int', $ranks[$row['id_abilita']]); ?>
                            </div>
                        </td>
                        <td>
                            <div class="abilita_scheda_sub">
                                <?php /*Stampo il form di incremento se il pg ha abbastanza px*/
                                if((((($ranks[$row['id_abilita']] + 1) * $PARAMETERS['settings']['px_x_rank']) <= ($px_totali_pg - $px_spesi)) && (gdrcd_filter('get', $_REQUEST['pg']) == $_SESSION['login']) && ($ranks[$row['id_abilita']] < $PARAMETERS['settings']['skills_cap'])) || ($_SESSION['permessi'] >= MODERATOR)) { ?>
                                    [<a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']
                                    ) ?>&op=addskill&what=<?php echo $row['id_abilita'] ?>">+</a>]
                                    <?php if(($_SESSION['permessi'] >= MODERATOR) && ($ranks[$row['id_abilita']] > 0)) { ?>
                                        [<a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']) ?>&op=subskill&what=<?php echo $row['id_abilita'] ?>">-</a>]
                                    <?php
                                    }
                                } else {
                                    echo '&nbsp;';
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                    $count++;
                    $total++;
                    if(($count >= ceil($num / 2)) || ($total >= $num)) {
                        $count = 0;
                        echo '</table></td>';
                    }
                }//while

            ?>
            </tr>
        </table>
    </div>
    <div class="form_info">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['info_skill_cost']); ?>
    </div>
</div>