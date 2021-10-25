<form class="form"
      action="main.php?page=gestione_meteo_stagioni" method="post">
    <div class="single_input">
        <div class="label"><?php echo  gdrcd_filter('out', $MESSAGE['interface']['administration']['name_col']); ?></div>
        <input type="text" name="nome" id="nome" class="form_input" >
    </div>
    <div class="single_input">
        <div class="label"><?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['meteo_season']['min']); ?></div>
        <input type="numer" name="minima" id="minima" class="form_input"  >

    </div>
    <div class="single_input">
        <div class="label"><?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['meteo_season']['max']); ?></div>
        <input type="number" name="massima" id="massima" class="form_input"  >

    </div>
    <div class="single_input">
        <div class="label"><?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['meteo_season']['date_start']); ?></div>
        <input type="date" name="data_inizio" id="data_inizio" class="form_input"  >

    </div>
    <div class="single_input">
        <div class="label"><?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['meteo_season']['sunrise']); ?></div>
        <input type="time" name="alba" id="alba" class="form_input" >

    </div>
    <div class="single_input">
        <div class="label"><?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['meteo_season']['sunset']); ?></div>
        <input type="time" name="tramonto" id="tramonto" class="form_input"  >

    </div>




    <div class="single_input">
        <input type="submit" name="submit" value="Salva"/>
        <input type="hidden" name="op" value="save_new">
        <input type="hidden" name="id" value="<?php echo  gdrcd_filter('out', $condizione['id']); ?>">
    </div>
</form>
