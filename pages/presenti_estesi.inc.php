<!-- Box presenti-->
<div class="pagina_presenti_estesa">
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['logged_users']['page_title']); ?></h2>
    </div>
    <div class="presenti_estesi">
        <?php
        /** * Abilitazione tooltip
         * @author Blancks
         */
        if ($PARAMETERS['mode']['user_online_state'] == 'ON') {
            echo '<div id="descriptionLoc"></div>';
        }
        //Carico la lista presenti.
        /** * Fix della query per includere l'uso dell'orario di uscita per capire istantaneamente quando il pg non è più connesso
         * @author Blancks
         */
        $query = "SELECT personaggio.nome, personaggio.cognome, personaggio.permessi, personaggio.sesso, personaggio.id_razza, razza.sing_m, razza.sing_f, razza.icon, personaggio.disponibile, personaggio.online_status, personaggio.is_invisible, personaggio.ultima_mappa, personaggio.ultimo_luogo, personaggio.posizione, personaggio.ora_entrata, personaggio.ora_uscita, personaggio.ultimo_refresh, mappa.stanza_apparente, mappa.nome as luogo, mappa_click.nome as mappa FROM personaggio LEFT JOIN mappa ON personaggio.ultimo_luogo = mappa.id LEFT JOIN mappa_click ON personaggio.ultima_mappa = mappa_click.id_click LEFT JOIN razza ON personaggio.id_razza = razza.id_razza WHERE personaggio.ora_entrata > personaggio.ora_uscita AND DATE_ADD(personaggio.ultimo_refresh, INTERVAL 4 MINUTE) > NOW() ORDER BY personaggio.is_invisible, personaggio.ultima_mappa, personaggio.ultimo_luogo, personaggio.nome";
        $result = gdrcd_query($query, 'result');

        echo '<ul class="elenco_presenti">';
        $ultimo_luogo_corrente = '';
        $mappa_corrente = '';

        while ($record = gdrcd_query($result, 'fetch')) {

            //Stampo il nome del luogo
            if ($record['is_invisible'] == 1)  {
                $luogo_corrente = $MESSAGE['status_pg']['invisible'][1];
            } else {
                if ($record['mappa'] != $mappa_corrente)  {
                    $mappa_corrente = $record['mappa'];
                    echo '<li class="mappa">'.gdrcd_filter('out', $mappa_corrente).'</li>';
                }//if

                if (empty($record['stanza_apparente'])) {
                    $luogo_corrente = $record['luogo'];
                } else  {
                    $luogo_corrente = $record['stanza_apparente'];
                }//else
            }
            //Stampo il nome del luogo solo per il primo PG che vi e' posizionato
            if (empty($luogo_corrente) === true) {
                #echo 'ok';
                /*if ($record['mappa']>=0){
                    $luogo_corrente = $PARAMETERS['names']['maps_location'];
                } else {
                    $luogo_corrente = $PARAMETERS['names']['base_location'];
                }//else*/
                if ($ultimo_luogo_corrente != $luogo_corrente)  {
                    $ultimo_luogo_corrente = $luogo_corrente;
                    echo '<li class="luogo">'.gdrcd_filter('out', $luogo_corrente).'</li>';
                } //if
            } else {
                if ($ultimo_luogo_corrente != $luogo_corrente)   {
                    $ultimo_luogo_corrente = $luogo_corrente;
                    if ($record['is_invisible'] == 0)  {
                        if (($PARAMETERS['mode']['mapwise_links'] == 'OFF')) { #||($record['ultima_mappa']==$_SESSION['mappa'])
                            echo '<li class="luogo"><a href="main.php?dir='.$record['ultimo_luogo'].'&map_id='.$record['ultima_mappa'].'">'.gdrcd_filter('out', $luogo_corrente).'</a></li>';
                        } else  {
                            echo '<li class="luogo">'.gdrcd_filter('out', $luogo_corrente).'</li>';
                        }
                    } else  {
                        echo '<li class="luogo">'.gdrcd_filter('out', $luogo_corrente).'</li>';
                    }//else
                }
            }//if
            /** * Parametro di personalizzazione di uno stato online via tooltip
             * @author Blancks
             */
            $online_state = '';
            if ($PARAMETERS['mode']['user_online_state'] == 'ON' && ! empty($record['online_status']) && $record['online_status'] != null) {
                $record['online_status'] = trim(nl2br(gdrcd_filter('in', $record['online_status'])));
                $record['online_status'] = strtr($record['online_status'], ["\n\r" => '', "\n" => '', "\r" => '', '"' => '&quot;']);
                $online_state = 'onmouseover="show_desc(event, \''.$record['online_status'].'\');" onmouseout="hide_desc();""';
            }
            //Stampo il PG
            echo '<li class="presente"'.$online_state.'>';
            //Entrata, uscita PG
            //Controllo da quanto il pg e' loggato


            $activity = gdrcd_check_time($record['ora_entrata']);

            //Se e' loggato da meno di 2 minuti
            if ($activity <= 2)   {
                //Lo segnalo come appena entrato
                echo '<img class="presenti_ico" src="imgs/icons/enter.gif" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['enter']).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['enter']).'" />';
            } else  {
                 //Altrimenti e' semplicemente loggato
                    echo '<img class="presenti_ico" src="imgs/icons/blank.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['logged']).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['logged']).'" />';
                }//else
             
            switch ($record['permessi']) {
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
            }//else
            //Livello di accesso del PG (utente, master, admin, superuser)
            echo '<img class="presenti_ico" src="imgs/icons/permessi'.$record['permessi'].'.gif" alt="'.gdrcd_filter('out', $alt_permessi).'" title="'.gdrcd_filter('out', $alt_permessi).'" />';

            //Icona stato di disponibilità. E' sensibile se la riga che sto stampando corrisponde all'utente loggato.
            $change_disp = ($record['disponibile'] + 1) % 3;
            echo '<img class="presenti_ico" src="imgs/icons/disponibile'.$record['disponibile'].'.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['availability'][$record['disponibile']]).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['availability'][$record['disponibile']]).'" />';

            //Icona della razza pg
            if ($record['icon'] == '') {
                $record['icon'] = 'standard_razza.png';
            }
            echo '<img class="presenti_ico" src="themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/races/'.$record['icon'].'" alt="'.gdrcd_filter('out', $record['sing_'.$record['sesso']]).'" title="'.gdrcd_filter('out', $record['sing_'.$record['sesso']]).'" />';

            //Icona del genere del pg
            echo '<img class="presenti_ico" src="imgs/icons/testamini'.$record['sesso'].'.png" alt="'.gdrcd_filter('out', $MESSAGE['status_pg']['gender'][$record['sesso']]).'" title="'.gdrcd_filter('out', $MESSAGE['status_pg']['gender'][$record['sesso']]).'" />';

            //Nome pg e link alla sua scheda
            echo '<a href="main.php?page=messages_center&op=create&destinatario='.gdrcd_filter('url', $record['nome']).'" class="link_sheet">MP</a> ';

            //Nome pg e link alla sua scheda
            echo ' <a href="main.php?page=scheda&pg='.$record['nome'].'" class="link_sheet gender_'.$record['sesso'].'">'.gdrcd_filter('out', $record['nome']);
            if (empty($record['cognome']) === false) {
                echo ' '.gdrcd_filter('out', $record['cognome']);
            }
            echo '</a> ';
            echo '</li>';
        }//while
        echo '</ul>';
        ?>
    </div>
</div>
<!-- Chiusura finestra del gioco -->

