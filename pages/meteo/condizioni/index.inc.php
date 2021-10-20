<!-- Corpo della pagina -->
<div class="page_body">
    <table>
        <!-- Intestazione tabella -->
        <tr>
            <td class="casella_titolo">
                <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['name_col']); ?></div>
            </td>
            <td class="casella_titolo">
                <div class="titoli_elenco"><?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['meteo_condition']['wind_name']); ?></div>
            </td>
            <td class="casella_titolo">
                <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>
            </td>
        </tr>
        <!-- Record -->

    <?php

    $all=$class->getAll();

    while($row = gdrcd_query($all, 'fetch')){
    ?>


        <tr>
            <td class="casella_elemento">
                <div class="elementi_elenco"><?php echo $row['nome']; ?></div>
            </td>
            <td class="casella_elemento">
                <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['vento']); ?></div>
            </td>
            <td class="casella_controlli"><!-- Iconcine dei controlli -->
                <div class="controlli_elenco">
                    <!-- Modifica -->
                    <div class="controllo_elenco">
                        <form action="main.php?page=gestione_luoghi" method="post">
                            <input type="hidden" name="id_record"
                                   value="<?php echo $row['id'] ?>" />
                            <input type="hidden" name="op" value="edit" />
                            <input type="image"
                                   src="imgs/icons/edit.png"
                                   alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>"
                                   title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                        </form>
                    </div>
                    <!-- Elimina -->
                    <div class="controllo_elenco">
                        <form action="main.php?page=gestione_luoghi" method="post">
                            <input type="hidden" name="id_record"
                                   value="<?php echo $row['id'] ?>" />
                            <input type="hidden" name="op" value="erase" />
                            <input type="image"
                                   src="imgs/icons/erase.png"
                                   alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>"
                                   title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']
                                   ); ?>" />
                        </form>
                    </div>
                </div>
            </td>
        </tr>
        <?php

    }

    ?>

</div>