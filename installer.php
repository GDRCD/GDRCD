<?php

$dont_check = true;
require 'core/required.php';

?>
<div class="pagina_ambientazione">
    <?php

    if ( isset($_POST['do_update']) ) {
        switch ( $_POST['do_update'] ) {
            case 'Installa':
                if ( DbMigrationEngine::dbNeedsInstallation() and !empty($_POST['do_update']) ) {

                    try {
                        if ( file_exists(ROOT . '/gdrcd_db.sql') ) {
                            DbMigrationEngine::migrateDb(); ?>

                            <div class="warning">Fatto!</div>
                            <div class="link_back">
                                <a href="index.php">
                                    Indietro
                                </a>
                            </div>

                            <?php
                            die();
                        } else {
                            echo "<div class='warning'>FILE NON ESISTENTe.</div>";
                        }
                    } catch ( Exception $e ) {
                        echo '<div class="warning">' . gdrcd_filter('out', $e->getMessage()) . '</div>';
                    }
                }

                break;
        }
    }

    if ( DbMigrationEngine::dbNeedsInstallation() ) {
        ?>
        <form method="post" action="installer.php">
            <h2>GDRCD 6.0</h2>
            <p>Installare database?</p>
            <input type="submit" name="do_update" value="Installa"/>&nbsp;&nbsp;<a href="/">Annulla</a>
        </form>
        <?php
    } else { ?>
        <div class="error">Database già esistente.</div>
        <?php
    } ?>
    <!-- Link di ritorno alla homepage -->
    <div class="link_back">
        <a href="index.php">
            Indietro
        </a>
    </div>
</div>
