<?php

require_once(__DIR__ . '/../includes/required.php');

$abi_class = Abilita::getInstance();
$stat_class = Statistiche::getInstance();
$pg = Filters::in($_REQUEST['pg']);
$id_pg = Filters::in($_REQUEST['id_pg']);
$op = Filters::out($_REQUEST['op']);

var_dump($id_pg);

if ($abi_class->AbiVisibility($pg)) {

    switch ($op) {
        case 'upgradeSkill':
            $id_abilita = Filters::int($_REQUEST['what']);
            $res = $abi_class->upgradeskill($id_abilita, $id_pg);
            break;
        case 'downgradeSkill':
            $id_abilita = Filters::int($_REQUEST['what']);
            $res = $abi_class->downgradeSkill($id_abilita, $id_pg);
            break;

        case 'operationDone':
            $mex_id = Filters::out($_REQUEST['mex']);
            $mex = $abi_class->operationDone($mex_id);
            break;
    }

    # Cambio pagina per evitare doppio invio
    if (isset($res)) {
        $mex_id = $res['mex']; ?>

        <script>window.location.href = '/main.php?page=scheda_skill&pg=<?=$pg;?>&id_pg=<?=$id_pg;?>&op=operationDone&mex=<?=$mex_id;?>';</script>

        <?php
        die();
    }

    $abi_list = $abi_class->AbilityList($id_pg);
    $px_rimasti = $abi_class->RemainedExp($id_pg);
    ?>

    <div class="pagina_scheda">
        <div class="page_title">
            <h2><?php echo Filters::out($MESSAGE['interface']['sheet']['page_name']); ?></h2>
        </div>
        <div class="page_body">
            <div class="menu_scheda"><!-- Menu scheda -->
                <?php include('scheda/menu.inc.php'); ?>
            </div>
            <div class="elenco_abilita"><!-- Elenco abilità -->

                <?php if (isset($mex)) { ?>

                    <div class="warning"><?= Filters::out($mex); ?></div>

                <?php } ?>

                <div class="titolo_box">
                    <?php echo Filters::out($MESSAGE['interface']['sheet']['box_title']['skills']); ?>
                </div>
                <div class="form_info">
                    <?php echo Filters::out($MESSAGE['interface']['sheet']['avalaible_xp']) . ': ' . $px_rimasti; ?>
                </div>
                <div class="div_colonne_abilita_scheda">

                    <div class="fake-table colonne_abilita_scheda">
                        <?php foreach ($abi_list as $abi) {

                            $id = Filters::int($abi['id_abilita']);
                            $nome = Filters::out($abi['nome']);
                            $stat_data = $stat_class->getStat($abi['car']);
                            $stat_name = Filters::out($stat_data['nome']);
                            $grado = Filters::int($abi['grado']);
                            $abi_extra_data = $abi_class->LvlData($id, $grado); ?>

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
                                        <?php if ($abi_class->upgradeSkillPermission($id_pg, $grado)) { ?>
                                            [
                                            <a href="main.php?page=scheda_skill&pg=<?= $pg; ?>&id_pg=<?=$id_pg;?>&op=upgradeSkill&what=<?= $id ?>">+</a>]
                                        <?php } ?>
                                        <?php if ($abi_class->downgradeSkillPermission($grado)) { ?>
                                            [
                                            <a href="main.php?page=scheda_skill&pg=<?= $pg; ?>&id_pg=<?=$id_pg;?>&op=downgradeSkill&what=<?= $id ?>">-</a>]
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
                                                <div class="table-text" style="text-align: center"><?= $abi_extra_data['price']; ?></div>
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
        $(function(){

            $('.colonne_abilita_scheda .abilita_scheda_nome').on('click',function(e){
                e.stopImmediatePropagation();

                $(this).closest('.tr').find('.abilita_scheda_text').slideToggle();
            })

        })


    </script>

<?php } else { ?>

    <div class="warning">Non hai i permessi per visualizzare le abilità di questo personaggio.</div>

<?php } ?>
