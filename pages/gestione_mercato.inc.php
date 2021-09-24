<div class="pagina_gestione_mercato">
    <?php /*HELP: Pagina di gestione del mercato */

    /*Controllo permessi*/
    if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else {
        if(isset($_POST['op']) === true) {
            /*Se e' stato richiesto di caricare un oggetto*/
            if($_POST['op'] == 'load') {
                $loaded_item = gdrcd_query("SELECT * FROM oggetto WHERE id_oggetto=".gdrcd_filter('num', $_POST['load_item'])."");

                $characters = gdrcd_query("SELECT nome FROM personaggio ORDER BY nome", 'result');
            }
            /*Se e' stato richiesto di modificare un oggetto...*/
            if($_POST['op'] == 'update') {
                /*...modificando i campi*/
                if(isset($_POST['modifica']) === true) {
                    gdrcd_query("UPDATE oggetto SET tipo=".gdrcd_filter('in', $_POST['tipo_oggetto']).", nome='".gdrcd_filter('in', $_POST['nome_oggetto'])."', urlimg='".gdrcd_filter('in', $_POST['img_oggetto'])."', descrizione='".gdrcd_filter('in', $_POST['descrizione_oggetto'])."', costo=".gdrcd_filter('num', $_POST['costo_oggetto']).", ubicabile=".gdrcd_filter('num', $_POST['fit_in']).", attacco=".gdrcd_filter('num', $_POST['attacco_oggetto']).", difesa=".gdrcd_filter('num', $_POST['difesa_oggetto']).", cariche=".gdrcd_filter('num', $_POST['cariche_oggetto']).", bonus_car0=".gdrcd_filter('num', $_POST['car0_oggetto']).", bonus_car1=".gdrcd_filter('num', $_POST['car1_oggetto']).", bonus_car2=".gdrcd_filter('num', $_POST['car2_oggetto']).", bonus_car3=".gdrcd_filter('num', $_POST['car3_oggetto']).", bonus_car4=".gdrcd_filter('num', $_POST['car4_oggetto']).", bonus_car5=".gdrcd_filter('num', $_POST['car5_oggetto'])." WHERE id_oggetto=".gdrcd_filter('num', $_POST['id_oggetto'])."");

                    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['modified']).'</div>';
                } /*...eliminandolo*/ else {
                    if(isset($_POST['elimina']) === true) {
                        /*Risarcisco gli eventuali possessori */
                        $rec = gdrcd_query("SELECT costo FROM oggetto WHERE id_oggetto=".gdrcd_filter('num', $_POST['id_oggetto'])." LIMIT 1");

                        $refound = gdrcd_query("SELECT nome FROM clgpersonaggiooggetto WHERE id_oggetto=".gdrcd_filter('num', $_POST['id_oggetto'])."", 'result');

                        while($do_refound = gdrcd_query($refound, 'fetch')) {
                            gdrcd_query("UPDATE personaggio SET soldi = soldi + ".gdrcd_filter('num', $rec['costo'])." WHERE nome = '".gdrcd_filter_in($do_refound['nome'])."'");
                        }
                        gdrcd_query($refound, 'free');
                        /*Elimino l'oggetto*/
                        gdrcd_query("DELETE FROM oggetto WHERE id_oggetto=".gdrcd_filter('num', $_POST['id_oggetto'])." LIMIT 1");

                        gdrcd_query("DELETE FROM clgpersonaggiooggetto WHERE id_oggetto=".gdrcd_filter('num', $_POST['id_oggetto']));

                        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['modified']).'</div>';
                    }
                }
            }
            /*Se e' stato richiesto di inserire un oggetto*/
            if(gdrcd_filter('get', $_POST['op']) == 'insert') {
                if(gdrcd_filter('get', $_POST['img_oggetto']) == '') {
                    $immagine_oggetto = 'standard_oggetto.png';
                } else {
                    $immagine_oggetto = gdrcd_filter('get', $_POST['img_oggetto']);
                }
                gdrcd_query("INSERT INTO oggetto (tipo, nome, urlimg, descrizione, costo, ubicabile, attacco, difesa, cariche, bonus_car0, bonus_car1, bonus_car2, bonus_car3, bonus_car4, bonus_car5, creatore, data_inserimento) VALUES (".gdrcd_filter('in', $_POST['tipo_oggetto']).", '".gdrcd_filter('in', $_POST['nome_oggetto'])."', '".gdrcd_filter('in', $immagine_oggetto)."', '".gdrcd_filter('in', $_POST['descrizione_oggetto'])."', ".gdrcd_filter('num', $_POST['costo_oggetto']).", ".gdrcd_filter('num', $_POST['fit_in']).", ".gdrcd_filter('num', $_POST['attacco_oggetto']).", ".gdrcd_filter('num', $_POST['difesa_oggetto']).", ".gdrcd_filter('num', $_POST['cariche_oggetto']).", ".gdrcd_filter('num', $_POST['car0_oggetto']).", ".gdrcd_filter('num', $_POST['car1_oggetto']).", ".gdrcd_filter('num', $_POST['car2_oggetto']).", ".gdrcd_filter('num', $_POST['car3_oggetto']).", ".gdrcd_filter('num', $_POST['car4_oggetto']).", ".gdrcd_filter('num', $_POST['car5_oggetto']).", '".$_SESSION['login']."', NOW())");

                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['inserted']).'</div>';
            }
            /*Se e' stato richiesto di assegnare un oggetto al mercato o ad un PG*/
            if((gdrcd_filter('get', $_POST['op']) == 'assign') && (gdrcd_filter('num', $_POST['num_oggetti']) > 0)) {
                if($_POST['give_item'] == 'mercato') {
                    $result = gdrcd_query("SELECT id_oggetto FROM mercato WHERE id_oggetto = ".$_POST['id_oggetto']."", 'result');

                    if(gdrcd_query($result, 'num_rows') > 0) {
                        gdrcd_query($result, 'free');
                        $query = "UPDATE mercato SET numero = ".gdrcd_filter('num', $_POST['num_oggetti'])." WHERE id_oggetto = ".gdrcd_filter('num', $_POST['id_oggetto'])."";
                    } else {
                        $query = "INSERT INTO mercato (id_oggetto, numero) VALUES (".gdrcd_filter('num', $_POST['id_oggetto']).", ".gdrcd_filter('num', $_POST['num_oggetti']).")";
                    }
                    gdrcd_query($query);
                } else {
                    $result = gdrcd_query("SELECT id_oggetto FROM clgpersonaggiooggetto WHERE id_oggetto = ".gdrcd_filter('num', $_POST['id_oggetto'])." AND nome = '".gdrcd_filter('in', $_POST['give_item'])."'", 'result');

                    if(gdrcd_query($result, 'num_rows') > 0) {
                        gdrcd_query($result, 'free');
                        $query = "UPDATE clgpersonaggiooggetto SET numero = numero + ".gdrcd_filter('num', $_POST['num_oggetti'])." WHERE id_oggetto = ".gdrcd_filter('num', $_POST['id_oggetto'])." AND nome = '".gdrcd_filter('in', $_POST['give_item'])."'";
                    } else {
                        $query = "INSERT INTO clgpersonaggiooggetto (nome, id_oggetto, cariche, numero) VALUES ('".gdrcd_filter('in', $_POST['give_item'])."', ".gdrcd_filter('num', $_POST['id_oggetto']).", ".gdrcd_filter('num', $_POST['cariche_oggetto']).", ".gdrcd_filter('num', $_POST['num_oggetti']).")";
                    }
                    gdrcd_query($query);
                }
                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['modified']).'</div>';
            }
        }

        $elenco_oggetti = gdrcd_query("SELECT id_oggetto, nome FROM oggetto ORDER BY nome", 'result');

        $tipi_oggetto = gdrcd_query("SELECT * FROM codtipooggetto ORDER BY descrizione", 'result');
        ?>
        <div class="panels_box">
            <!-- Elenco degli oggetti esistenti -->
            <div class="panels_box">
                <form class="form_gestione" action="main.php?page=gestione_mercato" method="post">
                    <div class='form_label'>
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['load_item']); ?>
                    </div>
                    <div class='form_field'>
                        <?php if(gdrcd_query($elenco_oggetti, 'num_rows') > 0) { ?>
                            <select name="load_item">
                                <?php while($option = gdrcd_query($elenco_oggetti, 'fetch')) { ?>
                                    <option value="<?php echo $option['id_oggetto']; ?>">
                                        <?php echo gdrcd_filter('out', $option['nome']); ?>
                                    </option>
                                <?php }
                                gdrcd_query($elenco_oggetti, 'free');
                                ?>
                            </select>
                        <?php } ?>
                    </div>
                    <input type="hidden" name="op" value="load" />
                    <div class='form_submit'>
                        <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                    </div>
                </form>
            </div>
            <!-- Form di impostazione dei campi -->
            <form class="form_gestione" action="main.php?page=gestione_mercato" method="post">
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_type']); ?>
                </div>
                <div class='form_field'>
                    <?php if(gdrcd_query($tipi_oggetto, 'num_rows') > 0) { ?>
                        <select name="tipo_oggetto">
                            <?php while($option = gdrcd_query($tipi_oggetto, 'fetch')) { ?>
                                <option value="<?php echo $option['cod_tipo']; ?>" <?php if($loaded_item['tipo'] == $option['cod_tipo']) {echo 'SELECTED';} ?>>
                                    <?php echo gdrcd_filter('out', $option['descrizione']); ?>
                                </option>
                            <?php
                            }
                            gdrcd_query($tipi_oggetto, 'free');
                            ?>
                        </select>
                    <?php } ?>
                </div>
                <!-- link crea nuovo -->
                <div class="link_back">
                    <a href="main.php?page=gestione_tipi&types=items">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['link']['menage_types']); ?>
                    </a>
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_name']); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="nome_oggetto" value="<?php echo $loaded_item['nome']; ?>" />
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_image']); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="img_oggetto" value="<?php echo $loaded_item['urlimg']; ?>" />
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_info']); ?>
                </div>
                <div class='form_field'>
                    <textarea type="textbox" name="descrizione_oggetto"><?php echo $loaded_item['descrizione']; ?></textarea>
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_price']); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="costo_oggetto" value="<?php echo (int) $loaded_item['costo']; ?>" />
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_fit_in']); ?>
                </div>
                <div class='form_field'>
                    <select name="fit_in">
                        <option value="<?php echo INVENTARIO; ?>" <?php if($loaded_item['ubicabile'] == INVENTARIO) {echo 'selected';} ?>>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['fit_in']['inventory']); ?>
                        </option>
                        <option value="<?php echo ZAINO; ?>" <?php if($loaded_item['ubicabile'] == ZAINO) {echo 'selected';} ?>>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['fit_in']['bag']); ?>
                        </option>
                        <option value="<?php echo MANODX; ?>" <?php if($loaded_item['ubicabile'] == MANODX) {echo 'selected';} ?>>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['fit_in']['hand_dx']); ?>
                        </option>
                        <option value="<?php echo MANOSX; ?>" <?php if($loaded_item['ubicabile'] == MANOSX) {echo 'selected';} ?>>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['fit_in']['hand_sx']); ?>
                        </option>
                        <option value="<?php echo TORSO; ?>" <?php if($loaded_item['ubicabile'] == TORSO) {echo 'selected';} ?>>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['fit_in']['chest']); ?>
                        </option>
                        <option value="<?php echo GAMBE; ?>" <?php if($loaded_item['ubicabile'] == GAMBE) {echo 'selected';} ?>>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['fit_in']['legs']); ?>
                        </option>
                        <option value="<?php echo PIEDI; ?>" <?php if($loaded_item['ubicabile'] == PIEDI) {echo 'selected';} ?>>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['fit_in']['feet']); ?>
                        </option>
                        <option value="<?php echo TESTA; ?>" <?php if($loaded_item['ubicabile'] == TESTA) {echo 'selected';} ?>>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['fit_in']['head']); ?>
                        </option>
                        <option value="<?php echo ANELLO; ?>" <?php if($loaded_item['ubicabile'] == ANELLO) {echo 'selected';} ?>>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['fit_in']['ring']); ?>
                        </option>
                        <option value="<?php echo COLLO; ?>" <?php if($loaded_item['ubicabile'] == COLLO) {echo 'selected';} ?>>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['fit_in']['neck']); ?>
                        </option>
                    </select>
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_bonus_offensive']); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="attacco_oggetto" value="<?php echo (int) $loaded_item['attacco']; ?>" />
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_bonus_defensive']); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="difesa_oggetto" value="<?php echo (int) $loaded_item['difesa']; ?>" />
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_charges']); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="cariche_oggetto" value="<?php echo (int) $loaded_item['cariche']; ?>" />
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_bonus']).' '.gdrcd_capital_letter(gdrcd_filter('out', $PARAMETERS['names']['stats']['car0'])); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="car0_oggetto" value="<?php echo (int) $loaded_item['bonus_car0']; ?>" />
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_bonus']).' '.gdrcd_capital_letter(gdrcd_filter('out', $PARAMETERS['names']['stats']['car1'])); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="car1_oggetto" value="<?php echo (int) $loaded_item['bonus_car1']; ?>" />
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_bonus']).' '.gdrcd_capital_letter(gdrcd_filter('out', $PARAMETERS['names']['stats']['car2'])); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="car2_oggetto" value="<?php echo (int) $loaded_item['bonus_car2']; ?>" />
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_bonus']).' '.gdrcd_capital_letter(gdrcd_filter('out', $PARAMETERS['names']['stats']['car3'])); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="car3_oggetto" value="<?php echo (int) $loaded_item['bonus_car3']; ?>" />
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_bonus']).' '.gdrcd_capital_letter(gdrcd_filter('out', $PARAMETERS['names']['stats']['car4'])); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="car4_oggetto" value="<?php echo (int) $loaded_item['bonus_car4']; ?>" />
                </div>
                <div class='form_label'>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['item_bonus']).' '.gdrcd_capital_letter(gdrcd_filter('out', $PARAMETERS['names']['stats']['car5'])); ?>
                </div>
                <div class='form_field'>
                    <input type="text" name="car5_oggetto" value="<?php echo (int) $loaded_item['bonus_car5']; ?>" />
                </div>
                <?php if(isset($loaded_item) == true) { ?>
                    <input type="hidden" name="op" value="update" />
                    <input type="hidden" name="id_oggetto" value="<?php echo $loaded_item['id_oggetto']; ?>" />
                <?php } else { ?>
                    <input type="hidden" name="op" value="insert" />
                <?php } ?>
                <div class='form_submit'>
                    <input type="submit" name="modifica"
                           value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                    <?php if(isset($loaded_item) == true) { ?>
                        <input type="submit" name="elimina" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['delete']); ?>" />
                        <input type="submit" name="annulla" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['cancel']); ?>" />
                    <?php } ?>
                </div>
            </form>
            <!-- Form di assegnazione oggetti (appare solo se è stato caricato un oggetto) -->
            <?php if(isset($loaded_item) == true) { ?>
                <div class="panels_box">
                    <form class="form_gestione" action="main.php?page=gestione_mercato" method="post">
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['give_item']); ?>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['number_item']); ?>
                        </div>
                        <div class='form_field'>
                            <input type="text" name="num_oggetti" value="0" />
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['items']['destination_item']); ?>
                        </div>
                        <div class='form_field'>
                            <?php if(gdrcd_query($characters, 'num_rows') > 0) { ?>
                                <select name="give_item">
                                    <option value="mercato"><?php echo gdrcd_filter('out', $PARAMETERS['names']['market_name']); ?></option>
                                    <?php while($option = gdrcd_query($characters, 'fetch')) { ?>
                                        <option value="<?php echo $option['nome']; ?>">
                                            <?php echo gdrcd_filter('out', $option['nome']); ?>
                                        </option>
                                    <?php }
                                    gdrcd_query($characters, 'free');
                                    ?>
                                </select>
                            <?php } ?>
                        </div>
                        <input type="hidden" name="id_oggetto" value="<?php echo $loaded_item['id_oggetto']; ?>" />
                        <input type="hidden" name="cariche_oggetto" value="<?php echo $loaded_item['cariche']; ?>" />
                        <input type="hidden" name="op" value="assign" />
                        <div class='form_submit'>
                            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                        </div>
                    </form>
                </div>
            <?php } ?>
        </div>
        <?php }//else ?>
</div><!-- Pagina -->
