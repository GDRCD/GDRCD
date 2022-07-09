<div class="pagina_schedam_odifica">
    <?php /*HELP: */

    if (isset($_REQUEST['pg']) === false)
    {
        echo gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']);
    } else
    {
    if ($_SESSION['permessi'] < MODERATOR)
    {
        echo gdrcd_filter('out', $MESSAGE['error']['access_denied']);
    } else
    {
    if ($_POST['op'] == 'modify')
    {
        gdrcd_query(
            "UPDATE personaggio 
                    SET affetti = '" . gdrcd_filter('in', $_POST['modifica_affetti']) . "', 
                        descrizione = '" . gdrcd_filter('in', $_POST['modifica_background']) . "', 
                        url_media = '" . gdrcd_filter('in', gdrcd_filter('fullurl', $_POST['modifica_url_media'])) . "', 
                        url_img = '" . gdrcd_filter('in', gdrcd_filter('fullurl', $_POST['modifica_url_img'])) . "', 
                        car0 = " . gdrcd_filter('num', $_POST['car0']) . ", 
                        car1 = " . gdrcd_filter('num', $_POST['car1']) . ", 
                        car2 = " . gdrcd_filter('num', $_POST['car2']) . ", 
                        car3 = " . gdrcd_filter('num', $_POST['car3']) . ", 
                        car4 = " . gdrcd_filter('num', $_POST['car4']) . ",  
                        car5 = " . gdrcd_filter('num', $_POST['car5']) . ", 
                        sesso = '" . gdrcd_filter('in', $_POST['modifica_sesso']) . "', 
                        id_razza = " . gdrcd_filter('num', $_POST['modifica_razza']) . ", 
                        banca=" . gdrcd_filter('num', $_POST['modifica_banca']) . ", 
                        salute_max=" . gdrcd_filter('num', $_POST['modifica_salute_max']) . " 
                    WHERE   nome = '" . gdrcd_filter('in', $_REQUEST['pg']) . "' 
                        AND permessi <= " . $_SESSION['permessi']);
        echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['modified']) . '</div>';
    } else
    {
        /*Carico le informazioni del PG*/

        $record = gdrcd_query("SELECT sesso, id_razza, descrizione, affetti, url_img, url_media, car0, car1, car2, car3, car4, car5, salute_max, banca  FROM personaggio WHERE nome='" . gdrcd_filter('in',
                $_REQUEST['pg']) . "'");
    }
    ?>

    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['page_name']); ?></h2>
    </div>

    <div class="page_body">
        <?php if (isset($_POST['op']) === false)
        { ?>
            <div class="panels_box">
            <?php
            if ($_SESSION['permessi'] >= MODERATOR)
            {
                ?>
                <div class="form_gioco">
                    <!-- Form utente modifica -->
                    <form action="main.php?page=scheda_gst" method="post">

                        <div class='form_label'>
                            <?php echo gdrcd_filter('out',
                                $MESSAGE['interface']['sheet']['modify_form']['admin']['gender']); ?>
                        </div>
                        <div class='form_field'>
                            <select name="modifica_sesso">
                                <option value="m" <?php if ($record['sesso'] == 'm')
                                {
                                    echo 'selected';
                                } ?> />
                                m</option>
                                <option value="f" <?php if ($record['sesso'] == 'f')
                                {
                                    echo 'selected';
                                } ?> />
                                f</option>
                            </select>
                        </div>


                        <?php $query = "SELECT id_razza, nome_razza FROM razza ORDER BY nome_razza";
                        $razza_r = gdrcd_query($query, 'result'); ?>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['race']['sing']); ?>
                        </div>
                        <div class='form_field'>
                            <select name="modifica_razza">
                                <?php while($razza_row=gdrcd_query($razza_r, 'fetch')){ ?>
	   <option value="<?php echo $razza_row['id_razza']; ?>" <?php if($razza_row['id_razza']==$record['id_razza']){echo 'selected';} ?> /><?php echo $razza_row['nome_razza']; ?></option>
       <?php }
                                gdrcd_query($razza_r, 'free');

                                ?>
                            </select>
                        </div>


                        <div class='form_label'>
                            <?php echo gdrcd_filter('out',
                                $MESSAGE['interface']['sheet']['modify_form']['admin']['url_img']); ?>
                        </div>
                        <div class='form_field'>
                            <input type="text" name="modifica_url_img"
                                   value="<?php echo gdrcd_filter('out', $record['url_img']); ?>" class="form_input"/>
                        </div>

                        <div class='form_label'>
                            <?php echo gdrcd_filter('out',
                                $MESSAGE['interface']['sheet']['modify_form']['admin']['background']); ?>
                        </div>
                        <div class='form_field'>
                            <textarea type="textbox" name="modifica_background"
                                      class="form_textarea"><?php echo gdrcd_filter('out',
                                    $record['descrizione']); ?></textarea>
                        </div>
                        <div class="form_info">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
                        </div>

                        <div class='form_label'>
                            <?php echo gdrcd_filter('out',
                                $MESSAGE['interface']['sheet']['modify_form']['admin']['relationships']); ?>
                        </div>
                        <div class='form_field'>
                            <textarea type="textbox" name="modifica_affetti"
                                      class="form_textarea"><?php echo gdrcd_filter('out',
                                    $record['affetti']); ?></textarea>
                        </div>
                        <div class="form_info">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
                        </div>

                        <div class='form_label'>
                            <?php echo gdrcd_filter('out',
                                $MESSAGE['interface']['sheet']['modify_form']['admin']['url_media']); ?>
                        </div>
                        <div class='form_field'>
                            <input type="text" name="modifica_url_media"
                                   value="<?php echo gdrcd_filter('out', $record['url_media']); ?>" class="form_input"/>
                        </div>

                        <div class='form_label'>
                            <?php echo gdrcd_filter('out',
                                $MESSAGE['interface']['sheet']['modify_form']['admin']['bank']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="modifica_banca" value="<?php echo $record['banca']; ?>" class="form_input"/>
                        </div>

                        <div class='form_label'>
                            <?php echo gdrcd_filter('out',
                                $MESSAGE['interface']['sheet']['modify_form']['admin']['max_hp']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="modifica_salute_max" value="<?php echo $record['salute_max']; ?>"
                                   class="form_input"/>
                        </div>

                        <!-- Caratteristiche -->
                        <div class="form_label">
                            <?php echo gdrcd_filter('out', $MESSAGE['register']['fields']['stats']); ?>
                        </div>
                        <div class="form_field">
                            <table>
                                <tr>
                                    <td>
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car0']); ?><br/>
                                        <select name="car0">
                                            <?php for ($i = 1; $i <= $PARAMETERS['settings']['cars_cap']; $i++)
                                            { ?>
                                                <option value="<?php echo $i; ?>" <?php if ($record['car0'] == $i)
                                                {
                                                    echo 'SELECTED';
                                                } ?> >
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car1']); ?><br/>
                                        <select name="car1">
                                            <?php for ($i = 1; $i <= $PARAMETERS['settings']['cars_cap']; $i++)
                                            { ?>
                                                <option value="<?php echo $i; ?>" <?php if ($record['car1'] == $i)
                                                {
                                                    echo 'SELECTED';
                                                } ?> >
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car2']); ?><br/>
                                        <select name="car2">
                                            <?php for ($i = 1; $i <= $PARAMETERS['settings']['cars_cap']; $i++)
                                            { ?>
                                                <option value="<?php echo $i; ?>" <?php if ($record['car2'] == $i)
                                                {
                                                    echo 'SELECTED';
                                                } ?> >
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car3']); ?><br/>
                                        <select name="car3">
                                            <?php for ($i = 1; $i <= $PARAMETERS['settings']['cars_cap']; $i++)
                                            { ?>
                                                <option value="<?php echo $i; ?>" <?php if ($record['car3'] == $i)
                                                {
                                                    echo 'SELECTED';
                                                } ?> >
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car4']); ?><br/>
                                        <select name="car4">
                                            <?php for ($i = 1; $i <= $PARAMETERS['settings']['cars_cap']; $i++)
                                            { ?>
                                                <option value="<?php echo $i; ?>" <?php if ($record['car4'] == $i)
                                                {
                                                    echo 'SELECTED';
                                                } ?> >
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car5']); ?><br/>
                                        <select name="car5">
                                            <?php for ($i = 1; $i <= $PARAMETERS['settings']['cars_cap']; $i++)
                                            { ?>
                                                <option value="<?php echo $i; ?>" <?php if ($record['car5'] == $i)
                                                {
                                                    echo 'SELECTED';
                                                } ?> >
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                <tr>
                            </table>
                        </div>


                        <input type="hidden" name="op" value="modify"/>
                        <input type="hidden"
                               value="<?php echo gdrcd_filter('get', $_REQUEST['pg']); ?>"
                               name="pg"/>

                        <div class='form_submit'>
                            <input type="submit" value="<?php echo $MESSAGE['interface']['forms']['submit']; ?>"
                                   class="form_submit"/>
                        </div>

                    </form>
                </div>

                </div>

                <?php
            }//if
        }//if
        }
        }//else?>


    </div>
    <!-- Link a piè di pagina -->
    <div class="link_back">
        <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',
            $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['link']['back']); ?></a>
    </div>

</div><!-- pagina -->