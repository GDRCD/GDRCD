<?php /*HELP: */
/*Aggiorno la mappa corrente del PG*/
$current_map = (isset($_GET['map_id']) === true) ? gdrcd_filter('num', $_GET['map_id']) : $_SESSION['mappa'];

$redirect_pc = 0;
/*Se ho richiesto di far partire o arrivare una mappa mobile*/
if((isset($_POST['op']) === true) && (($_POST['op'] == gdrcd_filter('out', $MESSAGE['interface']['maps']['leave'])) ||
        ($_POST['op'] == gdrcd_filter('out', $MESSAGE['interface']['maps']['arrive'])))) {
    /*Aggiorno la sua posizione*/
    gdrcd_query("UPDATE mappa_click SET posizione = ".gdrcd_filter('num', $_POST['destination'])." WHERE id_click = ".gdrcd_filter('num', $_REQUEST['map_id'])." LIMIT 1");
}
if((isset($_POST['op']) === true) && ($_POST['op'] == gdrcd_filter('out', $MESSAGE['interface']['maps']['set_meteo']))) {
    /*Aggiorno la sua posizione*/
    gdrcd_query("UPDATE mappa_click SET meteo = '".gdrcd_filter('num', $_POST['temperature'])."°C - ".gdrcd_filter('in', $_POST['climate'])."' WHERE id_click = ".gdrcd_filter('num', $_REQUEST['map_id'])." LIMIT 1");
}
/*Seleziono le voci della mappa*/
$result = gdrcd_query("SELECT mappa.id, mappa.nome, mappa.chat, mappa.x_cord, mappa.y_cord, mappa.id_mappa, mappa_click.nome AS nome_mappa, mappa_click.immagine, mappa_click.posizione, mappa_click.id_click, mappa_click.mobile FROM mappa_click LEFT JOIN mappa ON mappa.id_mappa = mappa_click.id_click WHERE mappa_click.id_click = ".$current_map."", 'result');

