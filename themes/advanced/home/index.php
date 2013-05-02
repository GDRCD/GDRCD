<?php 
/** * Skin Advanced
	* Markup e procedure della homepage
	* @author Blancks
*/
	
?><div id="main">

	<div id="site_width">

		<div id="header">
			<div class="login_form">
				<form action="login.php" id="do_login" method="post"<?php if ($PARAMETERS['mode']['popup_choise']=='ON'){ echo ' onsubmit="check_login(); return false;"'; } ?>>
					<div>
						<span class="form_label"><label for="username"><?php echo $MESSAGE['homepage']['forms']['username'];?></label></span>
						<input type="text" id="username" name="login1" />
					</div>
					<div>
						<span class="form_label"><label for="password"><?php echo $MESSAGE['homepage']['forms']['password'];?></label></span>
						<input type="password" id="password" name="pass1" />
					</div>
<?php 	if ($PARAMETERS['mode']['popup_choise']=='ON'){ ?>
					<div>
						<span class="form_label"><label for="allow_popup"><?php echo $MESSAGE['homepage']['forms']['open_in_popup'];?></label></span>
						<input type="checkbox" id="allow_popup" />
						<input type="hidden" value="0" name="popup" id="popup">
					</div>
<?php	}	?>
					<input type="submit" value="<?php echo $MESSAGE['homepage']['forms']['login'];?>" />
				</form>
			</div>
			
			<h1><a href="index.php"><?php echo $MESSAGE['homepage']['main_content']['site_title']; ?></a></h1>
			<div class="subtitle"><?php echo $MESSAGE['homepage']['main_content']['site_subtitle']; ?></div>
		</div>


		<div id="content">
	
			<div class="sidecontent">
				<ul>
					<li><a href="index.php?page=index&content=iscrizione"><?php echo $MESSAGE['homepage']['registration'];?></a></li>
					<li><a href="index.php?page=index&content=user_regolamento"><?php echo $MESSAGE['homepage']['rules'];?></a></li>
					<li><a href="index.php?page=index&content=user_ambientazione"><?php echo $MESSAGE['homepage']['storyline'];?></a></li>
					<li><a href="index.php?page=index&content=user_razze"><?php echo $MESSAGE['homepage']['races'];?></a></li>
				</ul>
				
				<div class="side_modules">
					<strong><?php echo $users['online'], ' ', gdrcd_filter('out',$MESSAGE['homepage']['forms']['online_now']); ?></strong>
				</div>
				
				<div class="side_modules">
<?php	if (empty($RP_response)){ ?>
					<strong><?php echo gdrcd_filter('out',$MESSAGE['homepage']['forms']['forgot']);?></strong>
					
					<div class="pass_rec">
						<form action="index.php" method="post">
							<div>
								<span class="form_label"><label for="passrecovery"><?php echo $MESSAGE['homepage']['forms']['email'];?></label></span>
								<input type="text" id="passrecovery" name="email" />
							</div>
							<input type="submit" value="<?php echo $MESSAGE['homepage']['forms']['new_pass'];?>" />
						</form>
					</div>
<?php	}else{ ?>
					<div class="pass_rec">
						<?php echo $RP_response; ?>
					</div>
<?php	} ?>
				</div>
				
				<div class="side_modules">
						<?php include 'themes/'. $PARAMETERS['themes']['current_theme'] .'/home/user_stats.php'; ?>
				</div>
			</div>
			
			<div class="content_body">
			
<?php

		if (file_exists('themes/'. $PARAMETERS['themes']['current_theme'] .'/home/' . $content . '.php'))
				include 'themes/'. $PARAMETERS['themes']['current_theme'] .'/home/' . $content . '.php';


?>
			
			</div>
			
			<br class="blank" />
	
		</div>
	
	
		<div id="footer">
	
			<div>
				<p><?php echo gdrcd_filter('out',$PARAMETERS['info']['site_name']), ' - ', gdrcd_filter('out',$MESSAGE['homepage']['info']['webm']), ': ', gdrcd_filter('out',$PARAMETERS['info']['webmaster_name']), ' - ', gdrcd_filter('out',$MESSAGE['homepage']['info']['dbadmin']),': ', gdrcd_filter('out', $PARAMETERS['info']['dbadmin_name']) ,' - ', gdrcd_filter('out',$MESSAGE['homepage']['info']['email']), ': <a href="mailto:', gdrcd_filter('out',$PARAMETERS['info']['webmaster_email']), '">', gdrcd_filter('out',$PARAMETERS['info']['webmaster_email']), '</a>.'; ?></p>
				<p><?php echo $CREDITS, ' ', $LICENCE ?></p>
			</div>
			
		</div>

	</div>

</div>