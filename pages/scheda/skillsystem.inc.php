<?php

require_once(__DIR__ . '/../../includes/required.php');
require_once(__DIR__ . '/../Abilita/abilita_class.php');

$abi_class = new Abilita();

$pg = gdrcd_filter('in', $_REQUEST['pg']);
$op = gdrcd_filter('out', $_REQUEST['op']);


switch ($op) {
    case 'upgradeSkill':
        $id_abilita = gdrcd_filter('num', $_REQUEST['what']);
        $pg = gdrcd_filter('in', $_REQUEST['pg']);
        $res = $abi_class->upgradeskill($id_abilita, $pg);
        break;
}

$abi_list = $abi_class->AbilityList($pg);
$px_rimasti = $abi_class->RemainedExp($pg);
?>
<div class="elenco_abilita"><!-- Elenco abilità -->
    <div class="titolo_box">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['box_title']['skills']); ?>
    </div>
    <?php

    //Decremento skill
    if ((gdrcd_filter('get', $_REQUEST['op']) == 'subskill') && ($_SESSION['permessi'] >= MODERATOR)) {
        if ($ranks[$_REQUEST['what']] == 1) {
            $query = "DELETE FROM clgpersonaggioabilita WHERE id_abilita = " . $_REQUEST['what'] . " AND nome = '" . gdrcd_filter('in', $_REQUEST['pg']) . "' LIMIT 1";
            $ranks[$_REQUEST['what']] = 0;
        } else {
            $ranks[$_REQUEST['what']]--;
            $query = "UPDATE clgpersonaggioabilita SET grado = " . $ranks[$_REQUEST['what']] . " WHERE id_abilita = " . $_REQUEST['what'] . " AND nome = '" . gdrcd_filter('in', $_REQUEST['pg']) . "'";
        }//else
        gdrcd_query($query);
        echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['modified']) . '</div>';
    }//Fine decremento skill
    //conteggio le abilità
    $row = gdrcd_query("SELECT COUNT(*) FROM abilita WHERE id_razza=-1 OR id_razza= " . $personaggio['id_razza'] . "");
    $num = $row['COUNT(*)'];

    //carico l'elenco delle abilità
    $result = gdrcd_query("SELECT nome, car, id_abilita FROM abilita WHERE id_razza=-1 OR id_razza= " . $personaggio['id_razza'] . " ORDER BY id_razza DESC, nome", 'result');
    $count = 0;
    $total = 0;
    ?>
    <div class="form_info">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['avalaible_xp']) . ': ' . $px_rimasti; ?>
    </div>
    <div class="div_colonne_abilita_scheda">

        <table class="colonne_abilita_scheda">
            <?php foreach ($abi_list as $abi) {

                $id = gdrcd_filter('num', $abi['id_abilita']);
                $nome = gdrcd_filter('out', $abi['nome']);
                $car = gdrcd_filter('out', $PARAMETERS['names']['stats']['car' . gdrcd_filter('out', $abi['car'])]);
                $grado = gdrcd_filter('num', $abi['grado']);
                ?>
                <tr>
                    <td>
                        <div class="abilita_scheda_nome">
                            <?= $nome; ?>
                        </div>
                    </td>
                    <td>
                        <div class="abilita_scheda_car">
                            <?= "({$car})"; ?>
                        </div>
                    </td>
                    <td>
                        <div class="abilita_scheda_tank">
                            <?= $grado; ?>
                        </div>
                    </td>
                    <td>
                        <div class="abilita_scheda_sub">
                            <?php if ($abi_class->upgradeSkillPermission($pg)) { ?>
                                [<a href="main.php?page=scheda&pg=<?= $pg; ?>&op=upgradeSkill&what=<?= $id ?>">+</a>]
                            <?php } ?>
                </tr>
            <?php } ?>

        </table>

        <!--
        <table class="colonne_abilita_scheda">
            <tr>
                <?php while ($row = gdrcd_query($result, 'fetch')) {
            if ($count == 0) {
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
                        <?php echo '(' . gdrcd_filter('out', $PARAMETERS['names']['stats']['car' . $row['car']]) . ')'; ?>
                    </div>
                </td>
                <td>
                    <div class="abilita_scheda_tank">
                        <?php echo 0 + gdrcd_filter('out', $ranks[$row['id_abilita']]); ?>
                    </div>
                </td>
                <td>
                    <div class="abilita_scheda_sub">
                        <?php /*Stampo il form di incremento se il pg ha abbastanza px*/
            if ((((($ranks[$row['id_abilita']] + 1) * $PARAMETERS['settings']['px_x_rank']) <= ($px_totali_pg - $px_spesi)) && (gdrcd_filter('get', $_REQUEST['pg']) == $_SESSION['login']) && ($ranks[$row['id_abilita']] < $PARAMETERS['settings']['skills_cap'])) || ($_SESSION['permessi'] >= MODERATOR)) { ?>
                            [<a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']
            ) ?>&op=addskill&what=<?php echo $row['id_abilita'] ?>">+</a>]
                            <?php if (($_SESSION['permessi'] >= MODERATOR) && ($ranks[$row['id_abilita']] > 0)) { ?>
                                [
                                <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']) ?>&op=subskill&what=<?php echo $row['id_abilita'] ?>">-</a>]
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
            if (($count >= ceil($num / 2)) || ($total >= $num)) {
                $count = 0;
                echo '</table></td>';
            }
        }//while
        gdrcd_query($result, 'free');
        ?>
            </tr>
        </table> -->
    </div>
    <div class="form_info">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['info_skill_cost']); ?>
    </div>
</div>