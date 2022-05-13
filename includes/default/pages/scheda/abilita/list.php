<?php

require_once(__DIR__ . '/../../../core/required.php');

$scheda_class = SchedaAbilita::getInstance();
$abi_class = Abilita::getInstance();
$stat_class = Statistiche::getInstance();
$pg_abi_class = PersonaggioAbilita::getInstance();

$pg = Filters::in($_REQUEST['pg']);
$id_pg = Filters::in($_REQUEST['id_pg']);
$abi_list = $abi_class->getAllAbilita();
$px_rimasti = $pg_abi_class->RemainedExp($id_pg);
?>

<div class="pagina_scheda">
    <div class="page_body">
        <div class="menu_scheda"><!-- Menu scheda -->
            <?php include('scheda/menu.inc.php'); ?>
        </div>
        <div class="elenco_abilita"><!-- Elenco abilità -->

            <?php if (isset($mex)) { ?>

                <div class="warning"><?= Filters::out($mex); ?></div>

            <?php } ?>

            <div class="form_info">
                <?php echo Filters::out($MESSAGE['interface']['sheet']['avalaible_xp']) . ': ' . $px_rimasti; ?>
            </div>
            <div class="div_colonne_abilita_scheda">

                <div class="fake-table colonne_abilita_scheda">

                    <div class="header">
                        <?php echo Filters::out($MESSAGE['interface']['sheet']['box_title']['skills']); ?>
                    </div>

                    <?php foreach ($abi_list as $abi) {

                        $id = Filters::int($abi['id']);
                        $nome = Filters::out($abi['nome']);
                        $stat_data = $stat_class->getStat($abi['statistica']);
                        $stat_name = Filters::out($stat_data['nome']);
                        $pg_abi_data = $pg_abi_class->getPgAbility($id,$id_pg,'grado');
                        $grado = Filters::int($pg_abi_data['grado']);

                        $abi_extra_data = $scheda_class->LvlData($id, $grado); ?>

                        <div class="tr">
                            <div class="sub-tr">
                                <div class="abilita_scheda_nome td" title="Clicca per aprire/chiudere la descrizione.">
                                    <?= $nome; ?>
                                </div>
                                <div class="abilita_scheda_car td">
                                    <?= "({$stat_name})"; ?>
                                </div>
                                <div class="abilita_scheda_tank td">
                                    <?= $grado; ?>
                                </div>
                                <div class="abilita_scheda_sub td">
                                    <?php if ($pg_abi_class->permissionUpgradeAbilita($id_pg, $grado)) { ?>
                                        [
                                        <a href="main.php?page=scheda_skill&pg=<?= $pg; ?>&id_pg=<?= $id_pg; ?>&op=upgrade&abilita=<?= $id ?>">+</a>]
                                    <?php } ?>
                                    <?php if ($pg_abi_class->permissionDowngradeAbilita($grado)) { ?>
                                        [
                                        <a href="main.php?page=scheda_skill&pg=<?= $pg; ?>&id_pg=<?= $id_pg; ?>&op=downgrade&abilita=<?= $id ?>">-</a>]
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="sub-tr">
                                <div class="abilita_scheda_text">
                                    <div class="default_tex">
                                        <div class="table-subtitle">Descrizione</div>
                                        <div class="table-text"><?= $abi_extra_data['text']; ?></div>
                                    </div>
                                    <?php if ($abi_class->extraActive()) { ?>
                                        <div class="actual_extra_text">
                                            <div class="table-subtitle">Descrizione Livello attuale</div>
                                            <div class="table-text"><?= $abi_extra_data['lvl_extra_text']; ?></div>
                                        </div>
                                        <div class="next_extra_text">
                                            <div class="table-subtitle">Descrizione Livello successivo</div>
                                            <div class="table-text"><?= $abi_extra_data['next_lvl_extra_text']; ?></div>
                                        </div>
                                        <div class="next_extra_text">
                                            <div class="table-subtitle">Costo Livello successivo</div>
                                            <div class="table-text"
                                                 style="text-align: center"><?= $abi_extra_data['price']; ?></div>
                                        </div>
                                    <?php }
                                    if ($abi_class->requirementActive()) {
                                        ?>
                                        <div class="next_req_text">
                                            <div class="table-subtitle">Requisiti Livello successivo</div>
                                            <div class="table-text"><?= $abi_extra_data['requirement']; ?></div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                        </div>
                    <?php } ?>

                </div>

            </div>
            <div class="form_info">
                <?php echo Filters::out($MESSAGE['interface']['sheet']['info_skill_cost']); ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {

        $('.colonne_abilita_scheda .abilita_scheda_nome').on('click', function (e) {
            e.stopImmediatePropagation();

            $(this).closest('.tr').find('.abilita_scheda_text').slideToggle();
        })

    })


</script>
