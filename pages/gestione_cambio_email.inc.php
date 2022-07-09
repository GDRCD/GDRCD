<div class="pagina_gestione_cambio_email">
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['pass']['page_name']); ?></h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <?php if (gdrcd_filter('get', $_POST['op']) == 'force') {
            if (($_SESSION['permessi'] >= MODERATOR)) {
                if ($_SESSION['permessi'] == SUPERUSER) {
                    $query = "UPDATE personaggio SET email = '" . gdrcd_encript($_POST['new_email']) . "' WHERE nome = '" . gdrcd_filter_in($_POST['account']) . "'";
                } else {
                    $query = "UPDATE personaggio SET email = '" . gdrcd_encript($_POST['new_email']) . "' WHERE nome = '" . gdrcd_filter_in($_POST['account']) . "' AND permessi < " . SUPERUSER . "";
                }
                gdrcd_query($query);

                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                </div>
            <?php } else { ?>
                <div class="error">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['cant_do']); ?>
                </div>
            <?php }//else ?>
            <div class="link_back">
                <a href="main.php?page=gestione_cambio_email">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['link']['back']); ?>
                </a>
            </div>
            <?php
        }
        /*Visualizzazione di base*/
        if (isset($_POST['op']) === false) {
            if ($_SESSION['permessi'] >= MODERATOR) {
                $query = ($_SESSION['permessi'] == SUPERUSER) ? "SELECT nome FROM personaggio ORDER BY nome" : "SELECT nome FROM personaggio WHERE permessi < " . SUPERUSER . " ORDER BY nome";
                $result = gdrcd_query($query, 'result'); ?>
                <div class="panels_box">
                    <div class="form_gioco">
                        <form action="main.php?page=gestione_cambio_email" method="post">
                            <div class="form_label">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['email']['email']); ?>
                            </div>
                            <div class="form_field">
                                <input name="new_email" required/>
                            </div>

                            <div class="form_label">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['email']['new']); ?>
                            </div>
                            <div class="form_field">
                                <select name="account" required>
                                    <option disabled
                                            selected><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['pass']['change_to']); ?></option>
                                    <?php while ($row = gdrcd_query($result, 'fetch')) { ?>
                                        <option value="<?php echo $row['nome']; ?>"><?php echo $row['nome']; ?></option>
                                    <?php }//while
                                    gdrcd_query($result, 'free');
                                    ?>
                                </select>
                            </div>
                            <div class="form_submit">
                                <input type="hidden" name="op" value="force"/>
                                <input type="submit" name="nulla"
                                       value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['email']['submit']['user']); ?>"/>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
            }//if
        }//if ?>
    </div>
</div><!-- Box principale -->
