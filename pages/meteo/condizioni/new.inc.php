<form class="form"
      action="main.php?page=gestione_meteo_condizioni" method="post">
    <div class="single_input">
    <div class="label"><?php echo  gdrcd_filter('out', $MESSAGE['interface']['administration']['name_col']); ?></div>
    <input type="text" name="nome" id="nome" class="form_input">
    </div>
    <div class="single_input">
        <div class="label"><?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['meteo_condition']['wind_name']); ?></div>
        <select data-placeholder="Opzioni per il vento" multiple class="chosen-select" name="vento[]" id="vento">
            <?php
            echo $class->selectVento();
            ?>
        </select>
    </div>
    <div class="single_input">
        <div class="label"><?php echo  gdrcd_filter('out',$MESSAGE['interface']['sheet']['modify_form']['url_img']); ?></div>
        <input type="text" name="img" id="img" class="form_input">
    </div>
    <div class="single_input">
        <input type="submit" name="submit" value="Salva"/>
        <input type="hidden" name="op" value="save_new">
    </div>
</form>
