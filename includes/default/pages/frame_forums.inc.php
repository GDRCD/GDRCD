<?php /*Frame del box con il link alle bacheche e ai messaggi */ ?>
<iframe src="<?=Router::loadPages('frame/forums/index.inc.php?ref=30');?>" class="iframe_forums" allowtransparency="true" frameborder="0"
        scrolling="no">
    <p><?php Filters::out(print $MESSAGE['errors']['can_t_load_frame']) . ' (http://' . $PARAMETERS['info']['site_url'] . '/pages/frame/forums/index.inc.php'; ?></p>
</iframe>