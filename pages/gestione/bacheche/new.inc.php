<div class="panels_box">
    <form action="main.php?page=gestione_bacheche" method="post" class="form_gestione">
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['name']); ?>
        </div>
        <div class='form_field'>
            <input name="nome" type="text" />
        </div>
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['type']['name']); ?>
        </div>
        <div class='form_field'>
            <!-- Elenco dei tipi -->
            <select name="tipo">
                <option value="<?php echo INGIOCO; ?>">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][INGIOCO]); ?>
                </option>
                <option value="<?php echo PERTUTTI; ?>">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][PERTUTTI]); ?>
                </option>
                <option value="<?php echo SOLORAZZA; ?>">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][SOLORAZZA]); ?>
                </option>
                <option value="<?php echo SOLOGILDA; ?>">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][SOLOGILDA]); ?>
                </option>
                <option value="<?php echo SOLOMASTERS; ?>">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][SOLOMASTERS]); ?>
                </option>
                <option value="<?php echo SOLOMODERATORS; ?>">
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
                    <option value="<?php echo gdrcd_filter('out', $option['id_razza']); ?>">
                        <?php echo gdrcd_filter('out', $option['nome_razza']); ?>
                    </option>
                <?php
                }
            
                foreach($gilde as $option) { ?>
                    <option value="<?php echo gdrcd_filter('out', $option['id_gilda']); ?>">
                        <?php echo gdrcd_filter('out', $option['nome']); ?>
                    </option>
                <?php
                }
                ?>
            </select>
        </div>
        <!-- bottoni -->
        <div class='form_submit'>
            <input type="hidden" name="op" value="save_new">
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
        </div>
    </form>
</div>
<div class="link_back">
    <a href="main.php?page=gestione_bacheche">Torna indietro</a>
</div>