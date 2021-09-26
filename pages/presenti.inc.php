<?php include('../ref_header.inc.php'); /*Header comune*/ ?>
    <!-- Box presenti-->
    <div class="pagina_presenti">
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['logged_users']['plur']); ?></h2>
        </div>
        <?php
        //Refresh presenza.
        if(isset($_REQUEST['disponibile']) === true) {
            $query = "UPDATE personaggio SET ultimo_refresh = NOW(), disponibile=".gdrcd_filter('num', $_REQUEST['disponibile'])."  WHERE nome = '".gdrcd_filter('in', $_SESSION['login'])."'";
        } elseif(isset($_REQUEST['invisibile']) && ($_SESSION['permessi'] >= GAMEMASTER)) {
            $query = "UPDATE personaggio SET ultimo_refresh = NOW(), is_invisible=".gdrcd_filter('num', $_REQUEST['invisibile'])."  WHERE nome = '".gdrcd_filter('in', $_SESSION['login'])."'";
        } else {
            $query = "UPDATE personaggio SET ultimo_refresh = NOW() WHERE nome = '".gdrcd_filter('in', $_SESSION['login'])."'";
        }
        gdrcd_query($query);
        echo '<div class="elenco_presenti">';

        //Carico la lista presenti (Entrati).
        $query = "SELECT personaggio.nome, personaggio.cognome, personaggio.permessi, personaggio.sesso, personaggio.id_razza, razza.sing_m, razza.sing_f, razza.icon, personaggio.disponibile, personaggio.is_invisible FROM personaggio LEFT JOIN razza ON personaggio.id_razza = razza.id_razza WHERE DATE_ADD(personaggio.ora_entrata, INTERVAL 2 MINUTE) > NOW() ORDER BY personaggio.ora_entrata, personaggio.nome";
        $result = gdrcd_query($query, 'result');

        echo '<div class="luogo">'.$MESSAGE['interface']['logged_users']['logged_in'].'</li>';

        while($record = gdrcd_query($result, 'fetch')) {
            //Stampo il PG
            echo '<div class="presente">';
            switch($record['permessi']) {
                case USER:
                    $alt_permessi = '';
                    break;
                case GUILDMODERATOR:
                    $alt_permessi = $PARAMETERS['names']['guild_name']['lead'];
                    break;
                case GAMEMASTER:
                    $alt_permessi = $PARAMETERS['names']['master']['sing'];
                    break;
                case MODERATOR:
                    $alt_permessi = $PARAMETERS['names']['moderators']['sing'];
                    break;
                case SUPERUSER:
                    $alt_permessi = $PARAMETERS['names']['administrator']['sing'];
                    break;
            }
            //Livello di accesso del PG (utente, master, admin, superuser)
            echo '<img class="presenti_ico" src="../imgs/icons/permessi'.$record['permessi'].'.gif" alt="'.gdrcd_filter('out', $alt_permessi).'" title="'.gdrcd_filter('out', $alt_permessi).'" />';
            //Icona stato di disponibilità. E' sensibile se la riga che sto stampando corrisponde all'utente loggato.
            $change_disp = ($record['disponibile'] + 1) % 3;
            if($record['nome'] == $_SESSION['login']) {
                //se c'e' stato un cambio di permessi aggiorno
                if($record['permessi'] != $_SESSION['permessi']) {
                    $_SESSION['permessi'] = $record['permessi'];
                }
                echo '<a href="presenti.inc.php?disponibile='.$change_disp.'" class="link_sheet">';
            }
            echo '<img class="presenti_ico" src="../imgs/icons/disponibile'.$record['disponibile'].'.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['availability'][$record['disponibile']]).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['availability'][$record['disponibile']]).'" />';
            if($record['nome'] == $_SESSION['login']) {
                echo '</a>';
            }
            //Icona della razza pg
            if($record['icon'] == '') {
                $record['icon'] = 'standard_razza.png';
            }
            echo '<img class="presenti_ico" src="../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/races/'.$record['icon'].'" alt="'.gdrcd_filter('out', $record['sing_'.$record['sesso']]).'" title="'.gdrcd_filter('out', $record['sing_'.$record['sesso']]).'" />';
            //Icona del genere del pg
            echo '<img class="presenti_ico" src="../imgs/icons/testamini'.$record['sesso'].'.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['gender'][$record['sesso']]).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['gender'][$record['sesso']]).'" />';
            //Nome pg e link alla sua scheda
            echo ' <a href="../main.php?page=scheda&pg='.gdrcd_filter('url', $record['nome']).'" class="link_sheet" target="_top">'.gdrcd_filter('out', $record['nome']);
            if(empty($record['cognome']) === false and 0) {
                echo ' '.gdrcd_filter('out', $record['cognome']);
            }
            echo '</a> ';
            //Comando visibile/invisibile
            if(($_SESSION['permessi'] >= GAMEMASTER) && ($record['nome'] == $_SESSION['login'])) {
                $next = ($record['is_invisible'] == 1) ? 0 : 1;

                echo '<a href="presenti.inc.php?invisibile='.$next.'"><img class="presenti_ico" src="../imgs/icons/vis'.$record['is_invisible'].'.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['invisible'][$record['is_invisible']]).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['invisible'][$record['is_invisible']]).'" /></a>';
            }
            echo '</div>';
        }//while
        gdrcd_query($result, 'free');

        //Carico la lista presenti (Usciti).
        /** * Fix della query per includere l'uso dell'orario di uscita per capire istantaneamente quando un pg fa logout
         * @author Blancks
         */
        $query = "SELECT personaggio.nome, personaggio.cognome, personaggio.permessi, personaggio.sesso, personaggio.id_razza, razza.sing_m, razza.sing_f, razza.icon, personaggio.disponibile, personaggio.is_invisible FROM personaggio LEFT JOIN razza ON personaggio.id_razza = razza.id_razza WHERE (personaggio.ora_uscita > personaggio.ora_entrata AND DATE_ADD(personaggio.ora_uscita, INTERVAL 1 MINUTE) > NOW()) OR (personaggio.ora_uscita < personaggio.ora_entrata AND DATE_ADD(personaggio.ultimo_refresh, INTERVAL 4 MINUTE) > NOW() AND DATE_ADD(personaggio.ultimo_refresh, INTERVAL 3 MINUTE) < NOW()) ORDER BY personaggio.ultimo_refresh, personaggio.nome";
        $result = gdrcd_query($query, 'result');

        echo '<div class="luogo">'.$MESSAGE['interface']['logged_users']['logged_out'].'</div>';

        while($record = gdrcd_query($result, 'fetch')) {
            //Stampo il PG
            echo '<div class="presente">';
            switch($record['permessi']) {
                case USER:
                    $alt_permessi = '';
                    break;
                case GUILDMODERATOR:
                    $alt_permessi = $PARAMETERS['names']['guild_name']['lead'];
                    break;
                case GAMEMASTER:
                    $alt_permessi = $PARAMETERS['names']['master']['sing'];
                    break;
                case MODERATOR:
                    $alt_permessi = $PARAMETERS['names']['moderators']['sing'];
                    break;
                case SUPERUSER:
                    $alt_permessi = $PARAMETERS['names']['administrator']['sing'];
                    break;
            }
            //Livello di accesso del PG (utente, master, admin, superuser)
            echo '<img class="presenti_ico" src="../imgs/icons/permessi'.$record['permessi'].'.gif" alt="'.gdrcd_filter('out', $alt_permessi).'" title="'.gdrcd_filter('out', $alt_permessi).'" />';
            //Icona stato di disponibilità. E' sensibile se la riga che sto stampando corrisponde all'utente loggato.
            $change_disp = ($record['disponibile'] + 1) % 3;
            if($record['nome'] == $_SESSION['login']) {
                //se c'e' stato un cambio di permessi aggiorno
                if($record['permessi'] != $_SESSION['permessi']) {
                    $_SESSION['permessi'] = $record['permessi'];
                }
                echo '<a href="presenti.inc.php?disponibile='.$change_disp.'" class="link_sheet">';
            }
            echo '<img class="presenti_ico" src="../imgs/icons/disponibile'.$record['disponibile'].'.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['availability'][$record['disponibile']]).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['availability'][$record['disponibile']]).'" />';
            if($record['nome'] == $_SESSION['login']) {
                echo '</a>';
            }
            //Icona della razza pg
            if($record['icon'] == '') {
                $record['icon'] = 'standard_razza.png';
            }
            echo '<img class="presenti_ico" src="../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/races/'.$record['icon'].'" alt="'.gdrcd_filter('out', $record['sing_'.$record['sesso']]).'" title="'.gdrcd_filter('out', $record['sing_'.$record['sesso']]).'" />';
            //Icona del genere del pg
            echo '<img class="presenti_ico" src="../imgs/icons/testamini'.$record['sesso'].'.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['gender'][$record['sesso']]).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['gender'][$record['sesso']]).'" />';
            //Nome pg e link alla sua scheda
            echo ' <a href="../main.php?page=scheda&pg='.gdrcd_filter('in', $record['nome']).'" class="link_sheet" target="_top">'.gdrcd_filter('out', $record['nome']);
            if(empty($record['cognome']) === false and 0) {
                echo ' '.gdrcd_filter('out', $record['cognome']);
            }
            echo '</a> ';
            //Comando visibile/invisibile
            if(($_SESSION['permessi'] >= GAMEMASTER) && ($record['nome'] == $_SESSION['login'])) {
                $next = ($record['is_invisible'] == 1) ? 0 : 1;

                echo '<a href="presenti.inc.php?invisibile='.$next.'"><img class="presenti_ico" src="../imgs/icons/vis'.$record['is_invisible'].'.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['invisible'][$record['is_invisible']]).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['invisible'][$record['is_invisible']]).'" /></a>';
            }
            echo '</div>';
        }//while
        gdrcd_query($result, 'free');

        //Carico la lista presenti (In luogo).
        /** * Fix della query per includere l'uso dell'orario di uscita per capire istantaneamente quando il pg non è più connesso
         * @author Blancks
         */
        $query = "SELECT personaggio.nome, personaggio.cognome, personaggio.permessi, personaggio.sesso, personaggio.id_razza, razza.sing_m, razza.sing_f, razza.icon, personaggio.disponibile, personaggio.is_invisible, mappa.stanza_apparente, mappa.nome as luogo FROM personaggio LEFT JOIN mappa ON personaggio.ultimo_luogo = mappa.id LEFT JOIN razza ON personaggio.id_razza = razza.id_razza WHERE (personaggio.ora_entrata > personaggio.ora_uscita AND DATE_ADD(personaggio.ultimo_refresh, INTERVAL 4 MINUTE) > NOW()) AND personaggio.ultimo_luogo = ".$_SESSION['luogo']." AND personaggio.ultima_mappa= ".$_SESSION['mappa']." ORDER BY personaggio.is_invisible, personaggio.ultimo_luogo, personaggio.nome";
        $result = gdrcd_query($query, 'result');

        $ultimo_luogo_corrente = '';
        while($record = gdrcd_query($result, 'fetch')) {
            $luogo_corrente = (empty ($record['stanza_apparente']) === true) ? $record['luogo'] : $record['stanza_apparente'];

            if(empty($luogo_corrente) === true) {
                    $luogo_corrente = ($record['mappa'] >= 0) ? $PARAMETERS['names']['maps_location'] : $PARAMETERS['names']['base_location'];
            }
            if($ultimo_luogo_corrente != $luogo_corrente) {
                $ultimo_luogo_corrente = $luogo_corrente;
                echo '<div class="luogo">'.gdrcd_filter('out', $luogo_corrente).'</li>';
            } //if

            //Stampo il PG
            if(($record['is_invisible'] == 0) || ($record['nome'] == $_SESSION['login'])) {
                echo '<div class="presente">';

                switch($record['permessi']) {
                    case USER:
                        $alt_permessi = '';
                        break;
                    case GUILDMODERATOR:
                        $alt_permessi = $PARAMETERS['names']['guild_name']['lead'];
                        break;
                    case GAMEMASTER:
                        $alt_permessi = $PARAMETERS['names']['master']['sing'];
                        break;
                    case MODERATOR:
                        $alt_permessi = $PARAMETERS['names']['moderators']['sing'];
                        break;
                    case SUPERUSER:
                        $alt_permessi = $PARAMETERS['names']['administrator']['sing'];
                        break;
                }

                //Livello di accesso del PG (utente, master, admin, superuser)
                echo '<img class="presenti_ico" src="../imgs/icons/permessi'.$record['permessi'].'.gif" alt="'.gdrcd_filter('out', $alt_permessi).'" title="'.gdrcd_filter('out', $alt_permessi).'" />';

                //Icona stato di disponibilità. E' sensibile se la riga che sto stampando corrisponde all'utente loggato.
                $change_disp = ($record['disponibile'] + 1) % 3;
                if($record['nome'] == $_SESSION['login']) {
                    //se c'e' stato un cambio di permessi aggiorno
                    if($record['permessi'] != $_SESSION['permessi']) {
                        $_SESSION['permessi'] = $record['permessi'];
                    }
                    echo '<a href="presenti.inc.php?disponibile='.$change_disp.'" class="link_sheet">';
                }
                echo '<img class="presenti_ico" src="../imgs/icons/disponibile'.$record['disponibile'].'.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['availability'][$record['disponibile']]).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['availability'][$record['disponibile']]).'" />';
                if($record['nome'] == $_SESSION['login']) {
                    echo '</a>';
                }

                //Icona della razza pg
                if($record['icon'] == '') {
                    $record['icon'] = 'standard_razza.png';
                }
                echo '<img class="presenti_ico" src="../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/races/'.$record['icon'].'" alt="'.gdrcd_filter('out', $record['sing_'.$record['sesso']]).'" title="'.gdrcd_filter('out', $record['sing_'.$record['sesso']]).'" />';

                //Icona del genere del pg
                echo '<img class="presenti_ico" src="../imgs/icons/testamini'.$record['sesso'].'.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['gender'][$record['sesso']]).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['gender'][$record['sesso']]).'" />';

                //Nome pg e link alla sua scheda
                echo ' <a href="../main.php?page=scheda&pg='.$record['nome'].'" class="link_sheet" target="_top">'.gdrcd_filter('out', $record['nome']);
                if(empty($record['cognome']) === false and 0) {
                    echo ' '.gdrcd_filter('out', $record['cognome']);
                }
                echo '</a> ';

                //Comando visibile/invisibile
                if(($_SESSION['permessi'] >= GAMEMASTER) && ($record['nome'] == $_SESSION['login'])) {
                    $next = ($record['is_invisible'] == 1) ? 0 : 1;

                    echo '<a href="presenti.inc.php?invisibile='.$next.'"><img class="presenti_ico" src="../imgs/icons/vis'.$record['is_invisible'].'.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['invisible'][$record['is_invisible']]).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['invisible'][$record['is_invisible']]).'" /></a>';
                }
                echo '</div>';
            }
        }//while
        gdrcd_query($result, 'free');

        echo '</div>';

        // Conteggio i presenti.
        $record = gdrcd_query("SELECT COUNT(*) AS numero FROM personaggio WHERE personaggio.ora_entrata > personaggio.ora_uscita AND DATE_ADD(personaggio.ultimo_refresh, INTERVAL 4 MINUTE) > NOW() AND personaggio.is_invisible = 0");

        //numero utenti presenti.
        echo '<div class="link_presenti"><a href="../main.php?page=presenti_estesi" target="_top">';
        if($record['numero'] == 1) {
            echo '<div class="page_title"><h2>'.$record['numero'].' '.gdrcd_filter('out', $PARAMETERS['names']['users_name']['sing']).' '.gdrcd_filter('out', $MESSAGE['interface']['logged_users']['sing']).'</h2></div>';
        } else {
            echo '<div class="page_title"><h2 class="presenti_title">'.$record['numero'].' '.gdrcd_filter('out', $PARAMETERS['names']['users_name']['plur']).' '.gdrcd_filter('out', $MESSAGE['interface']['logged_users']['plur']).'</h2></div>';
        }
        echo '</a></div>';
        ?>
    </div>
    <!-- Chiudura finestra del gioco -->
<?php include('../footer.inc.php');  /*Footer comune*/ ?>