<?php

/** Homepage
 * Markup e procedure della homepage
 * @author Blancks
 */

/*
 * Includo i Crediti
 */
require 'includes/credits.inc.php';

/*
 * Conteggio utenti online
 */
$users = gdrcd_query("SELECT COUNT(nome) AS online FROM personaggio WHERE ora_entrata > ora_uscita AND DATE_ADD(ultimo_refresh, INTERVAL 4 MINUTE) > NOW()");


?>
<div id="wrapper">
    <div id="layout">

        <header>
            <div class="titlecontent">
                <h1><a href="index.php"><?php echo $MESSAGE['homepage']['main_content']['site_title']; ?></a></h1>
                <div class="subtitle"><?php echo $MESSAGE['homepage']['main_content']['site_subtitle']; ?></div>
            </div>
			<?php
				// Include il modulo per il login
				include (__DIR__ . '/login.inc.php');
			?>
        </header>


        <div id="content">
            <aside>
                <div class="side_modules nopad">
                    <?php
                        // Include il modulo con il menù di navigaizone
                        include (__DIR__ . '/nav.inc.php');
                    ?>
                </div>
                <div class="side_modules">
                    <strong><?php echo $users['online'], ' ', gdrcd_filter('out', $MESSAGE['homepage']['forms']['online_now']); ?></strong>
                </div>

                <div class="side_modules">
                    <?php
                        // Include il modulo di reset della password
                        include (__DIR__ . '/reset_password.inc.php');
                    ?>
                </div>

                <div class="side_modules nopad">
                    <?php
                        // Include le statistiche del sito
                        include (__DIR__ . '/user_stats.inc.php');
                    ?>
                </div>
            </aside>

            <main>
                <?php
                    gdrcd_load_modules('homepage__'.$MODULE['content']);
				?>
            </main>

        </div>

        <footer>
            <div>
                <p><?=$REFERENCES?></p>
                <p><?=$CREDITS,' ',$LICENCE?></p>
            </div>
        </footer>
    </div>
</div>