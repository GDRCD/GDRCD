<!-- Corpo della pagina -->

<div class="fake-table">
    <div class="tr header">
        <div class="td">
            <div class="titoli_elenco"><?php echo Filters::out(  $MESSAGE['interface']['administration']['name_col']); ?></div>
        </div>
        <div class="td">
            <div class="titoli_elenco"><?php echo Filters::out( $MESSAGE['interface']['administration']['meteo_season']['min']); ?>
            </div>
        </div>
        <div class="td">
            <div class="titoli_elenco"><?php echo Filters::out( $MESSAGE['interface']['administration']['meteo_season']['max']); ?></div>
        </div>
        <div class="td">
            <div class="titoli_elenco"><?php echo Filters::out( $MESSAGE['interface']['administration']['meteo_season']['date_start']); ?></div>
        </div>
        <div class="td">
            <div class="titoli_elenco"><?php echo Filters::out( $MESSAGE['interface']['administration']['meteo_season']['sunrise']); ?></div>
        </div>
        <div class="td">
            <div class="titoli_elenco"><?php echo Filters::out( $MESSAGE['interface']['administration']['meteo_season']['sunset']); ?></div>
        </div>
        <div class="td">
            <div class="titoli_elenco"><?php echo Filters::out(  $MESSAGE['interface']['administration']['ops_col']); ?></div>

        </div>
    </div>
        <!-- Record -->

    <?php
    $all=MeteoStagioni::getInstance()->getAllSeason();
    while($row = DB::query($all, 'fetch')){
    ?>


    <div class="tr">
        <div class="td">
            <?php echo Filters::out( $row['nome']); ?>
        </div>
        <div class="td">
             <?php echo Filters::out( $row['minima']); ?>
            </div>
        <div class="td">
                <?php echo Filters::out( $row['massima']); ?>
        </div>
        <div class="td">
            <?php echo Filters::out(  $row['data_inizio']); ?>
        </div>
        <div class="td">
            <?php echo Filters::out(  $row['alba']); ?>
        </div>
        <div class="td">
            <?php echo Filters::out(  $row['tramonto']); ?>
        </div>
        <div class="td"><!-- Iconcine dei controlli -->
             <!-- Modifica -->
                         <form action="main.php?page=gestione_meteo_stagioni&id=<?php echo Filters::out(  $row['id']); ?>"
                              method="post">
                            <input hidden value="edit" name="op">
                            <button type="submit" name="id" value="<?php echo Filters::out(  $row['id']); ?>"
                                    class="btn-link">[<?php echo Filters::out( $MESSAGE['interface']['forums']['link']['edit']); ?>]
                            </button>
                        </form>
        </div>
        <div class="td"><!-- Iconcine dei controlli -->
                    <!-- Elimina -->
                    <form action="main.php?page=gestione_meteo_stagioni"  method="post">
                        <input hidden value="delete" name="op">

                        <button type="submit" name="id" onClick='return confirmSubmit()'
                                value="<?php echo Filters::out(  $row['id']); ?>" class="btn-link">
                            [<?php echo Filters::out(  $MESSAGE['interface']['forums']['link']['delete']); ?>]
                        </button>
                    </form>
        </div>
    </div>
        <?php
    }
    ?>
</div>

        <!-- link crea nuovo -->


<div class="link_back">
    <form action="main.php?page=gestione_meteo_stagioni" method="post">
        <input type="submit" class="btn-link" value="<?php echo Filters::out(  $MESSAGE['interface']['sheet']['diary']['new']); ?>">
        <input type="hidden" name="op" value="new"/>
    </form>
</div>

<script>
    function confirmSubmit() {
        var agree = confirm("Vuoi eliminare la stagione?");
        if (agree)
            return true;
        else
            return false;
    }
</script>
