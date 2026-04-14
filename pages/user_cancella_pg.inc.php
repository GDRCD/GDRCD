<?php /*HELP: */
$row = gdrcd_query("SELECT email, pass FROM personaggio WHERE id_personaggio = '".$_SESSION['id_personaggio']."'");
$email = $row['email'];
$pass = $row['pass'];
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

            if((gdrcd_password_check(gdrcd_filter_email($_POST['email']),$email))  && (gdrcd_password_check($_POST['new_pass'], $pass))) {
                gdrcd_query("UPDATE personaggio SET permessi = -1 WHERE id_personaggio = '".$_SESSION['id_personaggio']."' ");
                $contestoLog = gdrcd_log_context_make([
                            'ip' => $_SERVER['REMOTE_ADDR'],
                        ]
                    );  
                gdrcd_log_notice(
                        'Cancella account del personaggio',
                        ['evento' => 'personaggio.cancella_account', ...$contestoLog],
                        $_SESSION['id_personaggio']
                    );
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
            gdrcd_query("UPDATE personaggio SET permessi = -1 WHERE id_personaggio = '".gdrcd_filter('in', $_POST['account'])."' AND permessi < ".SUPERUSER."");
            $nome = gdrcd_stmt_one("SELECT nome 
            FROM personaggio
            WHERE id_personaggio = ?",
            [$_POST['account']]);

            $contestoLog = gdrcd_log_context_make([
                            'ip' => $_SERVER['REMOTE_ADDR']
                        ],
                            $_POST['account'],
                            $nome['nome'],
                    );  

            // Registro l'evento sia per il personaggio interessato che per l'autore dell'azione
            gdrcd_log_notice(
                        'Disabilita account del personaggio',
                        ['evento' =>  'personaggio.disabilita_account', ...$contestoLog],
                          $_POST['account']     
                    );

            gdrcd_log_notice(
                'Disabilita account del personaggio',
                ['evento' =>  'personaggio.disabilita_account', ...$contestoLog],   
                $_SESSION['id_personaggio']
            ); 
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
            $nome=gdrcd_stmt_one("SELECT nome FROM personaggio
             WHERE id_personaggio = ?", [$_POST['account']]);
           
            

            gdrcd_query("UPDATE personaggio SET permessi = -1 WHERE id_personaggio = '".gdrcd_filter('in', $_POST['account'])."'");
            $contestoLog = gdrcd_log_context_make([
                            'ip' => $_SERVER['REMOTE_ADDR']
                        ],
                            $_POST['account'],
                            $nome['nome'],
                    );
           // Registro l'evento sia per il personaggio interessato che per l'autore dell'azione
            gdrcd_log_notice(
                        'Disabilita account del personaggio',
                        ['evento' =>  'personaggio.disabilita_account', ...$contestoLog],   
                         $_POST['account']
                    );

                     gdrcd_log_notice(
                        'Disabilita account del personaggio',
                            ['evento' =>  'personaggio.disabilita_account', ...$contestoLog],    
                         $_SESSION['id_personaggio']
                    );
            
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
            $nome=gdrcd_stmt_one("SELECT nome FROM personaggio
             WHERE id_personaggio = ?", [$_POST['account']]);
           
            
            gdrcd_query("UPDATE personaggio SET permessi = 0 WHERE id_personaggio = '".gdrcd_filter('in', $_POST['account'])."'");
            $contestoLog = gdrcd_log_context_make([
                            'ip' => $_SERVER['REMOTE_ADDR']
                        ],
                            $_POST['account'],
                            $nome['nome'],
                    );
            // Registro l'evento sia per il personaggio interessato che per l'autore dell'azione
             gdrcd_log_notice(
                        'Ripristina account del personaggio',
                        ['evento' =>  'personaggio.ripristina_account', ...$contestoLog],   
                         $_POST['account']
                    );   
            gdrcd_log_notice(
                        'Ripristina account del personaggio',
                        ['evento' =>  'personaggio.ripristina_account', ...$contestoLog],   
                         $_SESSION['id_personaggio']
                    );
            
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
                $query = ($_SESSION['permessi'] == SUPERUSER) ? "SELECT id_personaggio, nome FROM personaggio WHERE permessi != -1 ORDER BY nome" : "SELECT id_personaggio, nome FROM personaggio WHERE permessi != -1 AND permessi < ".SUPERUSER." ORDER BY nome";
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
                                        <option value="<?php echo $row['id_personaggio']; ?>"><?php echo $row['nome']; ?></option>
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
                $query = "SELECT id_personaggio, nome FROM personaggio WHERE permessi < 0 ORDER BY nome";
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
                                        <option value="<?php echo $row['id_personaggio']; ?>"><?php echo $row['nome']; ?></option>
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
