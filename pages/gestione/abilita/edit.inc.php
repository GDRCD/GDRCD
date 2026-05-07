<?php
 $loaded_record = gdrcd_stmt_one("SELECT * FROM abilita WHERE id_abilita= ? ", [$_REQUEST['id_record']]);
?>                    
<form action="main.php?page=gestione_abilita" method="post" class="form_gestione">
    <div class='form_label'>
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['name']); ?>
    </div>
    <div class='form_field'>
        <input name="nome" value="<?php echo $loaded_record['nome']; ?>" />
    </div>
    <div class='form_label'>
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['infos']); ?>
    </div>
    <div class='form_field'>
        <textarea name="descrizione"><?php echo $loaded_record['descrizione']; ?></textarea>
    </div>
    <div class='form_label'>
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['car']); ?>
    </div>
    <div class='form_field'>
        <select name='car'>
            <option value="0" <?php if($loaded_record['car'] == 0) { echo 'SELECTED'; } ?>>
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car0']); ?></option>
            <option value="1" <?php if($loaded_record['car'] == 1) { echo 'SELECTED'; } ?>>
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car1']); ?></option>
            <option value="2" <?php if($loaded_record['car'] == 2) { echo 'SELECTED'; } ?>>
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car2']); ?></option>
            <option value="3" <?php if($loaded_record['car'] == 3) { echo 'SELECTED'; } ?>>
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car3']); ?></option>
            <option value="4" <?php if($loaded_record['car'] == 4) { echo 'SELECTED'; } ?>>
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car4']); ?></option>
            <option value="5" <?php if($loaded_record['car'] == 5) { echo 'SELECTED'; } ?>>
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car5']); ?></option>
        </select>
    </div>
    <div class='form_label'>
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['race']); ?>
    </div>
    <div class='form_field'>
        <select name='id_razza'>
            <option value="-1" <?php if($loaded_record['id_razza'] == -1) { echo 'SELECTED'; } ?>>
                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['no_race']); ?>
            </option>
            <?php
            $result = gdrcd_query("SELECT id_razza, nome_razza FROM razza ORDER BY nome_razza", 'result');
            while($raz = gdrcd_query($result, 'fetch')) {
                ?>
                <option value="<?php echo $raz['id_razza']; ?>" <?php if($loaded_record['id_razza'] == $raz['id_razza']) { echo 'SELECTED'; } ?> >
                    <?php echo gdrcd_filter('out', $raz['nome_razza']); ?>
                </option>
            <?php
            }
            gdrcd_query($result, 'free');
            ?>
        </select>
    </div>
    <!-- bottoni -->
    <div class='form_submit'>
        <?php /* Se l'operazione è una modifica stampo i tasti modifica e annulla */
        if($operation == "edit") { ?>
            <input type="hidden" name="id_record" value="<?php echo $loaded_record['id_abilita']; ?>">
            <input type="hidden" name="op" value="modify" />
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['submit']['edit']); ?>" />
        <?php
        }  else {  /* Altrimenti il tasto inserisci */ ?>
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['submit']['insert']); ?>" />
            <input type="hidden" name="op" value="insert" />
        <?php
        } ?>
    </div>
</form>

<div class="link_back">
    <a href="main.php?page=gestione_abilita">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['link']['back']); ?>
    </a>
</div>