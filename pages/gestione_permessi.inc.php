<div class="pagina_gestione_permessi">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
        <!-- Titolo della pagina -->
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['roles']['page_name']); ?></h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php /*Modifica di un record*/
            if((gdrcd_filter('', $_POST['op']) == $MESSAGE['interface']['administration']['roles']['submit']['edit']) || (gdrcd_filter('', $_POST['op']) == $MESSAGE['interface']['administration']['roles']['submit']['new'])) {
                /*Eseguo l'aggiornamento*/
                gdrcd_query("UPDATE personaggio SET permessi = ".gdrcd_filter('num', $_POST['permessi'])." WHERE nome = '".gdrcd_filter('in', $_POST['nome'])."' LIMIT 1");

                switch(gdrcd_filter('num', $_POST['permessi'])) {
                    case USER:
                        $newrole = gdrcd_filter('out', $PARAMETERS['names']['users_name']['sing']);
                        break;
                    case GUILDMODERATOR:
                        $newrole = gdrcd_filter('out', $PARAMETERS['names']['guild_name']['lead']);
                        break;
                    case GAMEMASTER:
                        $newrole = gdrcd_filter('out', $PARAMETERS['names']['master']['sing']);
                        break;
                    case MODERATOR:
                        $newrole = gdrcd_filter('out', $PARAMETERS['names']['moderators']['sing']);
                        break;
                    case SUPERUSER:
                        $newrole = gdrcd_filter('out', $PARAMETERS['names']['administrator']['sing']);
                        break;
                }
                /*Registro l'operazione*/
                gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento, descrizione_evento) VALUES ('".gdrcd_filter('in', $_POST['nome'])."','".$_SESSION['login']."', NOW(), '".CHANGEDROLE."', '->".$newrole."')");

                /*Avviso l'utente*/
                gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('".$_SESSION['login']."', '".gdrcd_filter('in', $_POST['nome'])."', NOW(), '".gdrcd_filter('in', $MESSAGE['interface']['administration']['roles']['message_body'][0].$newrole.$MESSAGE['interface']['administration']['roles']['message_body'][1])."')");

                ?>
                <!-- Conferma -->
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_permessi">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['roles']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Form di inserimento/modifica*/
            if(isset($_POST['op']) === false) {
                $result = gdrcd_query("SELECT nome, permessi FROM personaggio WHERE permessi > ".USER." ORDER BY permessi DESC", 'result'); ?>
                <!-- Form di abilitazione -->
                <div class="panels_box">
                    <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                        <form action="main.php?page=gestione_permessi"
                              method="post"
                              class="form_gestione">

                            <div class='form_label'>
                                <?php echo $row['nome']; ?>
                            </div>

                            <div class='form_field'>

                                <select name="permessi" ?>
                                    <option value="<?php echo USER; ?>"
                                        <?php if($row['permessi'] == USER) {
                                            echo 'SELECTED';
                                        } ?> />
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['users_name']['sing']); ?>
                                    </option>
                                    <option value="<?php echo GUILDMODERATOR; ?>"
                                        <?php if($row['permessi'] == GUILDMODERATOR) {echo 'SELECTED';} ?> />
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['guild_name']['lead']); ?>
                                    </option>
                                    <option value="<?php echo GAMEMASTER; ?>"
                                        <?php if($row['permessi'] == GAMEMASTER) { echo 'SELECTED';} ?> />
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['master']['sing']); ?>
                                    </option>
                                    <?php if($_SESSION['permessi'] > MODERATOR) { ?>
                                        <option value="<?php echo MODERATOR; ?>"
                                            <?php if($row['permessi'] == MODERATOR) {echo 'SELECTED';} ?> />
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['moderators']['sing']); ?>
                                        </option>
                                        <option value="<?php echo SUPERUSER; ?>"
                                            <?php if($row['permessi'] == SUPERUSER) {echo 'SELECTED';} ?> />
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['administrator']['sing']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <!-- bottoni -->
                            <div class='form_submit'>
                                <input type="hidden" name="nome" value="<?php echo $row['nome']; ?>">
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['roles']['submit']['edit']); ?>" name="op" />
                            </div>
                        </form>
                    <?php }//while
                    gdrcd_query($result, 'free');
                    //Nominativi utente
                    $result = gdrcd_query("SELECT nome FROM personaggio WHERE permessi > ".DELETED, 'result');
                    ?>
                    <form action="main.php?page=gestione_permessi" method="post" class="form_gestione">
                        <div class='form_field'>
                            <select name="nome">
                                <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                    <option value="<?php echo gdrcd_filter('out', $row['nome']); ?>">
                                        <?php echo gdrcd_filter('out', $row['nome']); ?>
                                    </option>
                                <?php }//while
                                gdrcd_query($result, 'free');
                                ?>
                            </select>
                            <select name="permessi" ?>
                                <option value="<?php echo USER; ?>" />
                                <?php echo gdrcd_filter('out', $PARAMETERS['names']['users_name']['sing']); ?>
                                </option>
                                <option value="<?php echo GUILDMODERATOR; ?>" />
                                <?php echo gdrcd_filter('out', $PARAMETERS['names']['guild_name']['lead']); ?>
                                </option>
                                <option value="<?php echo GAMEMASTER; ?>" />
                                <?php echo gdrcd_filter('out', $PARAMETERS['names']['master']['sing']); ?>
                                </option>
                                <?php if($_SESSION['permessi'] > MODERATOR) { ?>
                                    <option value="<?php echo MODERATOR; ?>" />
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['moderators']['sing']); ?>
                                    </option>
                                    <option value="<?php echo SUPERUSER; ?>" />
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['administrator']['sing']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <!-- bottoni -->
                        <div class='form_submit'>
                            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['roles']['submit']['new']); ?>" name="op" />
                        </div>
                    </form>
                </div>
            <?php } //if ?>
        </div><!-- page_body -->
    <?php } //else ?>
</div><!-- pagina -->