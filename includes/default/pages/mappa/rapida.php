<div class="page_title">
    <h2>Mappa Rapida</h2>
</div>

<div class="page_body">
    <?php

    if ( $PARAMETERS['mode']['gotomap_list'] == 'ON' && empty($params['no_gotomap_list']) ) {
        $gotomap_list = [];
        $result = gdrcd_query("	SELECT 	mappa_click.id_click, mappa_click.nome, mappa.id, mappa.nome AS nome_chat, mappa.chat, mappa.pagina, mappa.id_mappa_collegata
							FROM mappa_click
							LEFT JOIN mappa ON mappa.id_mappa = mappa_click.id_click", 'result');
        if ( gdrcd_query($result, 'num_rows') > 0 ) {
            while ( $row = gdrcd_query($result, 'fetch') ) {
                $gotomap_list[$row['nome'] . '|@|' . $row['id_click']][$row['id']] = [
                    'nome' => $row['nome_chat'],
                    'chat' => $row['chat'],
                    'pagina' => $row['pagina'],
                    'mappa_collegata' => $row['id_mappa_collegata'],
                ];
            }
            gdrcd_query($result, 'free');
            ?>
            <select id="gotomap" onchange="self.location.href=this.value;">
                <?php foreach ( $gotomap_list as $infoMap => $infoLocation ) {
                    $splitInfoMap = explode('|@|', $infoMap);
                    ?>
                    <option value="main.php?page=mappaclick&map_id=<?php echo $splitInfoMap[1]; ?>"
                            <?php echo ($_SESSION['mappa'] == $splitInfoMap[1] && $_SESSION['luogo'] == -1) ? ' selected="selected"' : ''; ?>class="map"><?php echo $splitInfoMap[0]; ?></option>
                    <?php
                    if ( is_array($infoLocation) ) {
                        foreach ( $infoLocation as $idLoc => $infoLoc ) {
                            if ( !empty($infoLoc['nome']) ) {
                                if ( $infoLoc['chat'] != 0 ) {
                                    $valueLoc = 'dir=' . $idLoc . '&map_id=' . $splitInfoMap[1];
                                } else {
                                    $valueLoc = ($infoLoc['mappa_collegata'] != 0) ? 'page=mappaclick&map_id=' . $infoLoc['mappa_collegata'] : $valueLoc = 'page=' . $infoLoc['pagina'];
                                }
                                ?>
                                <option
                                    value="main.php?<?php echo $valueLoc; ?>"<?php echo ($_SESSION['luogo'] == $idLoc && $_SESSION['luogo'] != -1) ? ' selected="selected"' : ''; ?>>
                                    &raquo; <?php echo $infoLoc['nome']; ?></option>
                                <?php
                                $valueLoc = '';
                            }
                        }
                    }
                }
                ?>
            </select>
            <?php
            unset($gotomap_list);
        }
    }
    $mkey = 'menu';
    if ( !empty($params['menu_key']) ) {
        $mkey = $params['menu_key'];
    } ?>
</div>
