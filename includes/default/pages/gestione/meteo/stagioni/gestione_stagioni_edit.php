<?php

$id = Filters::int($_REQUEST['id']);
$stagioni = MeteoStagioni::getInstance();
$stagione = $stagioni->getSeason($id);

?>


<div class="new_season">
    <form class="form"
          action="main.php?page=gestione_meteo_stagioni" method="post">
        <div class="single_input">
            <div class="label"><?php echo Filters::out( $MESSAGE['interface']['administration']['name_col']); ?></div>
            <input type="text" name="nome" id="nome" class="form_input" value="<?php echo Filters::out($stagione['nome']); ?>">
        </div>
        <div class="single_input">
            <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_season']['min']); ?></div>
            <input type="number" name="minima" id="minima" class="form_input" value="<?php echo  Filters::out($stagione['minima']); ?>">
        </div>
        <div class="single_input">
            <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_season']['max']); ?></div>
            <input type="number" name="massima" id="massima" class="form_input" value="<?php echo Filters::out($stagione['massima']); ?>">
        </div>
        <div class="single_input">
            <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_season']['date_start']); ?></div>
            <input type="date" name="data_inizio" id="data_inizio" class="form_input" value="<?php echo Filters::out($stagione['data_inizio']); ?>">
        </div>
        <div class="single_input">
            <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_season']['date_end']); ?></div>
            <input type="date" name="data_fine" id="data_fine" class="form_input" value="<?php echo Filters::out($stagione['data_fine']); ?>">
        </div>
        <div class="single_input">
            <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_season']['sunrise']); ?></div>
            <input type="time" name="alba" id="alba" class="form_input" value="<?php echo Filters::out($stagione['alba']); ?>">
        </div>
        <div class="single_input">
            <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_season']['sunset']); ?></div>
            <input type="time" name="tramonto" id="tramonto" class="form_input" value="<?php echo Filters::out($stagione['tramonto']); ?>">
        </div>


        <div class="single_input">
            <input type="submit" name="submit" value="Salva"/>
            <input type="hidden" name="action" value="op_edit">
            <input type="hidden" name="id" value="<?php echo Filters::out($stagione['id']); ?>">
        </div>
    </form>

    <div class="link_back">
        <a href="main.php?page=gestione_meteo_stagioni">Torna indietro</a>
    </div>
</div>

<script src="/includes/default/pagesdes/default/pages/gestione/meteo/stagioni/gestione_stagioni_edit.js"></script>
