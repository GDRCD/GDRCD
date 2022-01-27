<!-- Corpo della pagina -->

<div class="fake-table index-table">
    <div class="tr header">
        <div class="td">
                <div class="titoli_elenco"><?php echo Filters::out( $MESSAGE['interface']['administration']['name_col']); ?></div>
        </div>
        <div class="td">
                <div class="titoli_elenco"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_condition']['wind_name']); ?>
                </div>
        </div>
            <div class="td">
                <div class="titoli_elenco"><?php echo Filters::out( $MESSAGE['interface']['administration']['ops_col']); ?></div>
            </div>
        <div class="td">
            <div class="titoli_elenco"><?php echo Filters::out( $MESSAGE['interface']['administration']['ops_col']); ?></div>

        </div>
        </div>
        <!-- Record -->

    <?php
    $all=MeteoCondizioni::getInstance()->getAllCondition();
    while($row =         DB::query($all, 'fetch')){
    ?>


    <div class="tr">
        <div class="td">
            <?php
            if(isset($row['img'])){
                $img=Filters::out($row['img']);
                echo "<img src={$img}>";
            }
            echo Filters::out($row['nome']); ?>
            </div>
        <div class="td">
                <?php echo Filters::out($row['vento']); ?>
        </div>
        <div class="td"><!-- Iconcine dei controlli -->

                    <!-- Modifica -->
                         <form action="main.php?page=gestione_meteo_condizioni&id=<?php echo Filters::out(  $row['id']); ?>"
                              method="post">
                            <input hidden value="edit" name="op">
                            <button type="submit" name="id" value="<?php echo Filters::out($row['id']); ?>"
                                    class="btn-link">[<?php echo Filters::out($MESSAGE['interface']['forums']['link']['edit']); ?>]
                            </button>
                        </form>
        </div>
        <div class="td"><!-- Iconcine dei controlli -->

                    <!-- Elimina -->
                    <form action="main.php?page=gestione_meteo_condizioni"  method="post">
                        <input hidden value="delete" name="op">

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

        <!-- link crea nuovo -->

</div>
<div class="link_back">
    <form action="main.php?page=gestione_meteo_condizioni" method="post">
        <input type="submit" class="btn-link" value="<?php echo Filters::out($MESSAGE['interface']['sheet']['diary']['new']); ?>">
        <input type="hidden" name="op" value="new"/>
    </form>
</div>

<script>
    function confirmSubmit() {
        var agree = confirm("Vuoi eliminare la condizione meteo?");
        if (agree)
            return true;
        else
            return false;
    }
</script>
