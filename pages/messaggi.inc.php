<?php include('../ref_header.inc.php'); /*Header comune*/ ?>

<?php

if (empty($_SESSION['last_istant_message'])===TRUE){$_SESSION['last_istant_message']=0;}

$non_letti=gdrcd_query("SELECT id FROM messaggi WHERE destinatario = '".gdrcd_filter('in',$_SESSION['login'])."' AND letto=0 AND id > ".$_SESSION['last_istant_message']."", 'result');

$max_id=gdrcd_query("SELECT max(id) as max FROM messaggi WHERE destinatario = '".gdrcd_filter('in',$_SESSION['login'])."' AND letto=0");?>

<div class="pagina_messaggi">


<!--div class="messaggio_forum">
<a href="../main.php?page=forum" target="_top">
<?php
if (empty ($PARAMETERS['names']['forum']['image_file'])===FALSE){
   //echo '<img src="../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$PARAMETERS['names']['forum']['image_file'].'" alt="'.gdrcd_filter('out',$PARAMETERS['names']['forum']['sing']).'" title=="'.gdrcd_filter('out',$PARAMETERS['names']['forum']['sing']).'" />';


   if (($PARAMETERS['names']['forum']['image_file_onclick'])===TRUE){
      $img_up=$PARAMETERS['names']['forum']['image_file'];
	  $img_down=$PARAMETERS['names']['forum']['image_file'];
   } else {
      $img_up=$PARAMETERS['names']['forum']['image_file'];
	  $img_down=$PARAMETERS['names']['forum']['image_file_onclick'];
   }


   echo '<SCRIPT LANGUAGE="JavaScript"> if (document.images) { var forum_button1_up = new Image(); forum_button1_up.src = "../themes/'. $PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$img_up.'"; var forum_button1_over = new Image(); forum_button1_over.src = "../themes/'. $PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$img_down.'";} function forum_over_button() { if (document.images) { document["forum_buttonOne"].src = forum_button1_over.src;}} function forum_up_button() { if (document.images) { document["forum_buttonOne"].src = forum_button1_up.src}}</SCRIPT>';

   echo '<a onMouseOver="forum_over_button()" onMouseOut="forum_up_button()" href="../main.php?page=forum"  target="_top"><img src="../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$PARAMETERS['names']['forum']['image_file'].'" alt="'.gdrcd_filter('out',$PARAMETERS['names']['forum']['plur']).'" title="'.gdrcd_filter('out',$PARAMETERS['names']['forum']['plur']).'" name="forum_buttonOne" /></a>';



} else {
   echo gdrcd_filter('out',$PARAMETERS['names']['forum']['sing']);
}
?>
</a>
</div-->

<?php if((gdrcd_query($non_letti, 'num_rows')==0) || ($max_id['max']<$_SESSION['last_istant_message'])){ ?>
<div class="messaggio_forum">
<?php

	gdrcd_query($non_letti, 'free');

if (empty ($PARAMETERS['names']['private_message']['image_file'])===FALSE){
   if (($PARAMETERS['names']['private_message']['image_file_onclick'])===TRUE){
      $img_up=$PARAMETERS['names']['private_message']['image_file'];
	  $img_down=$PARAMETERS['names']['private_message']['image_file'];
   } else {
      $img_up=$PARAMETERS['names']['private_message']['image_file'];
	  $img_down=$PARAMETERS['names']['private_message']['image_file_onclick'];
   }


   echo '<SCRIPT LANGUAGE="JavaScript"> if (document.images) { var msg_button1_up = new Image(); msg_button1_up.src = "../themes/'. $PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$img_up.'"; var msg_button1_over = new Image(); msg_button1_over.src = "../themes/'. $PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$img_down.'";} function msg_over_button() { if (document.images) { document["msg_buttonOne"].src = msg_button1_over.src;}} function msg_up_button() { if (document.images) { document["msg_buttonOne"].src = msg_button1_up.src}}</SCRIPT>';

   echo '<a onMouseOver="msg_over_button()" onMouseOut="msg_up_button()" href="../main.php?page=messages_center&offset=0"  target="_top"><img src="../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$PARAMETERS['names']['private_message']['image_file'].'" alt="'.gdrcd_filter('out',$PARAMETERS['names']['private_message']['plur']).'" title="'.gdrcd_filter('out',$PARAMETERS['names']['private_message']['plur']).'" name="msg_buttonOne" /></a>';
} else {
   echo '<a href="../main.php?page=messages_center&offset=0" target="_top">'.gdrcd_filter('out',$PARAMETERS['names']['private_message']['plur']).'</a>';
} ?>
</div>

<?php	if ($PARAMETERS['mode']['alert_pm_via_pagetitle']=='ON'){ ?>
<script type="text/javascript">
parent.stop_blinking_title();
</script>
<?php	} ?>

<?php

} else { //$_SESSION['last_istant_message']=$max_id['max'];?>

<div class="messaggio_forum_nuovo">
<a href="../main.php?page=messages_center&offset=0" target="_top">
<?php
if (empty ($PARAMETERS['names']['private_message']['image_file_new'])===FALSE){
   echo '<img src="../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$PARAMETERS['names']['private_message']['image_file_new'].'" alt="'.gdrcd_filter('out',$PARAMETERS['names']['private_message']['plur']).'" title="'.gdrcd_filter('out',$PARAMETERS['names']['private_message']['plur']).'" />';
} else {
   echo gdrcd_filter('out',$PARAMETERS['names']['private_message']['plur']);
} ?>
</a>
</div>

<?php	if ($PARAMETERS['mode']['alert_pm_via_pagetitle']=='ON'){ ?>
<script type="text/javascript">
parent.blink_title("(<?php echo $MESSAGE['interface']['forums']['topic']['new_posts']['sing']; ?>) <?php echo $PARAMETERS['info']['site_name']; ?>", true);
</script>
<?php	} ?>

<?php

		if ($PARAMETERS['mode']['allow_audio'] == 'ON' && $_SESSION['blocca_media'] != 1 && !empty($PARAMETERS['settings']['audio_new_messagges']))
		{
		  $ext=explode('.', $PARAMETERS['settings']['audio_new_messagges']);
			if (isset($PARAMETERS['settings']['audiotype']['.'.strtolower(end($ext))]))
			{
?>


<object data="../sounds/<?php echo $PARAMETERS['settings']['audio_new_messagges']; ?>" type="<?php echo $PARAMETERS['settings']['audiotype']['.'.strtolower(end(explode('.', $PARAMETERS['settings']['audio_new_messagges'])))]; ?>" autostart="true" style="width:1px; height:0px;">
		<embed src="../sounds/<?php echo $PARAMETERS['settings']['audio_new_messagges']; ?>" autostart="true" hidden="true" hidden="true" style="width:1px; height:0px;" />
</object>

<!--[if IE 9]>
<embed src="../sounds/<?php echo $PARAMETERS['settings']['audio_new_messagges']; ?>" autostart="true" hidden="true" />
<![endif]-->


<?php
			}


		}


	}

?>


</div>

<!-- Chiudura finestra del gioco -->

<?php include('../footer.inc.php');  /*Footer comune*/?>
