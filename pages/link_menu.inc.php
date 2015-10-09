<div class="pagina_link_menu">
  <?php
    if ($PARAMETERS['mode']['gotomap_list'] == 'ON' && empty($params['no_gotomap_list'])) {
      $gotomap_list = array();
      $result = gdrcd_query("	SELECT 	mappa_click.id_click, mappa_click.nome,
									mappa.id, mappa.nome AS nome_chat, mappa.chat, mappa.pagina, mappa.id_mappa_collegata
							FROM mappa_click
							LEFT JOIN mappa ON mappa.id_mappa = mappa_click.id_click", 'result');
      if (gdrcd_query($result, 'num_rows') > 0) {
        while ($row = gdrcd_query($result, 'fetch')) {
          $gotomap_list[$row['nome'] . '|@|' . $row['id_click']][$row['id']] = array(
            'nome' => $row['nome_chat'],
            'chat' => $row['chat'],
            'pagina' => $row['pagina'],
            'mappa_collegata' => $row['id_mappa_collegata']
          );
        }

        gdrcd_query($result, 'free');

        ?>
        <select id="gotomap" onchange="self.location.href=this.value;">

          <?php foreach ($gotomap_list as $infoMap => $infoLocation) {
            $splitInfoMap = explode('|@|', $infoMap);
            ?>
            <option
              value="main.php?page=mappaclick&map_id=<?php echo $splitInfoMap[1]; ?>"<?php echo ($_SESSION['mappa'] == $splitInfoMap[1] && $_SESSION['luogo'] == -1) ? ' selected="selected"' : ''; ?>
              class="map"><?php echo $splitInfoMap[0]; ?></option>

            <?php
            if (is_array($infoLocation)) {
              foreach ($infoLocation as $idLoc => $infoLoc) {

                if (!empty($infoLoc['nome'])) {

                  if ($infoLoc['chat'] != 0) {
                    $valueLoc = 'dir=' . $idLoc . '&map_id=' . $splitInfoMap[1];

                  } else {
                    if ($infoLoc['mappa_collegata'] != 0) {
                      $valueLoc = 'page=mappaclick&map_id=' . $infoLoc['mappa_collegata'];
                    } else {
                      $valueLoc = 'page=' . $infoLoc['pagina'];
                    }
                  }
                  ?>
                  <option
                    value="main.php?<?php echo $valueLoc; ?>"<?php echo ($_SESSION['luogo'] == $idLoc && $_SESSION['luogo'] != -1) ? ' selected="selected"' : ''; ?>>&raquo; <?php echo $infoLoc['nome']; ?></option>

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


    $mkey='menu';
    if(!empty($params['menu_key'])){
      $mkey=$params['menu_key'];
    }

    if(!empty($PARAMETERS['names']['gamemenu'][$mkey])) {
      ?>
      <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $PARAMETERS['names']['gamemenu'][$mkey]); ?></h2>
      </div>
      <?php
    }
  ?>

  <div class="page_body">

<?php
$raw_counter=0;

  foreach ($PARAMETERS[$mkey] as $key => $link_menu) {

    if (!empty($link_menu['url'])) {
      $content = '';
      if (empty($link_menu['image_file'])) {
        if (!empty($link_menu['text'])) {
          $content .= '>' . gdrcd_filter('out', $link_menu['text']);
        }
      } elseif (!empty($link_menu['sprite'])) {
        $link_menu['class'] = (empty($link_menu['class']) ? 'sprite' : $link_menu['class'] . ' sprite');
        $content = 'style="background-image: url(themes/' . $PARAMETERS['themes']['current_theme'] . '/imgs/' . $mkey . '/' . $link_menu['image_file'] . ')" alt="' . gdrcd_filter('out',
            $link_menu['text']) . '" title="' . gdrcd_filter('out', $link_menu['text']) . '">';
      } else {
        if (empty($link_menu['image_file_onclick'])) {
          $img_up = $link_menu['image_file'];
          $img_down = $link_menu['image_file'];
        } else {
          $img_up = $link_menu['image_file'];
          $img_down = $link_menu['image_file_onclick'];
        }
        $content = ' onMouseOver="n' . $mkey . $raw_counter . '_over_button()" onMouseOut="n' . $mkey . $raw_counter . '_up_button()"><img src= "themes/' . $PARAMETERS['themes']['current_theme'] . '/imgs/' . $mkey . '/' . $link_menu['image_file'] . '" alt="' . gdrcd_filter('out',
            $link_menu['text']) . '" title="' . gdrcd_filter('out',
            $link_menu['text']) . '" name="n' . $raw_counter . '_buttonOne" />';
        echo '<SCRIPT LANGUAGE="JavaScript"> if (document.images) { var n' . $mkey . $raw_counter . '_button1_up = new Image(); n' . $mkey . $raw_counter . '_button1_up.src = "themes/' . $PARAMETERS['themes']['current_theme'] . '/imgs/' . $mkey . '/' . $img_up . '"; var n' . $mkey . $raw_counter . '_button1_over = new Image(); n' . $mkey . $raw_counter . '_button1_over.src = "themes/' . $PARAMETERS['themes']['current_theme'] . '/imgs/' . $mkey . '/' . $img_down . '";} function n' . $mkey . $raw_counter . '_over_button() { if (document.images) { document["n' . $mkey . $raw_counter . '_buttonOne"].src = n' . $mkey . $raw_counter . '_button1_over.src;}} function n' . $mkey . $raw_counter . '_up_button() { if (document.images) { document["n' . $mkey . $raw_counter . '_buttonOne"].src = n' . $mkey . $raw_counter . '_button1_up.src}}</SCRIPT>';
      }

      echo '<div class="link_menu"><a href="' . $link_menu['url'] . '" id="link_' . $mkey . '_' . $key . '"';
      foreach ($link_menu as $k => $v) {
        if (!in_array($k, array('text', 'image_file', 'url', 'image_file_onclick', 'sprite'))) {
          echo $k . '="' . $v . '"';
        }
      }
      echo $content . '</a></div>';
    }
    $raw_counter++;
  }

/*HELP: Il menu viene generato automaticamente attingendo dalle informazioni contenute in config.inc.php. Tutte
  le istruzioni su come usare e configurare i menù sono riportate nel file config.inc.php */ ?>
</div>
</div>