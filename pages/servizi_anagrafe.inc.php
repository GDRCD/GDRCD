<?php /*HELP: */

if ($_SESSION['permessi'] >= MODERATOR)
{
    $query = "SELECT nome, cognome, email FROM personaggio ORDER BY nome";
} else
{
    $query = "SELECT nome, cognome FROM personaggio WHERE permessi > -1 ORDER BY nome";
}

$result = gdrcd_query($query, 'result'); ?>

<div class="pagina_servizi_anagrafe">


    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['pg_list']['page_name']); ?></h2>
    </div>

    <!-- Operazioni bancarie -->
    <div class="page_body">


        <?php /*Visualizzazione di base*/ ?>
        <div class="panels_box">

            <!-- Deposito -->
            <div class="form_gioco">
                <form
                    action="main.php?page=scheda"
                    method="post">
                    <div class="form_label">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['pg_list']['select']) ?>
                    </div>
                    <div class='form_field'>
                        <select name="pg">
                            <?php while ($row = gdrcd_query($result, 'fetch'))
                            { ?>
                                <option value="<?php echo gdrcd_filter('out', $row['nome']) ?>">
                                    <?php echo gdrcd_filter('out', $row['nome']) . ' ' . gdrcd_filter('out',
                                            $row['cognome']); ?>
                                    <?php if ($_SESSION['permessi'] >= SUPERUSER)
                                    {
                                        echo ' - ' . gdrcd_filter('out', $row['email']);
                                    } ?>
                                </option>
                            <?php }

                            gdrcd_query($result, 'free');
                            ?>
                        </select>
                    </div>
                    <div class='form_submit'>
                        <input name="conferma"
                               type="submit"
                               class="form_gestione_input"
                               value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']) ?>"/>
                    </div>
                </form>
            </div>

        </div>


    </div>
    <!-- banca_operazioni-->


</div><!-- banca_box -->

