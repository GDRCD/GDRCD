<?php /*Controllo che il diario sia del pg loggato, per inserimento nuove pagine*/
if ($_REQUEST['pg'] == $_SESSION['login']) { ?>
    <form action="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>" method="post">
        <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['new']); ?>">
        <input type="hidden" name="op" value="new"/>
    </form>
<?php } ?>

<div class="fake-table index-table">
    <div class="tr header">
        <div class="td">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['date']); ?>
        </div>
        <div class="td">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['title']); ?>
        </div>
        <?php
        if ($_REQUEST['pg'] == $_SESSION['login'] || $_SESSION['permessi'] >= MODERATOR) { ?>
            <div class="td">
                <?php echo $MESSAGE['interface']['sheet']['diary']['visible']; ?>
            </div>

            <div class="td">
                <?php echo $MESSAGE['interface']['forums']['link']['edit']; ?>
            </div>
            <div class="td">
                <?php echo $MESSAGE['interface']['forums']['link']['delete']; ?>
            </div>
        <?php } ?>
    </div>

    <?php
    if ($_REQUEST['pg'] == $_SESSION['login'] || $_SESSION['permessi'] >= PERMESSI_DIARIO) {
        $query = "SELECT id, data, titolo, testo, visibile FROM diario WHERE personaggio='" . gdrcd_filter('url', $_REQUEST['pg']) . "' ORDER BY data DESC";
    } else {
        $query = "SELECT id, data, titolo, testo, visibile FROM diario WHERE personaggio='" . gdrcd_filter('url', $_REQUEST['pg']) . "' and visibile='si' ORDER BY data DESC";
    }
    $result = gdrcd_query($query, 'result');

    while ($row = gdrcd_query($result, 'fetch')) {
        ?>
        <div class="tr">
            <div class="td">
                <?php echo gdrcd_format_date($row['data']); ?>
            </div>
            <div class="td">
                <form action="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"
                      method="post">
                    <input hidden value="view" name="op">
                    <button type="submit" name="id" value="<?php echo gdrcd_filter('out', $row['id']); ?>"
                            class="btn-link"><?php echo gdrcd_filter('out', $row['titolo']); ?></button>
                </form>
            </div>
            <?php
            if ($_REQUEST['pg'] == $_SESSION['login'] || $_SESSION['permessi'] >= PERMESSI_DIARIO) { ?>
                <div class="td">
                    <?php echo gdrcd_filter('out', $row['visibile']); ?>
                </div>
                <div class="td">
                    <form action="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"
                          method="post">
                        <input hidden value="edit" name="op">
                        <button type="submit" name="id" value="<?php echo gdrcd_filter('out', $row['id']); ?>"
                                class="btn-link">[<?php echo $MESSAGE['interface']['forums']['link']['edit']; ?>]
                        </button>
                    </form>
                </div>
                <div class="td">
                    <form action="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"
                          method="post">
                        <input hidden value="delete" name="op">
                        <button type="submit" name="id" onClick='return confirmSubmit()'
                                value="<?php echo gdrcd_filter('out', $row['id']); ?>" class="btn-link">
                            [<?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['delete']); ?>]
                        </button>
                    </form>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',
        $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
            $MESSAGE['interface']['sheet']['link']['back']); ?></a>
</div>
<script>
    function confirmSubmit() {
        var agree = confirm("Vuoi eliminare la pagina?");
        if (agree)
            return true;
        else
            return false;
    }
</script>
