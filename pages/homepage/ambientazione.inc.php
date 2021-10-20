<div class="pagina_ambientazione">
    <?php
    $strInnerPage == "";
    if($_REQUEST['page'] == 'user_ambientazione') {
        include('pages/user_ambientazione.inc.php');
    } else {
        if($_REQUEST['page'] == 'user_razze') {
            include('pages/user_razze.inc.php');
        } else {
            include('pages/user_regolamento.inc.php');
        }
    } ?>
    <!-- Link di ritorno alla homepage -->
    <div class="link_back">
        <a href="index.inc.php">
            <?php echo gdrcd_filter_out($PARAMETERS['info']['homepage_name']); ?>
        </a>
    </div>
</div>