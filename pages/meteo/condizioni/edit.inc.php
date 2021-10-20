<?php
$condizione=$class->getOne($_POST['id']);
?>
<form class="form"
      action="main.php?page=gestione_meteo_condizioni" method="post">
    <div class="single_input">
        <div class="label"><?php echo  gdrcd_filter('out', $MESSAGE['interface']['administration']['name_col']); ?></div>
        <input type="text" name="nome" id="nome" class="form_input" value="<?php echo  gdrcd_filter('out',$condizione['nome']); ?>">
    </div>
    <div class="single_input">
        <div class="label"><?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['meteo_condition']['wind_name']); ?></div>
        <select data-placeholder="Opzioni per il vento" multiple class="chosen-select" name="vento[]" id="vento">
            <?php
            $vento= explode(",", $condizione[vento]);
            foreach ($vento as $v){
                echo "<option selected>{$v}</option>";
            }
            $array_vento=array("Assente", "Brezza", "Brezza intensa", "Vento Forte", "Burrasca");
            $diff=array_diff($array_vento, $vento);
            foreach ($diff as $d ){
                echo "<option >{$d}</option>";
            }
            ?>
        </select>
    </div>
    <div class="single_input">
        <input type="submit" name="submit" value="Salva"/>
        <input type="hidden" name="op" value="save_edit">
        <input type="hidden" name="id" value="<?php echo  gdrcd_filter('out', $condizione['id']); ?>">
    </div>
</form>
