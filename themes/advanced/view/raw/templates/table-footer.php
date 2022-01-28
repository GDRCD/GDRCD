<div class="tr footer">
    <?php if (!empty($data['footer_text'])) { ?>
        <span><?= $data['footer_text']; ?></span>
    <?php }
    foreach ($links as $link) { ?>
        <a href="<?=$link['href'];?>"><?=$link['text'];?>></a>
    <?php } ?>
</div>