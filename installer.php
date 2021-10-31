<?php
$dont_check = true;
require 'header.inc.php'; /*Header comune*/
?>
<div class="pagina_ambientazione">
    <?php

    if(!empty($_POST['do_update'])) {
        $target_migration = empty($_POST['target']) ? null : (int)$_POST['target'];
        try {
            DbMigrationEngine::updateDbSchema($target_migration);
            echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['homepage']['installer']['done']) . '</div>';
        }
        catch (Exception $e){
            echo '<div class="warning">' . gdrcd_filter('out', $e->getMessage()) . '</div>';
        }
    }
    
    if (DbMigrationEngine::dbNeedsUpdate()) {
        ?>
        <form method="post" action="installer.php">
            <h2><?= $MESSAGE['homepage']['installer']['install_title'] ?></h2>
            <p><?= $MESSAGE['homepage']['installer']['install_text'] ?></p>
            <input type="submit" name="do_update" value="Installa" />&nbsp;&nbsp;<a href="/">Annulla</a>
        </form>
        <?php
    }
    /* else{


        ?>
        <div class="error"><?= $MESSAGE['homepage']['installer']['not_empty'] ?></div>
        <form method="post" action="installer.php">
            <fieldset>
                <legend>Opzioni Avanzate</legend>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['homepage']['installer']['migrate']); ?>
                </div>
                <div class="form_info">
                    <?php echo gdrcd_filter('out', $MESSAGE['homepage']['installer']['migrate_warn']); ?>
                </div>
                <div class='form_field'>
                    <select name="target" id="target">
                        <?php
                        $migrations = DbMigrationEngine::getAllAvailableMigrations();
                        foreach($migrations as $k => $m) {
                            echo '<option value="'.$m->getMigrationId().'" '
                                .($k == (count($migrations) -1) ? 'selected="selected"' : '')
                                .'>'.gdrcd_filter('out', get_class($m)).'</option>';
                        }
                        ?>
                    </select>
                </div>

                <input type="submit" name="do_update" value="Esegui" />
            </fieldset>
        </form>
        <?php
    } */ ?>
    <!-- Link di ritorno alla homepage -->
    <div class="link_back">
        <a href="index.php">
            <?php echo gdrcd_filter('out', $PARAMETERS['info']['homepage_name']); ?>
        </a>
    </div>
</div>
<?php require('footer.inc.php');  /*Footer comune*/ ?>
