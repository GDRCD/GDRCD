<?php

require_once(__DIR__ . '/../includes/required.php');
require_once(__DIR__ . '/Abilita/abilita_class.php');

$abi_class = new Abilita();

$pg = gdrcd_filter('in', $_REQUEST['pg']);
$op = gdrcd_filter('out', $_REQUEST['op']);


switch ($op) {
    case 'upgradeSkill':
        $id_abilita = gdrcd_filter('num', $_REQUEST['what']);
        $pg = gdrcd_filter('in', $_REQUEST['pg']);
        $res = $abi_class->upgradeskill($id_abilita, $pg);
        break;
    case 'downgradeSkill':
        $id_abilita = gdrcd_filter('num', $_REQUEST['what']);
        $pg = gdrcd_filter('in', $_REQUEST['pg']);
        $res = $abi_class->downgradeSkill($id_abilita, $pg);
        break;

}

$abi_list = $abi_class->AbilityList($pg);
$px_rimasti = $abi_class->RemainedExp($pg);
?>

<div class="pagina_scheda">
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['page_name']); ?></h2>
    </div>
    <div class="page_body">
        <div class="menu_scheda"><!-- Menu scheda -->
            <?php include('scheda/menu.inc.php'); ?>
        </div>
        <div class="elenco_abilita"><!-- Elenco abilità -->

            <?php if (isset($res)) { ?>

                <div class="warning"><?= gdrcd_filter('out', $res['mex']); ?></div>

            <?php } ?>

            <div class="titolo_box">
                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['box_title']['skills']); ?>
            </div>
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
                                    <?php if ($abi_class->upgradeSkillPermission($pg, $grado)) { ?>
                                        [
                                        <a href="main.php?page=scheda_skill&pg=<?= $pg; ?>&op=upgradeSkill&what=<?= $id ?>">+</a>]
                                    <?php } ?>
                                    <?php if ($abi_class->downgradeSkillPermission($grado)) { ?>
                                        [
                                        <a href="main.php?page=scheda_skill&pg=<?= $pg; ?>&op=downgradeSkill&what=<?= $id ?>">-</a>]
                                    <?php } ?>
                                </div>
                        </tr>
                    <?php } ?>

                </table>

            </div>
            <div class="form_info">
                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['info_skill_cost']); ?>
            </div>
        </div>
    </div>
</div>