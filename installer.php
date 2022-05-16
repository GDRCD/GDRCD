<?php
$dont_check = true;
require 'header.inc.php'; /*Header comune*/
?>
<div class="pagina_ambientazione">
    <?php
    
    if (DbMigrationEngine::dbNeedsUpdate() and !empty($_POST['do_update'])) {
        try {
            DbMigrationEngine::updateDbSchema();
            echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['homepage']['installer']['done']) . '</div>';
        } catch (Exception $e) {
            echo '<div class="warning">' . gdrcd_filter('out', $e->getMessage()) . '</div>';
        }
    }
    
    if (DbMigrationEngine::dbNeedsUpdate()) {
        ?>
        <form method="post" action="installer.php">
            <h2><?= $MESSAGE['homepage']['installer']['install_title'] ?></h2>
            <p><?= $MESSAGE['homepage']['installer']['install_text'] ?></p>
            <input type="submit" name="do_update" value="Installa"/>&nbsp;&nbsp;<a href="/">Annulla</a>
        </form>
        <?php
    } else { ?>
        <div class="error"><?= $MESSAGE['homepage']['installer']['not_empty'] ?></div>
        <?php
    } ?>
    <!-- Link di ritorno alla homepage -->
    <div class="link_back">
        <a href="index.php">
            <?php echo gdrcd_filter('out', $PARAMETERS['info']['homepage_name']); ?>
        </a>
    </div>
</div>
