<div class="page_homepage">


<table class="homepage_layout">
<tr>
<td class="home_left" rowspan="2">

<div class="login_box">
<div class="panels_box">
<div class="form_gioco">
  <form name="do_login" id="do_login" action="login.php" method="post"<?php if ($PARAMETERS['mode']['popup_choise']=='ON'){ ?> onsubmit="check_login(); return false;" <?php } ?>target="_top">
	<input type="hidden" value="0" name="popup" id="popup">
    <div class="page_title"><h2>
	   <?php echo $MESSAGE['homepage']['forms']['access_to'];?>
	</h2></div>
	<div class="form_label"><?php echo $MESSAGE['homepage']['forms']['username'];?></div>
    <div class="form_field"><input name="login1" /></div>
   	<div class="form_label"><?php echo $MESSAGE['homepage']['forms']['password'];?></div>
	<div class="form_field"><input type="password" name="pass1" /></div>
<?php 	if ($PARAMETERS['mode']['popup_choise']=='ON'){ ?>
	<div class="form_label"><?php echo $MESSAGE['homepage']['forms']['open_in_popup'];?></div>
	<div class="form_field"><input type="checkbox" id="allow_popup" /></div>
<?php	} ?>
    <div class="form_submit"><input type="submit" value="<?php echo $MESSAGE['homepage']['forms']['login'];?>" /></div>
  </form>
</div>
</div>
</div>

<!-- Password dimenticata -->
<div class="password_change">
<div class="panels_box">
<?php if($_POST['newp']=="changepass"){ 

	$newpass = gdrcd_query("SELECT email FROM personaggio WHERE email = '".gdrcd_filter('in',$_POST['email'])."' LIMIT 1", 'result');

	if (gdrcd_query($newpass, 'num_rows') > 0)
	{
		gdrcd_query($newpass, 'free');
	
       $pass = gdrcd_genera_pass();
	   gdrcd_query("UPDATE personaggio SET pass = '".gdrcd_encript($pass)."' WHERE email = '".gdrcd_filter('in',$_POST['email'])."' LIMIT 1");
	   
	   $subjcet = gdrcd_filter('out',$MESSAGE['register']['forms']['mail']['sub'].' '.$PARAMETERS['info']['site_name']);
	   $text	= gdrcd_filter('out',$MESSAGE['register']['forms']['mail']['text'].': '.$pass);
	   
	   mail($_POST['email'], $subject, $text, 'From: '.$PARAMETERS['info']['webmaster_email']);
	
?>
    <div class="warning">
	   <?php echo gdrcd_filter('out',$MESSAGE['warning']['modified']);?>
	</div>
	<?php } else { ?>
	<div class="error">
	   <?php echo gdrcd_filter('out',$MESSAGE['warning']['cant_do']);?>
	</div>
	<?php }//else 
    } else { ?>
<div class="form_gioco">
  <form name="do_login" action="index.php" method="post">
    <div class="page_title"><h2>
	   <?php echo gdrcd_filter('out',$MESSAGE['homepage']['forms']['forgot']);?>
	</h2></div>
	<div class="form_label"><?php echo $MESSAGE['homepage']['forms']['email'];?></div>
    <div class="form_field"><input name="email" /></div>
    <div class="form_submit"><input type="submit" value="<?php echo $MESSAGE['homepage']['forms']['new_pass'];?>" /><input type="hidden" name="newp" value="changepass" /></div>
  </form>
</div>
<?php } ?>
</div>
</div>

<div class="main_links">
  <a href="iscrizione.php">
  <div class="page_title"><h2>
	   <?php echo $MESSAGE['homepage']['registration'];?>
  </h2></div>
  </a>
  <a href="ambientazione.php?page=user_ambientazione">
  <div class="page_title"><h2>
	   <?php echo $MESSAGE['homepage']['storyline'];?>
  </h2></div>
  </a>
  <a href="ambientazione.php?page=user_regolamento">
  <div class="page_title"><h2>
	   <?php echo $MESSAGE['homepage']['rules'];?>
  </h2></div>
  </a>
</div>

</td>

<td class="home_center">

<?php if ($PARAMETERS['mode']['welcome_message_homepage']=='ON') { ?>
<div class="homepage_main_content">
<div class="page_title"><h2>
	   <?php echo $MESSAGE['homepage']['main_content']['welcome'];?>
</h2></div>
<div class="homepage_main_content_info">
       <?php echo $MESSAGE['homepage']['main_content']['infos'];?>
</div>
</div>
<?php } ?>


</td>


<td class="home_right">

<div class="online_box">
   <?php $row = gdrcd_query("SELECT COUNT(nome) AS counter FROM personaggio WHERE ora_entrata > ora_uscita AND DATE_ADD(ultimo_refresh, INTERVAL 4 MINUTE) > NOW()"); ?>
   <div class="page_title"><h2>
   <?php echo $row['counter'].' '.gdrcd_filter('out',$MESSAGE['homepage']['forms']['online_now']); ?>
   </h2></div>
</div>

<div class="stats_box">
   <?php include 'pages/user_stats.inc.php'; ?>
</div>

</td>

</tr>

<tr>
<td class="home_pegi" colspan="2">

<div class="pegi_box">
   <?php include 'pages/pegi.inc.php'; ?>
</div>

<!-- Pedice -->
</td>
</tr>

<tr>
<td class="home_bottom" colspan ="3">
<div class="homepage_info_box">
    <?php include 'includes/credits.inc.php'; ?>
    <?php echo gdrcd_filter('out',$PARAMETERS['info']['site_name']).' - '.gdrcd_filter('out',$MESSAGE['homepage']['info']['webm']).': '.gdrcd_filter('out',$PARAMETERS['info']['webmaster_name']).' - '.gdrcd_filter('out',$MESSAGE['homepage']['info']['email']).': <a href="mailto:'.gdrcd_filter('out',$PARAMETERS['info']['webmaster_email']).'">'.gdrcd_filter('out',$PARAMETERS['info']['webmaster_email']).'</a>. <br />'.$CREDITS.' '.$LICENCE;  ?>
</div>

</td>
</tr>

</table>




</div>