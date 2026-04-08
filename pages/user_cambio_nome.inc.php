<?php /*HELP: */

$row = gdrcd_query("SELECT email, pass, DATE_ADD(data_iscrizione, INTERVAL 7 DAY) AS data FROM personaggio WHERE id_personaggio = '".$_SESSION['id_personaggio']."'");

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
           $isEmailOk = gdrcd_password_check($_POST['email'], $email);
            $isPasswordOk = gdrcd_password_check($_POST['new_pass'], $pass);
            $isDateOk = ($iscriz >= strftime('%Y-%m-%d'));
            $isNameOk = !empty($_POST['new_name']);

 
           if ($isEmailOk && $isPasswordOk && $isDateOk && $isNameOk) {

                $query = "SELECT nome FROM personaggio WHERE nome ='".gdrcd_filter('in', $_POST['new_name'])."'";
                $result = gdrcd_query($query, 'result');
                if(gdrcd_query($result, 'num_rows') > 0) { ?>
                    <div class="error">
                        <?php echo gdrcd_filter('out', $MESSAGE['error']['existing_name']); ?>
                    </div>
                    <?php
                } else {
                    $nome = gdrcd_query("SELECT nome FROM personaggio WHERE id_personaggio = '" . gdrcd_filter('in', $_SESSION['id_personaggio']) . "'");

                    gdrcd_query("UPDATE personaggio SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE id_personaggio = '".$_SESSION['id_personaggio']."'");
                    
                    gdrcd_log_notice(
                        'Cambio nome del personaggio',
                        [
                            'evento' => 'personaggio.cambio_nome',
                            'nome_precedente' => $_SESSION['login'],
                            'nome_nuovo' => $_POST['new_name'],
                            'origine' => 'gestione_personaggio'
                        ],
                         $_SESSION['id_personaggio']
                    );
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
                $query = "SELECT nome FROM personaggio WHERE id_personaggio ='".gdrcd_filter('in', $_POST['new_name'])."'";
                $result = gdrcd_query($query, 'result');
                if(gdrcd_query($result, 'num_rows') > 0) {
                    gdrcd_query($result, 'free');
                    ?>
                    <div class="error">
                        <?php echo gdrcd_filter('out', $MESSAGE['error']['existing_name']); ?>
                    </div>
                <?php } else {
                    if($_SESSION['permessi'] == SUPERUSER) {
                        $nome = gdrcd_query("SELECT nome FROM personaggio WHERE id_personaggio = '" . gdrcd_filter('in', $_POST['account']) . "'");

 
                        gdrcd_query("UPDATE personaggio SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE id_personaggio = '".gdrcd_filter('in', $_POST['account'])."'");
                    } else {
                       
                       gdrcd_query("UPDATE personaggio SET nome = '".gdrcd_filter('in', $_POST['new_name'])."' WHERE id_personaggio = '".gdrcd_filter('in', $_POST['account'])."' AND permessi < ".SUPERUSER."");
                    }

                    /*Registro l'evento */
                     

                    gdrcd_log_notice(
                        'Cambio nome del personaggio',
                        [
                            'evento' => 'personaggio.cambio_nome',
                            'nome_precedente' => $nome['nome'],
                            'nome_nuovo' => $_POST['new_name'],
                            'eseguito_da' => $_SESSION['login'],
                            'origine' => 'gestione_personaggio'
                        ],
                         $_POST['account']
                    );
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
                                <input name="new_pass" type="password" />
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
                    $query = "SELECT id_personaggio, nome FROM personaggio ORDER BY nome";
                } else {
                    $query = "SELECT id_personaggio, nome FROM personaggio WHERE permessi < ".SUPERUSER." ORDER BY nome";
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
                                        <option value="<?php echo $row['id_personaggio']; ?>"><?php echo $row['nome']; ?></option>
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
