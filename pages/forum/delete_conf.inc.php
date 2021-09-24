<?php
echo '
<h3>'.gdrcd_filter('out', $MESSAGE['interface']['forums']['delete']['title']).'</h3>
<form action="main.php?page=forum" method="post">
  <input type="hidden" name="op" value="delete" />
  <input type="hidden" name="id_record" value="'.(int) $_REQUEST['id_record'].'" />
  <p>'.gdrcd_filter('out', $MESSAGE['interface']['forums']['delete']['ask']).'</p>
  <input type="submit" value="'.gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['delete']).'" />
</form>
';