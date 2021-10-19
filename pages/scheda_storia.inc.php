<div class="pagina_scheda_storia">
    <?php /*HELP: */
    //Se non e' stato specificato il nome del pg
    if(isset($_REQUEST['pg']) === false) {
        echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
        exit();
    }
    /*Visualizzo la pagina*/
  ?>
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['page_name']); ?></h2>
    </div>

    <!-- Elenco oggetti nello zaino -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['background']); ?></h2>
    </div>
    <div class="page_body">
        <div class="panels_box">
            <?php /*Oggetti nello zaino*/
            $personaggio = gdrcd_query("SELECT storia FROM personaggio WHERE nome = '".gdrcd_filter('in', $_REQUEST['pg'])."'");
             ?>
            <div class="body_box">
                    <?php
                    /** * Html, bbcode o entrambi ?
                     * @author Blancks
                     */
                    if($PARAMETERS['mode']['user_bbcode'] == 'ON') {
                        if($PARAMETERS['settings']['user_bbcode']['type'] == 'bbd' && $PARAMETERS['settings']['bbd']['free_html'] == 'ON') {
                            echo bbdecoder(gdrcd_html_filter($personaggio['storia']), true);
                        } elseif($PARAMETERS['settings']['user_bbcode']['type'] == 'bbd') {
                            echo bbdecoder(gdrcd_filter('out', $personaggio['storia']), true);
                        } else {
                            echo gdrcd_bbcoder(gdrcd_filter('out', $personaggio['storia']));
                        }
                    } else {
                        echo gdrcd_html_filter($personaggio['storia']);
                    } ?>
        </div>
        </div>
        <!-- Link a piè di pagina -->
        <div class="link_back">
            <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['link']['back']); ?></a>
        </div>
    </div>
</div><!-- Pagina -->