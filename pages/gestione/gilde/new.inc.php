<div class="panels_box">
    <form action="main.php?page=gestione_gilde" method="post" class="form_gestione">
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['name']); ?>
        </div>
        <div class='form_field'>
            <input name="nome" type="text" />
        </div>
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['type']); ?>
        </div>
        <div class='form_field'>
            <?php /* Carico l'elenco dei tipi di gilda */
            $tipi = gdrcd_stmt_all("SELECT cod_tipo, descrizione FROM codtipogilda");
            /*Se sono presenti tipi sul database*/
            if($tipi) { ?>
                <!-- Elenco dei tipi -->
                <select name="tipo">
                    <?php foreach($tipi as $option) { ?>
                        <option value="<?php echo $option['cod_tipo']; ?>">
                            <?php echo gdrcd_filter('out', $option['descrizione']); ?>
                        </option>
                    <?php }
                    
                    ?>
                </select>
            <?php
            } else { /*Altrimenti segnalo l'assenza di tipi*/
                echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['type_err']);
            } ?>
        </div>
        <div class="link_back">
            <a href="main.php?page=gestione_tipi&types=guilds">
                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['menage_types']); ?>
            </a>
        </div>

        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['image']); ?>
        </div>
        <div class='form_field'>
            <input name="immagine" type="text" />
        </div>

        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['site']); ?>
        </div>
        <div class='form_field'>
            <input name="url_sito" value="http://"  type="text" />
        </div>
        <div class='form_label'>
            Statuto
        </div>
        <div class='form_field'><textarea name="statuto"></textarea>
        </div>
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['visible']); ?>
        </div>
        <div class='form_field'>
            <input type="checkbox" name="visible" value="is_visible" />
        </div>
        <div class='form_info'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['visible_info']); ?>
        </div>
        <!-- bottoni -->
        <div class='form_submit'>
            <input type="hidden" name="op" value="save_new">
             <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['submit']['insert']); ?>" />
            
    </form>
</div>

<div class="link_back">
    <a href="main.php?page=gestione_gilde">Torna indietro</a>
</div>