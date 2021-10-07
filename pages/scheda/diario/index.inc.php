
    <?php /*Controllo che il diario sia del pg loggato*/
    if($_REQUEST['pg'] == $_SESSION['login']) { ?>

        <form action="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']);?>" method="post">
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['new']); ?>">


            <input type="hidden"  name="op" value="new"/>
        </form>


    <?php } ?>
    <div class="panels_box">
        <table>
            <tr>
                <td class="casella_titolo">
                    <div class="titoli_elenco">
                        <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['diary']['date']); ?>
                    </div>
                </td>
                <td class="casella_titolo">
                    <div class="titoli_elenco">
                        <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['diary']['title']); ?>
                    </div>
                </td>
                <?php
                if($_REQUEST['pg'] == $_SESSION['login'] || $_SESSION['permessi'] >= MODERATOR) { ?>
                <td class="casella_titolo">
                    <div class="titoli_elenco">
                        <?php echo $MESSAGE['interface']['forums']['link']['edit']; ?>
                    </div>
                </td>
                <td class="casella_titolo">
                    <div class="titoli_elenco">
                        <?php echo $MESSAGE['interface']['forums']['link']['delete']; ?>
                    </div>
                </td>
                <?php
                }
                ?></tr>

            <?php
            $query="SELECT id, data, titolo, testo FROM diario WHERE personaggio='".gdrcd_filter('url',
                $_REQUEST['pg'])."' ORDER BY data DESC";

            $result = gdrcd_query($query, 'result');

           while ($row = gdrcd_query($result, 'fetch')){?>
               <tr>
                   <td class="casella_elemento"><?php echo gdrcd_format_date( $row['data']); ?></td>
                   <td class="casella_elemento">
                       <form action="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']);?>" method="post">
                           <input hidden value="view" name="op">
                           <button type="submit" name="id" value="<?php echo gdrcd_filter('out', $row['id']); ?>" class="btn-link"><?php echo gdrcd_filter('out', $row['titolo']); ?></button>
                       </form>


                       </a></td>
                   <?php
                   if($_REQUEST['pg'] == $_SESSION['login'] || $_SESSION['permessi'] >= MODERATOR) { ?>
                   <td class="casella_elemento">
                       <form action="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']);?>" method="post">
                           <input hidden value="edit" name="op">
                           <button type="submit" name="id" value="<?php echo gdrcd_filter('out', $row['id']); ?>" class="btn-link">[<?php echo $MESSAGE['interface']['forums']['link']['edit']; ?>]</button>
                       </form>
                   </td>
                   <td class="casella_elemento">
                       <form action="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']);?>" method="post">
                           <input hidden value="delete" name="op">
                           <button type="submit" name="id" value="<?php echo gdrcd_filter('out', $row['id']); ?>" class="btn-link">[<?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['delete']); ?>]</button>
                       </form>
                   </td>

                   <?php
                   }
                   ?>
               </tr>
            <?php
           }
            ?>

        </table>
    </div>

<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',
        $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
            $MESSAGE['interface']['sheet']['link']['back']); ?></a>
</div>