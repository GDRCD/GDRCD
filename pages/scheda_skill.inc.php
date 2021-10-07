<?php

require_once(__DIR__ . '/../includes/required.php');
require_once(__DIR__ . '/Abilita/abilita_class.php');

$abi_class = new Abilita();

$pg = gdrcd_filter('in', $_REQUEST['pg']);
$op = gdrcd_filter('out', $_REQUEST['op']);

if ($abi_class->AbiVisibility($pg)) {

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

        case 'operationDone':
            $mex_id = gdrcd_filter('out', $_REQUEST['mex']);
            $mex = $abi_class->operationDone($mex_id);
            break;
    }

    # Cambio pagina per evitare doppio invio
    if (isset($res)) {
        $mex_id = $res['mex']; ?>

        <script>window.location.href = '/main.php?page=scheda_skill&pg=Super&op=operationDone&mex=<?=$mex_id;?>';</script>

        <?php
        die();
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

                <?php if (isset($mex)) { ?>

                    <div class="warning"><?= gdrcd_filter('out', $mex); ?></div>

                <?php } ?>

                <div class="titolo_box">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['box_title']['skills']); ?>
                </div>
                <div class="form_info">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['avalaible_xp']) . ': ' . $px_rimasti; ?>
                </div>
                <div class="div_colonne_abilita_scheda">

                    <div class="fake-table colonne_abilita_scheda">
                        <?php foreach ($abi_list as $abi) {

                            $id = gdrcd_filter('num', $abi['id_abilita']);
                            $nome = gdrcd_filter('out', $abi['nome']);
                            $car = gdrcd_filter('out', $PARAMETERS['names']['stats']['car' . gdrcd_filter('out', $abi['car'])]);
                            $grado = gdrcd_filter('num', $abi['grado']);
                            $abi_extra_data = $abi_class->LvlData($id, $grado); ?>

                            <div class="tr">
                                <div class="sub-tr">
                                    <div class="abilita_scheda_nome td" title="Clicca per aprire/chiudere la descrizione.">
                                        <?= $nome; ?>
                                    </div>
                                    <div class="abilita_scheda_car td">
                                        <?= "({$car})"; ?>
                                    </div>
                                    <div class="abilita_scheda_tank td">
                                        <?= $grado; ?>
                                    </div>
                                    <div class="abilita_scheda_sub td">
                                        <?php if ($abi_class->upgradeSkillPermission($pg, $grado)) { ?>
                                            [
                                            <a href="main.php?page=scheda_skill&pg=<?= $pg; ?>&op=upgradeSkill&what=<?= $id ?>">+</a>]
                                        <?php } ?>
                                        <?php if ($abi_class->downgradeSkillPermission($grado)) { ?>
                                            [
                                            <a href="main.php?page=scheda_skill&pg=<?= $pg; ?>&op=downgradeSkill&what=<?= $id ?>">-</a>]
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
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['info_skill_cost']); ?>
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
