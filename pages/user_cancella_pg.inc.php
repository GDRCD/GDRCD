<?php /*HELP: */
$row = gdrcd_query("SELECT email FROM personaggio WHERE nome = '".$_SESSION['login']."'");
$email = $row['email'];
?>
<div class="pagina_user_cancella_pg">
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['delete']['page_name']); ?></h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <?php /*Cancella il tuo account*/
        if($_POST['op'] == 'delete') {
            if(($email == gdrcd_filter_email($_POST['email'])) && (gdrcd_check_pass($_POST['new_pass']) === true)) {
                gdrcd_query("UPDATE personaggio SET permessi = -1 WHERE nome = '".$_SESSION['login']."' AND pass = '".gdrcd_encript($_POST['new_pass'])."'");
                gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".$_SESSION['login']."','".$_SESSION['login']."', NOW(), ".DELETEPG." ,'".gdrcd_filter('in', $MESSAGE['interface']['user']['delete']['undeleted'])."')");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                </div>
                <div class="page_title">
                    <h2>
                        <a href="index.php">
                            <?php echo $PARAMETERS['info']['homepage_name']; ?>
                        </a>
                    </h2>
                </div>
                <?php session_destroy();
            } else { ?>
                <div class="error">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['cant_do']); ?>
                </div>
            <?php }//else ?>
            <div class="link_back">
                <a href="main.php?page=user_cancella_pg">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['link']['back']); ?>
                </a>
            </div>
        <?php }
        /*Cancella altri - MODERATORE (Disabilita account)*/
        if((gdrcd_filter('get', $_POST['op']) == 'force') && ($_SESSION['permessi'] == MODERATOR)) {
            gdrcd_query("UPDATE personaggio SET permessi = -1 WHERE nome = '".gdrcd_filter('in', $_POST['account'])."' AND permessi < ".SUPERUSER."");
            gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_POST['account'])."','".$_SESSION['login']."', NOW(), ".DELETEPG." ,'".gdrcd_filter('in', $MESSAGE['interface']['user']['delete']['deleted'])."')");
            ?>
            <div class="warning">
                <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
            </div>
            <div class="link_back">
                <a href="main.php?page=user_cancella_pg">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['link']['back']); ?>
                </a>
            </div>
        <?php }
        /*Cancella altri - SUPERUSER (Disabilita account) */
        if((gdrcd_filter('get', $_POST['op']) == 'force') && ($_SESSION['permessi'] == SUPERUSER)) {
            gdrcd_query("UPDATE personaggio SET permessi = -1 WHERE nome = '".gdrcd_filter('in', $_POST['account'])."'");
            gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_POST['account'])."','".$_SESSION['login']."', NOW(), ".DELETEPG." ,'->')");
            ?>
            <div class="warning">
                <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
            </div>
            <div class="link_back">
                <a href="main.php?page=user_cancella_pg">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['link']['back']); ?>
                </a>
            </div>
        <?php }
        /*Ripristina account*/
        if((gdrcd_filter('get', $_POST['op']) == 'get_back') && ($_SESSION['permessi'] >= MODERATOR)) {
            gdrcd_query("UPDATE personaggio SET permessi = 0 WHERE nome = '".gdrcd_filter('in', $_POST['account'])."'");
            gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_POST['account'])."','".$_SESSION['login']."', NOW(), ".DELETEPG." ,'<-')");
            ?>
            <div class="warning">
                <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
            </div>
            <div class="link_back">
                <a href="main.php?page=user_cancella_pg">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['link']['back']); ?>
                </a>
            </div>
        <?php }
        /*Visualizzazione di base*/
        if(isset($_POST['op']) === false) { ?>
            <div class="panels_box">
                <div class="form_gioco">
                    <form action="main.php?page=user_cancella_pg" method="post">
                        <div class="form_label">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['delete']['email']); ?>
                        </div>
                        <div class="form_field">
                            <input name="email" />
                        </div>
                        <div class="form_label">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['delete']['pass']); ?>
                        </div>
                        <div class="form_field">
                            <input name="new_pass" />
                        </div>
                        <div class="form_submit">
                            <input type="hidden" name="op" value="delete" />
                            <input type="submit" name="nulla" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
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
                        <form action="main.php?page=user_cancella_pg" method="post">
                            <div class="form_label">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['delete']['force']); ?>
                            </div>
                            <div class="form_field">
                                <select name="account">
                                    <option disabled selected><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['delete']['who']); ?></option>
                                    <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                        <option value="<?php echo $row['nome']; ?>"><?php echo $row['nome']; ?></option>
                                    <?php }//while
                                    gdrcd_query($result, 'free');
                                    ?>
                                </select>
                            </div>
                            <div class="form_submit">
                                <input type="hidden" name="op" value="force" />
                                <input type="submit" name="nulla" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                            </div>
                        </form>
                    </div>
                </div>
                <?php
                $query = "SELECT nome FROM personaggio WHERE permessi < 0 ORDER BY nome";
                $result = gdrcd_query($query, 'result'); ?>

                <div class="panels_box">
                    <div class="form_gioco">
                        <form action="main.php?page=user_cancella_pg" method="post">
                            <div class="form_label">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['get_back']['force']); ?>
                            </div>
                            <div class="form_field">
                                <select name="account">
                                    <option disabled selected><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['get_back']['who']); ?></option>
                                    <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                        <option value="<?php echo $row['nome']; ?>"><?php echo $row['nome']; ?></option>
                                    <?php }//while
                                    gdrcd_query($result, 'free');
                                    ?>
                                </select>
                            </div>
                            <div class="form_submit">
                                <input type="hidden" name="op" value="get_back" />
                                <input type="submit" name="nulla" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                            </div>
                        </form>
                    </div>
                </div>
                <?php
            }//if
        }//if ?>
    </div>
</div><!-- Box principale -->

