<form action="main.php?page=gestione_abilita" method="post" class="form_gestione">
    <div class='form_label'>
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['name']); ?>
    </div>
    <div class='form_field'>
        <input name="nome" type="text" />
    </div>
    <div class='form_label'>
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['infos']); ?>
    </div>
    <div class='form_field'>
        <textarea name="descrizione"></textarea>
    </div>
    <div class='form_label'>
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['car']); ?>
    </div>
    <div class='form_field'>
        <select name='car'>
            <option value="0">
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car0']); ?></option>
            <option value="1">
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car1']); ?></option>
            <option value="2">
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car2']); ?></option>
            <option value="3">
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car3']); ?></option>
            <option value="4">
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car4']); ?></option>
            <option value="5">
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car5']); ?></option>
        </select>
    </div>
    <div class='form_label'>
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['race']); ?>
    </div>
    <div class='form_field'>
        <select name='id_razza'>
            <option value="-1" >
                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['no_race']); ?>
            </option>
            <?php
            $result = gdrcd_stmt_all("SELECT id_razza, nome_razza FROM razza ORDER BY nome_razza");
            foreach($result as $raz) {
                ?>
                <option>
                    <?php echo gdrcd_filter('out', $raz['nome_razza']); ?>
                </option>
            <?php
            }
            ?>
        </select>
    </div>
    <!-- bottoni -->
    <div class='form_submit'>
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['submit']['insert']); ?>" />
            <input type="hidden" name="op" value="save_new" />
    </div>
</form>

<div class="link_back">
    <a href="main.php?page=gestione_abilita">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['link']['back']); ?>
    </a>
</div>