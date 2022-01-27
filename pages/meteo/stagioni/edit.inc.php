<?php

$condizione=MeteoStagioni::getInstance()->getSeason(Filters::out($_POST['id']));
?>
<form class="form"
      action="main.php?page=gestione_meteo_stagioni" method="post">
    <div class="single_input">
        <div class="label"><?php echo Filters::out( $MESSAGE['interface']['administration']['name_col']); ?></div>
        <input type="text" name="nome" id="nome" class="form_input" value="<?php echo Filters::out($condizione['nome']); ?>">
    </div>
    <div class="single_input">
        <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_season']['min']); ?></div>
        <input type="number" name="minima" id="minima" class="form_input" value="<?php echo  Filters::out($condizione['minima']); ?>">
    </div>
    <div class="single_input">
        <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_season']['max']); ?></div>
        <input type="number" name="massima" id="massima" class="form_input" value="<?php echo Filters::out($condizione['massima']); ?>">
    </div>
    <div class="single_input">
        <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_season']['date_start']); ?></div>
        <input type="date" name="data_inizio" id="data_inizio" class="form_input" value="<?php echo Filters::out($condizione['data_inizio']); ?>">
    </div>
    <div class="single_input">
        <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_season']['sunrise']); ?></div>
        <input type="time" name="alba" id="alba" class="form_input" value="<?php echo Filters::out($condizione['alba']); ?>">
    </div>
    <div class="single_input">
        <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_season']['sunset']); ?></div>
        <input type="time" name="tramonto" id="tramonto" class="form_input" value="<?php echo Filters::out($condizione['tramonto']); ?>">
    </div>


    <div class="single_input">
        <input type="submit" name="submit" value="Salva"/>
        <input type="hidden" name="op" value="save_edit">
        <input type="hidden" name="id" value="<?php echo Filters::out($condizione['id']); ?>">
    </div>
</form>
<hr>
<form class="form"
      action="main.php?page=gestione_meteo_stagioni" method="post">
    <div class="label">Condizione meteo: </div>
    <select name="condizione" id="condizione" >
        <?php
        $all= Meteo::diffselectState(Filters::out($condizione['id']));

        while ($row = DB::query($all, 'fetch')){
            echo "<option value='{$row['id']}'>{$row['nome']}</option>";
        }
        ?>
    </select>

    <?php


    ?>
    <div class="label">Percentuale: </div>
    <input type="number" name="percentuale" id="percentuale">
    <input type="hidden" name="op" value="add_condition">
    <input type="hidden" name="id" value="<?php echo Filters::out($condizione['id']); ?>">
    <input type="submit" name="submit" value="Aggiungi condizione"/>
</form>

<div class="fake-table">
    <div class="tr header">
        <div class="td">
            <div class="titoli_elenco">Condizione</div>
        </div>
        <div class="td">
            <div class="titoli_elenco">Percentuale</div>
        </div>
        <div class="td">
            <div class="titoli_elenco">Elimina</div>
        </div>

    </div>
    <?php


    $all= Meteo::getAllState(Filters::out($condizione['id']));
    while ($row = DB::query($all, 'fetch')){

         echo "<div class='tr'>
                    <div class='td'>
                    {$row['nome']}
                    </div>
                     <div class='td'>
                    {$row['percentuale']}%
                    </div>
                     <div class='td'>";
         ?>
                     <!-- Elimina -->
                    <form action="main.php?page=gestione_meteo_stagioni"  method="post">
                        <input hidden value="delete_condition" name="op">

                        <button type="submit" name="id" onClick='return confirmSubmit()'
                                value="<?php echo Filters::out( $row['id']); ?>" class="btn-link">
                                [<?php echo Filters::out( $MESSAGE['interface']['forums']['link']['delete']); ?>]
                        </button>
                    </form>
                </div>
            </div>
   <?php
    }
    ?>
</div>