<?php

$stat_class = Statistiche::getInstance();
$stat_id = Filters::int($_GET['stat']);
$id_pg = Filters::int($_GET['id_pg']);

?>

<div class="stats_list">

    <div class="fake-table">

        <div class="header">
            <?= Filters::out($MESSAGE['interface']['sheet']['box_title']['stats']); ?>
        </div>

        <?php foreach (PersonaggioStats::getPgAllStats($id_pg) as $stat) {

            $id = Filters::int($stat['id']);
            $nome = Filters::out($stat['nome']);
            $descrizione = Filters::text($stat['descrizione']);
            $valore = Filters::int($stat['valore']); ?>

            <div class="tr">
                <div class="sub-tr">
                    <div class="td open_text" title="Clicca per aprire/chiudere la descrizione.">
                        <span><?= $nome; ?></span>
                    </div>
                    <div class="td">
                        <?= $valore; ?>
                    </div>
                    <div class="td">
                        <?php if (PersonaggioStats::permissionUpgradeStats() && PersonaggioStats::isPgStatUpgradable($id, $id_pg)) { ?>
                            [
                            <a href="main.php?page=scheda_stats&id_pg=<?= $id_pg; ?>&op=upgrade&stat=<?= $id ?>">+</a>]
                        <?php } ?>
                        <?php if (PersonaggioStats::permissionDowngradeStats() && PersonaggioStats::isPgStatDowngradable($id, $id_pg)) { ?>
                            [
                            <a href="main.php?page=scheda_stats&id_pg=<?= $id_pg; ?>&op=downgrade&stat=<?= $id ?>">-</a>]
                        <?php } ?>
                    </div>
                </div>

                <div class="sub-tr hidden_text">
                    <div class="td">
                        <div class="table-subtitle">Descrizione</div>
                        <div class="table-text"><?= !empty($descrizione) ? $descrizione : 'Nessuna descrizione.'; ?></div>
                    </div>
                </div>

            </div>
        <?php } ?>

    </div>

    <div class="form_info">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['info_stats']); ?>
    </div>

</div>

