<?php
$condizione=MeteoCondizioni::getInstance()->getCondition(Filters::out($_POST['id']));
?>
<form class="form"
      action="main.php?page=gestione_meteo_condizioni" method="post">
    <div class="single_input">
        <div class="label"><?php echo  Filters::out( $MESSAGE['interface']['administration']['name_col']); ?></div>
        <input type="text" name="nome" id="nome" class="form_input" value="<?php echo  Filters::out($condizione['nome']); ?>">
    </div>
    <div class="single_input">
        <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_condition']['wind_name']); ?></div>
        <select data-placeholder="Opzioni per il vento" multiple class="chosen-select" name="vento[]" id="vento">
            <?php
            $vento= explode(",", $condizione[vento]);
            echo $class->diffselectVento($vento);
            ?>
        </select>
    </div>
    <div class="single_input">
        <div class="label"><?php echo  Filters::out($MESSAGE['interface']['sheet']['modify_form']['url_img']); ?></div>
        <input type="text" name="img" id="img" class="form_input" value="<?php echo  Filters::out($condizione['img']); ?>">
    </div>
    <div class="single_input">
        <input type="submit" name="submit" value="Salva"/>
        <input type="hidden" name="op" value="save_edit">
        <input type="hidden" name="id" value="<?php echo  Filters::out( $condizione['id']); ?>">
    </div>
</form>
