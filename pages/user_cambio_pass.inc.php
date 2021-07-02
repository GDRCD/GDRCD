<?php /*HELP: */
$row = gdrcd_query("SELECT email FROM personaggio WHERE nome = '".$_SESSION['login']."'");
$email = $row['email'];
?>
<div class="pagina_user_cambio_pass">
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['pass']['page_name']); ?></h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <?php /*Cambio pass utenti*/
        if($_POST['op'] == 'new') {
            if((gdrcd_password_check(gdrcd_filter_email($_POST['email']),$email)) && (gdrcd_check_pass($_POST['new_pass']) === true)) {
                gdrcd_query("UPDATE personaggio SET pass = '".gdrcd_encript($_POST['new_pass'])."', ultimo_cambiopass = NOW() WHERE nome = '".$_SESSION['login']."'");
                gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".$_SESSION['login']."','".$_SESSION['login']."', NOW(), ".CHANGEDPASS." ,'".$_SERVER['REMOTE_ADDR']."')");
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
                <a href="main.php?page=user_cambio_pass">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['link']['back']); ?>
                </a>
            </div>
        <?php }
        /*Cambio pass admin*/
        if(gdrcd_filter('get', $_POST['op']) == 'force') {
            if(($_SESSION['permessi'] >= MODERATOR) && (gdrcd_check_pass($_POST['new_pass']) === true)) {
                if($_SESSION['permessi'] == SUPERUSER) {
                    $query = "UPDATE personaggio SET pass = '".gdrcd_encript($_POST['new_pass'])."', ultimo_cambiopass = NOW() WHERE nome = '".gdrcd_filter_in($_POST['account'])."'";
                } else {
                    $query = "UPDATE personaggio SET pass = '".gdrcd_encript($_POST['new_pass'])."', ultimo_cambiopass = NOW() WHERE nome = '".gdrcd_filter_in($_POST['account'])."' AND permessi < ".SUPERUSER."";
                }
                gdrcd_query($query);
                /*Registro l'evento */
                gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter_in($_POST['account'])."','".$_SESSION['login']."', NOW(), ".CHANGEDPASS." ,'".$_SERVER['REMOTE_ADDR']."')");
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
                <a href="main.php?page=user_cambio_pass">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['link']['back']); ?>
                </a>
            </div>
            <?php
        }
        /*Visualizzazione di base*/
        if(isset($_POST['op']) === false) { ?>
            <div class="panels_box">
                <div class="form_gioco">
                    <form action="main.php?page=user_cambio_pass" method="post">
                        <div class="form_label">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['pass']['email']); ?>
                        </div>
                        <div class="form_field">
                            <input name="email" />
                        </div>
                        <div class="form_label">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['pass']['new']); ?>
                        </div>
                        <div class="form_field">
                            <input name="new_pass" />
                        </div>
                        <div class="form_submit">
                            <input type="hidden" name="op" value="new" />
                            <input type="submit" name="nulla" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['pass']['submit']['user']); ?>" />
                        </div>
                    </form>
                </div>
            </div>
            <?php
            if($_SESSION['permessi'] >= MODERATOR) {
                $query = ($_SESSION['permessi'] == SUPERUSER) ? "SELECT nome FROM personaggio ORDER BY nome" : "SELECT nome FROM personaggio WHERE permessi < ".SUPERUSER." ORDER BY nome";
                $result = gdrcd_query($query, 'result'); ?>
                <div class="panels_box">
                    <div class="form_gioco">
                        <form action="main.php?page=user_cambio_pass" method="post">
                            <div class="form_label">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['pass']['force']); ?>
                            </div>
                            <div class="form_field">
                                <input name="new_pass" />
                            </div>
                            <div class="form_field">
                                <select name="account">
                                    <option disabled selected><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['pass']['change_to']); ?></option>
                                    <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                        <option value="<?php echo $row['nome']; ?>"><?php echo $row['nome']; ?></option>
                                    <?php }//while
                                    gdrcd_query($result, 'free');
                                    ?>
                                </select>
                            </div>
                            <div class="form_submit">
                                <input type="hidden" name="op" value="force" />
                                <input type="submit" name="nulla" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['pass']['submit']['user']); ?>" />
                            </div>
                        </form>
                    </div>
                </div>
                <?php
            }//if
        }//if ?>
    </div>
</div><!-- Box principale -->
