<div class="panels_box">
    <form action="main.php?page=gestione_ambientazione" method="post" class="form_gestione">
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['art']); ?>
        </div>
        <div class='form_field'>
            <input name="articolo" type="number" />
        </div>
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['title']); ?>
        </div>
        <div class='form_field'>
            <input name="titolo" type="text" />
        </div>
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['infos']); ?>
        </div>
        <div class='form_field'>
            <textarea name="testo"></textarea>
        </div>
        <div class="form_info">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
        </div>
        <!-- bottoni -->
        <div class='form_submit'>
                <input type="hidden" name="op" value="save_new">
                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
            
        </div>
    </form>
</div>
<!-- Link di ritorno alla visualizzazione di base -->
<div class="link_back">
    <a href="main.php?page=gestione_ambientazione">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['link']['back']); ?>
    </a>
</div>