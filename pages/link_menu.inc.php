<div class="pagina_link_menu">
    <?php
    if($PARAMETERS['mode']['gotomap_list'] == 'ON' && empty($params['no_gotomap_list'])) {
        $gotomap_list = [];
        $result = gdrcd_query("	SELECT 	mappa_click.id_click, mappa_click.nome, mappa.id, mappa.nome AS nome_chat, mappa.chat, mappa.pagina, mappa.id_mappa_collegata
							FROM mappa_click
							LEFT JOIN mappa ON mappa.id_mappa = mappa_click.id_click", 'result');
        if(gdrcd_query($result, 'num_rows') > 0) {
            while($row = gdrcd_query($result, 'fetch')) {
                $gotomap_list[$row['nome'].'|@|'.$row['id_click']][$row['id']] = [
                    'nome'            => $row['nome_chat'],
                    'chat'            => $row['chat'],
                    'pagina'          => $row['pagina'],
                    'mappa_collegata' => $row['id_mappa_collegata']
                ];
            }
            gdrcd_query($result, 'free');
            ?>
            <select id="gotomap" onchange="self.location.href=this.value;">
                <?php foreach($gotomap_list as $infoMap => $infoLocation) {
                    $splitInfoMap = explode('|@|', $infoMap);
                    ?>
                    <option value="main.php?page=mappaclick&map_id=<?php echo $splitInfoMap[1]; ?>"<?php echo ($_SESSION['mappa'] == $splitInfoMap[1] && $_SESSION['luogo'] == -1) ? ' selected="selected"' : ''; ?>class="map"><?php echo $splitInfoMap[0]; ?></option>
                    <?php
                    if(is_array($infoLocation)) {
                        foreach($infoLocation as $idLoc => $infoLoc) {
                            if( ! empty($infoLoc['nome'])) {
                                if($infoLoc['chat'] != 0) {
                                    $valueLoc = 'dir='.$idLoc.'&map_id='.$splitInfoMap[1];
                                } else {
                                    $valueLoc = ($infoLoc['mappa_collegata'] != 0) ? 'page=mappaclick&map_id='.$infoLoc['mappa_collegata'] :  $valueLoc = 'page='.$infoLoc['pagina'];
                                }
                                ?>
                                <option value="main.php?<?php echo $valueLoc; ?>"<?php echo ($_SESSION['luogo'] == $idLoc && $_SESSION['luogo'] != -1) ? ' selected="selected"' : ''; ?>>&raquo; <?php echo $infoLoc['nome']; ?></option>
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
    if( ! empty($params['menu_key'])) {
        $mkey = $params['menu_key'];
    }

    if( ! empty($PARAMETERS['names']['gamemenu'][$mkey])) {
        ?>
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $PARAMETERS['names']['gamemenu'][$mkey]); ?></h2>
        </div>
        <?php
    }
    ?>
    <div class="page_body">
        <?php
        $hovers = [];
        foreach($PARAMETERS[$mkey] as $key => $link_menu) {
            if( ! empty($link_menu['url'])) {
                $content = '';
                if(empty($link_menu['image_file'])) {
                    if( ! empty($link_menu['text'])) {
                        $content .= gdrcd_filter('out', $link_menu['text']);
                    }
                } elseif( ! empty($link_menu['sprite'])) {
                    $link_menu['class'] = (empty($link_menu['class']) ? 'sprite' : $link_menu['class'].' sprite');
                    $link_menu['style'] = 'background-image: url(themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/'.$mkey.'/'.$link_menu['image_file'].')';
                    $link_menu['alt'] = gdrcd_filter('out', $link_menu['text']);
                    $link_menu['title'] = gdrcd_filter('out', $link_menu['text']);
                } else {
                    if( ! empty($link_menu['image_file_onclick'])) {
                        $hovers['link_'.$mkey.'_'.$key] = [
                            'normal' => '/themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/'.$mkey.'/'.$link_menu['image_file'],
                            'hover'  => '/themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/'.$mkey.'/'.$link_menu['image_file_onclick']
                        ];
                    }
                    $content = '<img src="/themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/'.$mkey.'/'.$link_menu['image_file'].'" alt="'.gdrcd_filter('out',
                                                                                                                                                              $link_menu['text']
                        ).'" title="'.gdrcd_filter('out', $link_menu['text']).'"  />';
                }

                echo '<div class="link_menu"><a href="'.$link_menu['url'].'" id="link_'.$mkey.'_'.$key.'">';
                foreach($link_menu as $k => $v) {
                    if( ! in_array($k, ['text', 'image_file', 'url', 'image_file_onclick', 'sprite'])) {
                        echo $k.'="'.$v.'"';
                    }
                }
                echo $content.'</a></div>';
            }
        }
        ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script type="application/javascript">
            var <?= $mkey ?>_hovers = <?= json_encode($hovers); ?>;

            $(function () {
                $('.<?= $params['class'] ?> .link_menu a').mouseenter(function (ev) {
                    var $t = $(this);
                    var id = $t.attr('id');
                    if (id in <?= $mkey ?>_hovers) {
                        ev.preventDefault();
                        $t.find('img').attr('src', <?= $mkey ?>_hovers[id]['hover']);
                    }
                })
                    .mouseleave(function (ev) {
                        var $t = $(this);
                        var id = $t.attr('id');
                        if (id in <?= $mkey ?>_hovers) {
                            ev.preventDefault();
                            $t.find('img').attr('src', <?= $mkey ?>_hovers[id]['normal']);
                        }
                    });
            });
        </script>
        <?php
        /*HELP: Il menu viene generato automaticamente attingendo dalle informazioni contenute in config.inc.php. Tutte
        le istruzioni su come usare e configurare i menù sono riportate nel file config.inc.php */ ?>
    </div>
</div>