if(gdrcd_query($result, 'num_rows') == 0) {
    $result = gdrcd_query("SELECT id_click FROM mappa_click LIMIT 1", 'result');

    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['can_t_find_any_map']).'</div>';
} else {
    $just_one_click = gdrcd_query($result, 'fetch');
    gdrcd_query($result, 'free');

    $result = gdrcd_query("SELECT mappa.id, mappa.nome, mappa.chat, mappa.link_immagine, mappa.descrizione, mappa.link_immagine_hover, mappa.id_mappa_collegata, mappa.x_cord, mappa.y_cord, mappa.id_mappa, mappa.pagina, mappa_click.nome AS nome_mappa, mappa_click.immagine, mappa_click.posizione, mappa_click.id_click, mappa_click.mobile, mappa_click.larghezza, mappa_click.altezza FROM mappa_click LEFT JOIN mappa ON mappa.id_mappa = mappa_click.id_click WHERE mappa_click.id_click = ".$just_one_click['id_click']."", 'result');
    $redirect_pc = 1;

    /*Stampo la mappa cliccabile*/
    echo '<div class="pagina_mappaclick">';

    $echoed_title = false;
    $echo_bottom = false;
    $vicinato = 0;
    $self = 0;
    $mobile = 0;
    while($row = gdrcd_query($result, 'fetch')) {
        /*Se il personaggio si trovava in una mappa inesistente o cancellata aggiorno la sua posizione*/
        if($redirect_pc == 1) {
            gdrcd_query("UPDATE personaggio SET ultima_mappa=".gdrcd_filter('get', $row['id_click'])." WHERE nome = '".gdrcd_filter('in', $_SESSION['login'])."'");
        }
        /*Stampo il titolo, se non l'ho gia' fatto*/
        if($echoed_title === false) {
            echo '<div class="page_title">';
            echo '<h2>'.$row['nome_mappa'].'</h2>';
            echo '</div>';

            /** * Abilitazione tooltip
             * @author Blancks
             */
            if($PARAMETERS['mode']['map_tooltip'] == 'ON') {
                echo '<div id="descriptionLoc"></div>';
            }

            echo '<div class="mappaclick_map" style="background:url(\'themes/', $PARAMETERS['themes']['current_theme'], '/imgs/maps/', $row['immagine'], '\') top left no-repeat; width:', $row['larghezza'], 'px; height:', $row['altezza'], 'px;">';
            $echoed_title = true;
            $echo_bottom = true;
            $vicinato = $row['posizione'];
            $self = $row['id_click'];
            $mobile = $row['mobile'];
        }//if

        /*Stampo i link della mappa corrente*/
        /** * Bug Fix: i link sono ora posizionati in relazione alla mappa
         * Features: link a sottomappe e link immagine
         * @author Blancks
         */
        echo '<div style="position:absolute; margin:', $row['y_cord'], 'px 0 0 ', $row['x_cord'], 'px;">';

        $qstring_link = '';
        $label_link = '';

        if($row['chat'] == 1) {
            $qstring_link = 'dir='.$row['id'];
        } elseif($row['id_mappa_collegata'] != 0) {
            $qstring_link = 'page=mappaclick&map_id='.$row['id_mappa_collegata'];
        } else {
            $qstring_link = 'page='.$row['pagina'];
        }

        if(empty($row['link_immagine'])) {
            $label_link = $row['nome'];
        } else {
            $baseimg_link = 'themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/maps/';

            if( ! empty($row['link_immagine_hover'])) {
                $switchimg_link = 'onmouseover="this.src=\''.$baseimg_link.$row['link_immagine_hover'].'\';" onmouseout="this.src=\''.$baseimg_link.$row['link_immagine'].'\'"';
            } else {
                $switchimg_link = '';
            }

            $label_link = '<img src="'.$baseimg_link.$row['link_immagine'].'" alt="'.$row['nome'].'" '.$switchimg_link.' />';
        }

        $fadedesc_link = '';

        /** * Abilitazione tooltip
         * @author Blancks
         */
        if($PARAMETERS['mode']['map_tooltip'] == 'ON') {
            if( ! empty($row['descrizione'])) {
                $descrizione = trim(nl2br(gdrcd_filter('in', $row['descrizione'])));
                $descrizione = strtr($descrizione, ["\n\r" => '', "\n" => '', "\r" => '', '"' => '&quot;']);
                $fadedesc_link = 'onmouseover="show_desc(event, \''.$descrizione.'\');" onmouseout="hide_desc();"';
            }
        }
        echo '<a href="main.php?', $qstring_link, '" target="_top"', $fadedesc_link, '>', $label_link, '</a>';
        echo '</div>';
    }//while
    if($echo_bottom === true) {
        echo '</div>';
        $echo_bottom = false;
    }//if

    /* Se la mappa non è in viaggio */
    if($vicinato != INVIAGGIO) { ?>
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['maps']['more']); ?></h2>
        </div>
        <div class="mappaclick_more">
            <?php /* Carico le mappe dell'eventuale vicinato */
            $result = gdrcd_query("SELECT id_click, nome FROM mappa_click WHERE posizione = ".$vicinato." AND id_click <> ".$self." ORDER BY nome", 'result');

            if(gdrcd_query($result, 'num_rows') > 0) {
                while($record = gdrcd_query($result, 'fetch')) { ?>
                    <a href="main.php?page=mappaclick&map_id=<?php echo $record['id_click']; ?>" target="_top">
                        <?php echo gdrcd_filter('out', $record['nome']); ?>
                    </a>
                <?php }//while
                gdrcd_query($result, 'free');
            } else {
                echo gdrcd_filter('out', $MESSAGE['interface']['maps']['no_more']);
            } ?>
        </div>
        <?php /* se la mappa è in viaggio */
    } else { ?>
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['maps']['traveling']); ?></h2>
        </div>
    <?php
    }
    /*Controlli partenza mappe mobili/meteo*/
    if($_SESSION['permessi'] >= GAMEMASTER) { ?>
        <div class="form_box">
            <?php if($mobile == 1) { ?>
                <form class="form_gioco" action="main.php?page=mappaclick&map_id=<?php echo $_SESSION['mappa']; ?>" method="post">
                    <?php if($vicinato != INVIAGGIO) { ?>
                        <div class="form_submit">
                            <input type="hidden" name="destination" value="<?php echo INVIAGGIO; ?>" class="game_form_input" />
                            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['maps']['leave']); ?>" name="op" />
                        </div>
                    <?php
                    } else {
                        /*Genero la lista delle possibili destinazioni*/
                        $result = gdrcd_query("SELECT posizione, nome FROM mappa_click WHERE posizione <> -1 AND id_click <> ".$_SESSION['mappa']." ORDER BY nome", 'result');
                        /*Se esistono altre mappe*/
                        if(gdrcd_query($result, 'num_rows') > 0) { ?>
                            <div class="form_submit">
                            <select name="destination" class="game_form_selectbox">
                                <?php while($record = gdrcd_query($result, 'fetch')) { ?>
                                    <option value="<?php echo $record['posizione']; ?>">
                                        <?php echo gdrcd_filter('out', $record['nome']); ?>
                                    </option>
                                <?php
                                }
                                gdrcd_query($result, 'free');
                                ?>
                            </select>
                        <?php
                        } else { ?>
                            <input type="hidden" name="destination" value="0" class="game_form_input" />
                        <?php
                        } ?>
                        <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['maps']['arrive']); ?>" name="op" />
                        </div>
                    <?php } ?>
                </form>
            <?php
            }
            if($PARAMETERS['mode']['auto_meteo'] == 'OFF') { ?>
                <form class="form_gioco" action="main.php?page=mappaclick&map_id=<?php echo $_SESSION['mappa']; ?>" method="post">
                    <div class="form_submit">
                        <select name="temperature" class="game_form_selectbox">
                            <?php for($i = 45; $i >= -45; $i--) { ?>
                                <option value="<?php echo $i; ?>" <?php if($i == 0) { echo ' selected '; } ?>>
                                    <?php echo $i; ?>&ordm; C
                                </option>
                            <?php
                            } ?>
                        </select>
                        <select name="climate" class="game_form_selectbox">
                            <?php foreach($MESSAGE['interface']['meteo']['status'] as $climate) { ?>
                                <option value="<?php echo $climate; ?>">
                                    <?php echo $climate; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="meteo" value="meteo_change" class="game_form_input" />
                        <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['maps']['set_meteo']); ?>" name="op" />
                    </div>
                </form>
            <?php } ?>
        </div>
    <?php
    }//else
    echo '</div>';//Pagina
}//else
?>
