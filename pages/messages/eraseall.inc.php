<?php
gdrcd_query("DELETE FROM messaggi WHERE destinatario = '".$_SESSION['login']."' AND letto = 1");
?>
<div class="warning">
    <?php echo gdrcd_filter('out', $PARAMETERS['names']['private_message']['sing'].$MESSAGE['interface']['messages']['erased']); ?>
</div>
<div class="link_back">
    <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['go_back']); ?></a>
</div>