<?php

 if($_SESSION['permessi'] < MODERATOR) {
    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    return;
} 
$id = gdrcd_filter('num', $_POST['id']);
$loaded_record = gdrcd_stmt_one("SELECT * FROM araldo WHERE id_araldo=?  LIMIT 1", [$id]);
?>
<div class="panels_box">
    <form action="main.php?page=gestione_bacheche" method="post" class="form_gestione">
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['name']); ?>
        </div>
        <div class='form_field'>
            <input name="nome" value="<?php echo $loaded_record['nome']; ?>" />
        </div>
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['type']['name']); ?>
        </div>
        <div class='form_field'>
            <!-- Elenco dei tipi -->
            <select name="tipo">
                <option value="<?php echo INGIOCO; ?>"
                    <?php if($loaded_record['tipo'] == INGIOCO) {echo "selected";} ?>>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][INGIOCO]); ?>
                </option>
                <option value="<?php echo PERTUTTI; ?>"
                    <?php if($loaded_record['tipo'] == PERTUTTI) {echo "selected";} ?>>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][PERTUTTI]); ?>
                </option>
                <option value="<?php echo SOLORAZZA; ?>"
                    <?php if($loaded_record['tipo'] == SOLORAZZA) {echo "selected";} ?>>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][SOLORAZZA]); ?>
                </option>
                <option value="<?php echo SOLOGILDA; ?>"
                    <?php if($loaded_record['tipo'] == SOLOGILDA) {echo "selected";} ?>>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][SOLOGILDA]); ?>
                </option>
                <option value="<?php echo SOLOMASTERS; ?>"
                    <?php if($loaded_record['tipo'] == SOLOMASTERS) {echo "selected";} ?>>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][SOLOMASTERS]); ?>
                </option>
                <option value="<?php echo SOLOMODERATORS; ?>"
                    <?php if($loaded_record['tipo'] == SOLOMODERATORS) {echo "selected";} ?>>
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][SOLOMODERATORS]); ?>
                </option>
            </select>
        </div>
        <div class='form_info'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type']['info']); ?>
        </div>
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['owner']); ?>
        </div>
        <div class='form_field'>
            <?php /* Carico l'elenco delle mappe inserite */
            $razze = gdrcd_stmt_all("SELECT id_razza, nome_razza FROM razza");
            $gilde = gdrcd_stmt_all("SELECT id_gilda, nome FROM gilda"); ?>
            <!-- Elenco delle mappe -->
            <select name="owner">
                <!-- Opzione "Nessuna" -->
                <option value="-1"><!-- Opzione "Nessuno" -->
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['no_owner']); ?>
                </option>
                <?php
                foreach($razze as $option) { ?>
                    <option value="<?php echo gdrcd_filter('out', $option['id_razza']); ?>"
                        <?php if(($loaded_record['proprietari'] == $option['id_razza']) && ($loaded_record['tipo'] == SOLORAZZA)) {echo 'SELECTED';} ?>>
                        <?php echo gdrcd_filter('out', $option['nome_razza']); ?>
                    </option>
                <?php
                } 
                foreach($gilde as $option) { ?>
                    <option value="<?php echo gdrcd_filter('out', $option['id_gilda']); ?>"
                        <?php if(($loaded_record['proprietari'] == $option['id_gilda']) && ($loaded_record['tipo'] == SOLOGILDA)) {
                            echo 'SELECTED';
                        } ?>>
                        <?php echo gdrcd_filter('out', $option['nome']); ?>
                    </option>
                <?php
                }
                ?>
            </select>
        </div>
        <!-- bottoni -->
        <div class='form_submit'>
             <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['modify']); ?>" />
                <input type="hidden" name="id" value="<?php echo $loaded_record['id_araldo']; ?>">
                <input type="hidden" name="op" value="save_edit">
        </div>
    </form>
</div>

<div class="link_back">
    <a href="main.php?page=gestione_bacheche">Torna indietro</a>
</div>