<!-- Corpo della pagina -->

<div class="fake-table">
    <div class="tr">
        <div class="td">
                <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['name_col']); ?></div>
        </div>
        <div class="td">
                <div class="titoli_elenco"><?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['meteo_condition']['wind_name']); ?>
                </div>
        </div>
            <div class="td">
                <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>
            </div>
        <div class="td">
            <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>

        </div>
        </div>
        <!-- Record -->

    <?php
    $all=$class->getAll();
    while($row = gdrcd_query($all, 'fetch')){
    ?>


    <div class="tr">
        <div class="td">
                <div class="elementi_elenco"><?php echo $row['nome']; ?></div>
            </div>
        <div class="td">
                <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['vento']); ?></div>
        </div>
        <div class="td"><!-- Iconcine dei controlli -->
                <div class="controlli_elenco">
                    <!-- Modifica -->
                         <form action="main.php?page=gestione_meteo_condizioni&id=<?php echo gdrcd_filter('out', $row['id']); ?>"
                              method="post">
                            <input hidden value="edit" name="op">
                            <button type="submit" name="id" value="<?php echo gdrcd_filter('out', $row['id']); ?>"
                                    class="btn-link">[<?php echo $MESSAGE['interface']['forums']['link']['edit']; ?>]
                            </button>
                        </form>
                </div>
        </div>
        <div class="td"><!-- Iconcine dei controlli -->
            <div class="controlli_elenco">

                    <!-- Elimina -->
                    <form action="main.php?page=gestione_meteo_condizioni"  method="post">
                        <input hidden value="delete" name="op">

                        <button type="submit" name="id" onClick='return confirmSubmit()'
                                value="<?php echo gdrcd_filter('out', $row['id']); ?>" class="btn-link">
                            [<?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['delete']); ?>]
                        </button>
                    </form>
                </div>
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
        <input type="submit" class="btn-link" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['new']); ?>">
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
