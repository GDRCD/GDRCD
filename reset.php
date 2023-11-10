<?php
$dont_check = true;
require 'core/required.php';

if ( !DbMigrationEngine::dbNeedsInstallation() ) {
    $development = Filters::bool(Functions::get_constant('DEVELOPING', false));

    if ( $development || !DbMigrationEngine::dbConfigExist() ) {
        ?>
        <div class="pagina_ambientazione">
            <?php

            if ( isset($_POST['do_update']) ) {
                switch ( $_POST['do_update'] ) {
                    case 'Reset':
                        DbMigrationEngine::resetDB();
                        ?>

                        <div class="warning">Fatto!</div>
                        <div class="link_back">
                            <a href="index.php">
                                Indietro
                            </a>
                        </div>

                        <?php
                        break;
                }
            }

            ?>

            <form method="post" action="reset.php">
                <h2>GDRCD 6.0</h2>
                <p>resettare database?</p>
                <input type="submit" name="do_update" value="Reset"/>&nbsp;&nbsp;<a href="/">Annulla</a>
            </form>

            <!-- Link di ritorno alla homepage -->
            <div class="link_back">
                <a href="index.php">
                    Indietro
                </a>
            </div>
        </div>
    <?php }
} else { ?>
    <div class="pagina_ambientazione">
        <h2>GDRCD 6.0</h2>
        <p>Il database non è stato installato.</p>
        <div class="link_back">
            <a href="installer.php">
                Vai alla pagina di installazione
            </a>
        </div>
        <div class="link_back">
            <a href="index.php">
                Indietro
            </a>
        </div>
    </div>
<?php } ?>