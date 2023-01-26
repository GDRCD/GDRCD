<div class="pagina_scheda">
    <?php
    /* HELP: E' possibile modificare la scheda agendo su scheda.css nel tema scelto,
     * oppure sostituendo il codice che segue la voce "Scheda del personaggio"
     */
    /********* CARICAMENTO PERSONAGGIO ***********/
    //Se non e' stato specificato il nome del pg
    if(isset($_REQUEST['pg']) === false) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']).'</div>';
        exit();
    }
    $query = "SELECT personaggio.*, razza.sing_m, razza.sing_f, razza.id_razza, razza.bonus_car0, razza.bonus_car1, razza.bonus_car2, razza.bonus_car3, razza.bonus_car4, razza.bonus_car5
        FROM personaggio LEFT JOIN razza ON personaggio.id_razza=razza.id_razza
        WHERE personaggio.nome = '".gdrcd_filter('in', $_REQUEST['pg'])."'";
    $personaggi = gdrcd_query($query, 'result');
    //Se il personaggio non esiste
    if(gdrcd_query($personaggi, 'num_rows') == 0) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']).'</div>';
        exit();
    }
    $personaggio = gdrcd_query($personaggi, 'fetch');
    $bonus_oggetti = gdrcd_query("SELECT SUM(oggetto.bonus_car0) AS BO0, SUM(oggetto.bonus_car1) AS BO1, SUM(oggetto.bonus_car2) AS BO2, SUM(oggetto.bonus_car3) AS BO3, SUM(oggetto.bonus_car4) AS BO4, SUM(oggetto.bonus_car5) AS BO5
            FROM oggetto JOIN clgpersonaggiooggetto ON oggetto.id_oggetto = clgpersonaggiooggetto.id_oggetto
            WHERE clgpersonaggiooggetto.nome = '".gdrcd_filter('in', $_REQUEST['pg'])."' AND clgpersonaggiooggetto.posizione > ".ZAINO."");

    //Controllo esilio, se esiliato non visualizzo la scheda
    if($personaggio['esilio'] > strftime('%Y-%m-%d')) {
        echo '<div class="warning">'.gdrcd_filter('out', $personaggio['nome']).' '.gdrcd_filter('out', $personaggio['cognome']).' '.gdrcd_filter('out', $MESSAGE['warning']['character_exiled']).' '.gdrcd_format_date($personaggio['esilio']).' ('.$personaggio['motivo_esilio'].' - '.$personaggio['autore_esilio'].')</div>';
        if($_SESSION['permessi'] >= GAMEMASTER) { ?>
            <div class="panels_box">
                <div class="form_gioco">
                    <form action="main.php?page=scheda_modifica&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']) ?>" method="post">
                        <input type="hidden" value="<?php echo strftime('%Y'); ?>" name="year" />
                        <input type="hidden" value="<?php echo strftime('%m'); ?>" name="month" />
                        <input type="hidden" value="<?php echo strftime('%d'); ?>" name="day" />
                        <input type="hidden" value="<?php gdrcd_filter('out', $MESSAGE['interface']['sheet']['modify_form']['unexile']); ?>" name="causale" />
                        <input type="hidden" value="exile" name="op" />
                        <div class="form_label">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['modify_form']['unexile']); ?>
                        </div>
                        <div class="form_submit">
                            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                        </div>
                    </form>
                </div>
            </div>
        <?php
        }
        exit();
    }

    ?>
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['page_name']); ?></h2>
    </div>
    <div class="page_body">
        <?php
        /** * Controllo e avviso che è ora di cambiare password
         * @author Blancks
         */
        if($PARAMETERS['mode']['alert_password_change'] == 'ON') {
            $six_months = 15552000;
            $ts_signup = strtotime($personaggio['data_iscrizione']);
            $ts_lastpass = (int) strtotime($personaggio['ultimo_cambiopass']);
            if($ts_lastpass + $six_months < time() && $personaggio['nome'] == $_SESSION['login']) {
                $message = ($ts_signup + $six_months < time()) ? $MESSAGE['warning']['changepass'] : $MESSAGE['warning']['changepass_signup'];
                echo '<div class="warning">'.$message.'</div>';
            }
        }
        ?>
        <div class="menu_scheda"><!-- Menu scheda -->
            <?php include ('scheda/menu.inc.php'); ?>
        </div>
        <div class="page_body">
            <div class="ritratto"><!-- nome, ritratto, ultimo ingresso -->
                <div class="titolo_box">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['box_title']['portrait']); ?>
                </div>
                <div class="ritratto_nome">
                    <span class="ritratto_nome_nome">
                        <?php echo gdrcd_filter('out', $personaggio['nome']); ?>
                    </span>
                    <span class="ritratto_nome_cognome">
                        <?php echo gdrcd_filter('out', $personaggio['cognome']); ?>
                    </span>
                </div>
                <div class="ritratto_avatar">
                    <img src="<?php echo gdrcd_filter('fullurl', $personaggio['url_img']); ?>" class="ritratto_avatar_immagine" />
                </div>
                <div class="iscritto_da">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['first_login']).' '.gdrcd_format_date($personaggio['data_iscrizione']); ?>
                </div>
                <?php if(gdrcd_format_date($personaggio['ora_entrata']) != '00/00/0000') { ?>
                    <div class="ultimo_ingresso">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['last_login']).' '.gdrcd_format_date($personaggio['ora_entrata']); ?>
                    </div>
                <?php } ?>
                <div class="ritratto_invia_messaggio"><!-- Link invia messaggio -->
                    <a href="main.php?page=messages_center&op=create&destinatario=<?=gdrcd_filter('url', $personaggio['nome']); ?>"
                       class="link_invia_messaggio">
                        <?php if(empty($PARAMETERS['names']['private_message']['image_file']) === false) { ?>
                            <img src="<?php echo $PARAMETERS['names']['private_message']['image_file']; ?>"
                                 alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['send_message_to']['send']).' '.gdrcd_filter('out', $PARAMETERS['names']['private_message']['sing']).' '.gdrcd_filter('out', $MESSAGE['interface']['sheet']['send_message_to']['to']).' '.gdrcd_filter('out', $personaggio['nome']); ?>"
                                 title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['send_message_to']['send']).' '.gdrcd_filter('out', $PARAMETERS['names']['private_message']['sing']).' '.gdrcd_filter('out', $MESSAGE['interface']['sheet']['send_message_to']['to']).' '.gdrcd_filter('out', $personaggio['nome']); ?>"
                                 class="link_messaggio_forum">
                        <?php } else {
                            echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['send_message_to']['send']).' '.gdrcd_filter('out', strtolower($PARAMETERS['names']['private_message']['sing'])).' '.gdrcd_filter('out', $MESSAGE['interface']['sheet']['send_message_to']['to']).' '.gdrcd_filter('out', $personaggio['nome']);
                        } ?>
                    </a>
                </div>
            </div>
            <!-- nome, ritratto, ultimo ingresso, abiti portati -->
            <div class="profilo"><!-- Punteggi, salute, status, classe, razza. -->
                <div class="titolo_box">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['box_title']['profile']); ?>
                </div>
                <?php if($personaggio['permessi'] > 0) { ?>
                    <div class="profilo_voce">
                        <div class="profilo_voce_label">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['profile']['role']); ?>:
                        </div>
                        <div class="profilo_voce_valore">
                            <?php
                            switch($personaggio['permessi']) {
                                case USER:
                                    $permessi_utente = '';
                                    break;
                                case GUILDMODERATOR:
                                    $permessi_utente = $PARAMETERS['names']['guild_name']['lead'];
                                    break;
                                case GAMEMASTER:
                                    $permessi_utente = $PARAMETERS['names']['master']['sing'];
                                    break;
                                case MODERATOR:
                                    $permessi_utente = $PARAMETERS['names']['moderators']['sing'];
                                    break;
                                case SUPERUSER:
                                    $permessi_utente = $PARAMETERS['names']['administrator']['sing'];
                                    break;
                            }
                            echo gdrcd_filter('out', $permessi_utente).' <img src="imgs/icons/permessi'.(int) $personaggio['permessi'].'.gif" class="profilo_img_gilda" />'; ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="profilo_voce">
                    <div class="profilo_voce_label">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['profile']['occupation']); ?>:
                    </div>
                    <div class="profilo_voce_valore">
                        <?php //carico le gilde
                        $guilds = gdrcd_query("SELECT ruolo.nome_ruolo, ruolo.gilda, ruolo.immagine, gilda.visibile, gilda.nome AS nome_gilda FROM clgpersonaggioruolo LEFT JOIN ruolo ON ruolo.id_ruolo = clgpersonaggioruolo.id_ruolo LEFT JOIN gilda ON ruolo.gilda = gilda.id_gilda WHERE clgpersonaggioruolo.personaggio = '".gdrcd_filter('in', $personaggio['nome'])."'", 'result');
                        if(gdrcd_query($guilds, 'num_rows') == 0) {
                            echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['profile']['uneployed']);
                        } else {
                            while($row_guilds = gdrcd_query($guilds, 'fetch')) {
                                if($row_guilds['gilda'] == -1) {
                                    echo '<img class="profilo_img_gilda"  src="themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/guilds/'.gdrcd_filter('out', $row_guilds['immagine']).'" alt="'.gdrcd_filter('out', $row_guilds['nome_ruolo']).'" title="'.gdrcd_filter('out', $row_guilds['nome_ruolo']).'" />';
                                } else {
                                    if(($row_guilds['visibile'] == 1) || ($_SESSION['permessi'] >= USER)) {
                                        echo '<a href="main.php?page=servizi_gilde&id_gilda='.$row_guilds['gilda'].'"><img class="profilo_img_gilda"  src="themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/guilds/'.gdrcd_filter('out', $row_guilds['immagine']).'" alt="'.gdrcd_filter('out', $row_guilds['nome_ruolo'].' - '.$row_guilds['nome_gilda']).'" title="'.gdrcd_filter('out', $row_guilds['nome_ruolo'].' - '.$row_guilds['nome_gilda']).'" /></a>';
                                    }
                                }
                            }

                        } ?>
                    </div>
                </div>
                <div class="profilo_voce">
                    <div class="profilo_voce_label">
                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['race']['sing']); ?>:
                    </div>
                    <div class="profilo_voce_valore">
                        <?php if((empty($personaggio['sing_f']) == false) || (empty($personaggio['sing_m']) == false)) {
                            echo ($personaggio['sesso'] == 'f') ? gdrcd_filter('out', $personaggio['sing_f']) : gdrcd_filter('out', $personaggio['sing_m']);
                        } else {
                            echo gdrcd_filter('out', $PARAMETERS['names']['race']['sing'].' '.$MESSAGE['interface']['sheet']['profile']['no_race']);
                        } ?>
                    </div>
                </div>
                <div class="profilo_voce">
                    <div class="profilo_voce_label">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['profile']['experience']); ?>:
                    </div>
                    <div class="profilo_voce_valore">
                        <?php echo gdrcd_filter('out', floor($personaggio['esperienza'])); ?>
                    </div>
                </div>
                 <!-- caratteristiche -->
                <div class="profilo_voce">
                    <div class="profilo_voce_label">
                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car0']); ?>:
                    </div>
                    <div class="profilo_voce_valore">
                        <?php echo gdrcd_filter('out', $personaggio['car0'] + $personaggio['bonus_car0'] + $bonus_oggetti['BO0']); ?>
                    </div>
                </div>
                <div class="profilo_voce">
                    <div class="profilo_voce_label">
                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car1']); ?>:
                    </div>
                    <div class="profilo_voce_valore">
                        <?php echo gdrcd_filter('out', $personaggio['car1'] + $personaggio['bonus_car1'] + $bonus_oggetti['BO1']); ?>
                    </div>
                </div>
                <div class="profilo_voce">
                    <div class="profilo_voce_label">
                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car2']); ?>:
                    </div>
                    <div class="profilo_voce_valore">
                        <?php echo gdrcd_filter('out', $personaggio['car2'] + $personaggio['bonus_car2'] + $bonus_oggetti['BO2']); ?>
                    </div>
                </div>
                <div class="profilo_voce">
                    <div class="profilo_voce_label">
                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car3']); ?>:
                    </div>
                    <div class="profilo_voce_valore">
                        <?php echo gdrcd_filter('out', $personaggio['car3'] + $personaggio['bonus_car3'] + $bonus_oggetti['BO3']); ?>
                    </div>
                </div>
                <div class="profilo_voce">
                    <div class="profilo_voce_label">
                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car4']); ?>:
                    </div>
                    <div class="profilo_voce_valore">
                        <?php echo gdrcd_filter('out', $personaggio['car4'] + $personaggio['bonus_car4'] + $bonus_oggetti['BO4']); ?>
                    </div>
                </div>
                <div class="profilo_voce">
                    <div class="profilo_voce_label">
                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car5']); ?>:
                    </div>
                    <div class="profilo_voce_valore">
                        <?php echo gdrcd_filter('out', $personaggio['car5'] + $personaggio['bonus_car5'] + $bonus_oggetti['BO5']); ?>
                    </div>
                </div>
                <div class="profilo_voce">
                    <div class="profilo_voce_label">
                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['hitpoints']); ?>:
                    </div>
                    <div class="profilo_voce_valore">
                        <?php echo gdrcd_filter('out', $personaggio['salute']).'/'.gdrcd_filter('out', $personaggio['salute_max']); ?>
                    </div>
                </div>
                <div class="profilo_voce">
                    <div class="profilo_voce_label">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['profile']['status']); ?>:
                    </div>
                    <div class="profilo_voce_valore">
                        <?php echo nl2br(gdrcd_filter('out', $personaggio['stato'])); ?>
                    </div>
                </div>
            </div>
            <?php //Punteggi, salute, status, classe, razza.
            if($PARAMETERS['mode']['skillsystem'] == 'ON') { //solo se è attiva la modalità skillsystem
                include ('scheda/skillsystem.inc.php');
            } ?>


        </div>

    </div><!-- Elenco abilità -->
    <?php
    /********* CHIUSURA SCHEDA **********/
    //Impedisci XSS nella musica
    $personaggio['url_media'] = gdrcd_filter('fullurl', $personaggio['url_media']);
    if($PARAMETERS['mode']['allow_audio'] == 'ON' && !$_SESSION['blocca_media'] && !empty($personaggio['url_media'])) { ?>
        <audio autoplay>
            <source src="<?php echo $personaggio['url_media']; ?>" type="<?php echo $PARAMETERS['settings']['audiotype']['.' . strtolower(end(explode('.', $personaggio['url_media'])))]; ?>">
            Your browser does not support the audio element.
        </audio>
        <!--[if IE9]>
        <embed src="<?php echo $personaggio['url_media']; ?>" autostart="true" hidden="true"/>
        <![endif]-->
    <?php } ?>
</div><!-- Pagina -->
