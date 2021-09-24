<?php /*HELP: */

$row = gdrcd_query("SELECT email, pass, DATE_ADD(data_iscrizione, INTERVAL 7 DAY) AS data FROM personaggio WHERE nome = '".$_SESSION['login']."'");

$email = $row['email'];
$pass = $row['pass'];
$iscriz = explode(' ', $row['data']);
$iscriz = $iscriz['0'];
?>
<div class="pagina_user_cambio_nome">
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['name']['page_name']); ?></h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <?php /*Cambio pass utenti*/
        if($_POST['op'] == 'new') {
            if(($email == gdrcd_filter_email($_POST['email'])) && ($pass == gdrcd_encript($_POST['new_pass'])) && ($iscriz >= strftime('%Y-%m-%d')) && (empty($_POST['new_name']) === false)) {
                $query = "SELECT nome FROM personaggio WHERE nome ='".gdrcd_filter('in', $_POST['new_name'])."'";
                $result = gdrcd_query($query, 'result');
                if(gdrcd_query($result, 'num_rows') > 0) { ?>
                    <div class="error">
                        <?php echo gdrcd_filter('out', $MESSAGE['error']['existing_name']); ?>
                    </div>
                    <?php
                } else {
                    gdrcd_query("UPDATE personaggio SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".$_SESSION['login']."'");
                    gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_POST['new_name'])."','".$_SESSION['login']."', NOW(), ".CHANGEDNAME." ,'".$_SESSION['login'].' -> '.gdrcd_filter('in', $_POST['new_name'])."')");
                    gdrcd_query("UPDATE log SET nome_interessato = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome_interessato = '".$_SESSION['login']."'");
                    gdrcd_query("UPDATE log SET autore = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE autore = '".$_SESSION['login']."'");
                    gdrcd_query("UPDATE messaggi SET mittente = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE mittente = '".$_SESSION['login']."'");
                    gdrcd_query("UPDATE messaggi SET destinatario = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE destinatario = '".$_SESSION['login']."'");
                    gdrcd_query("UPDATE backmessaggi SET mittente = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE mittente = '".$_SESSION['login']."'");
                    gdrcd_query("UPDATE backmessaggi SET destinatario = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE destinatario = '".$_SESSION['login']."'");
                    gdrcd_query("UPDATE clgpersonaggioabilita SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".$_SESSION['login']."'");
                    gdrcd_query("UPDATE clgpersonaggiomostrine SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".$_SESSION['login']."'");
                    gdrcd_query("UPDATE clgpersonaggiooggetto SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".$_SESSION['login']."'");
                    gdrcd_query("UPDATE clgpersonaggioruolo SET personaggio = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE personaggio = '".$_SESSION['login']."'");

                    $_SESSION['login'] = gdrcd_filter('get', $_POST['new_name']);
                    ?>
                    <div class="warning">
                        <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                    </div>
                    <?php
                }
            } else { ?>
                <div class="error">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['cant_do']); ?>
                </div>

            <?php }//else ?>
            <div class="link_back">
                <a href="main.php?page=user_cambio_nome">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['link']['back']); ?>
                </a>
            </div>
        <?php }
        /*Cambio pass admin*/
        if(gdrcd_filter('get', $_POST['op']) == 'force') {
            if(($_SESSION['permessi'] >= MODERATOR) && (empty($_POST['new_name']) === false)) {
                $query = "SELECT nome FROM personaggio WHERE nome ='".gdrcd_filter('in', $_POST['new_name'])."'";
                $result = gdrcd_query($query, 'result');
                if(gdrcd_query($result, 'num_rows') > 0) {
                    gdrcd_query($result, 'free');
                    ?>
                    <div class="error">
                        <?php echo gdrcd_filter('out', $MESSAGE['error']['existing_name']); ?>
                    </div>
                <?php } else {
                    if($_SESSION['permessi'] == SUPERUSER) {

                        gdrcd_query("UPDATE log SET nome_interessato = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome_interessato = '".gdrcd_filter('in', $_POST['account'])."'");
                        gdrcd_query("UPDATE log SET autore = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE autore = '".gdrcd_filter('in', $_POST['account'])."'");
                        gdrcd_query("UPDATE messaggi SET mittente = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE mittente = '".gdrcd_filter('in', $_POST['account'])."'");
                        gdrcd_query("UPDATE messaggi SET destinatario = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE destinatario = '".gdrcd_filter('in', $_POST['account'])."'");
                        gdrcd_query("UPDATE backmessaggi SET mittente = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE mittente = '".gdrcd_filter('in', $_POST['account'])."'");
                        gdrcd_query("UPDATE backmessaggi SET destinatario = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE destinatario = '".gdrcd_filter('in', $_POST['account'])."'");
                        gdrcd_query("UPDATE clgpersonaggioabilita SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".gdrcd_filter('in', $_POST['account'])."'");
                        gdrcd_query("UPDATE clgpersonaggiomostrine SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".gdrcd_filter('in', $_POST['account'])."'");
                        gdrcd_query("UPDATE clgpersonaggiooggetto SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".gdrcd_filter('in', $_POST['account'])."'");
                        gdrcd_query("UPDATE clgpersonaggioruolo SET personaggio = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE personaggio = '".gdrcd_filter('in', $_POST['account'])."'");
                        gdrcd_query("UPDATE personaggio SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".gdrcd_filter('in', $_POST['account'])."'");
                    } else {
                        gdrcd_query("UPDATE log SET nome_interessato = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome_interessato = '".$_POST['account']."'");
                        gdrcd_query("UPDATE log SET autore = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE autore = '".$_POST['account']."'");
                        gdrcd_query("UPDATE messaggi SET mittente = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE mittente = '".$_POST['account']."' AND permessi < ".SUPERUSER."");
                        gdrcd_query("UPDATE messaggi SET destinatario = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE destinatario = '".$_POST['account']."' AND permessi < ".SUPERUSER."");
                        gdrcd_query("UPDATE backmessaggi SET mittente = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE mittente = '".$_POST['account']."' AND permessi < ".SUPERUSER."");
                        gdrcd_query("UPDATE backmessaggi SET destinatario = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE destinatario = '".$_POST['account']."' AND permessi < ".SUPERUSER."");
                        gdrcd_query("UPDATE clgpersonaggioabilita SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".$_POST['account']."' AND permessi < ".SUPERUSER."");
                        gdrcd_query("UPDATE clgpersonaggiomostrine SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".$_POST['account']."' AND permessi < ".SUPERUSER."");
                        gdrcd_query("UPDATE clgpersonaggiooggetto SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".$_POST['account']."' AND permessi < ".SUPERUSER."");
                        gdrcd_query("UPDATE clgpersonaggioruolo SET personaggio = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE personaggio = '".$_POST['account']."' AND permessi < ".SUPERUSER."");
                        gdrcd_query("UPDATE personaggio SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE nome = '".gdrcd_filter('in', $_POST['account'])."' AND permessi < ".SUPERUSER."");
                    }

                    /*Registro l'evento */
                    gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_POST['account'])."','".$_SESSION['login']."', NOW(), ".CHANGEDNAME." ,'".gdrcd_filter('in', $_POST['account']).' -> '.gdrcd_filter('in', $_POST['new_name'])."')");
                    ?>
                    <div class="warning">
                        <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                    </div>
                    <?php
                }
            } else { ?>
                <div class="error">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['cant_do']); ?>
                </div>
            <?php }//else ?>
            <div class="link_back">
                <a href="main.php?page=user_cambio_nome">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['link']['back']); ?>
                </a>
            </div>
        <?php }
        /*Visualizzazione di base*/
        if(isset($_POST['op']) === false) {
            if($iscriz >= strftime('%Y-%m-%d')) { ?>
                <div class="panels_box">
                    <div class="form_gioco">
                        <form action="main.php?page=user_cambio_nome" method="post">
                            <div class="form_label">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['name']['email']); ?>
                            </div>
                            <div class="form_field">
                                <input name="email" />
                            </div>
                            <div class="form_label">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['name']['pass']); ?>
                            </div>
                            <div class="form_field">
                                <input name="new_pass" />
                            </div>
                            <div class="form_label">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['name']['new']); ?>
                            </div>
                            <div class="form_field">
                                <input name="new_name" />
                            </div>
                            <div class="form_submit">
                                <input type="hidden" name="op" value="new" />
                                <input type="submit" name="nulla" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['pass']['submit']['user']); ?>" />
                            </div>
                        </form>
                    </div>
                </div>
            <?php }//if
            if($_SESSION['permessi'] >= MODERATOR) {
                if($_SESSION['permessi'] == SUPERUSER) {
                    $query = "SELECT nome FROM personaggio ORDER BY nome";
                } else {
                    $query = "SELECT nome FROM personaggio WHERE permessi < ".SUPERUSER." ORDER BY nome";
                }
                $result = gdrcd_query($query, 'result'); ?>
                <div class="panels_box">
                    <div class="form_gioco">
                        <form action="main.php?page=user_cambio_nome" method="post">
                            <div class="form_label">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['name']['force']); ?>
                            </div>
                            <div class="form_field">
                                <input name="new_name" />
                            </div>
                            <div class="form_field">
                                <select name="account">
                                    <option disabled selected><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['name']['change_to']); ?></option>
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
            <?php }//if
        }//if
        ?>
    </div>
</div><!-- Box principale -->

