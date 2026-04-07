<div class="logincontent">
	<div class="login_form">
		<form action="login.php" id="do_login" method="post"
			<?php if ($PARAMETERS['mode']['popup_choise'] == 'ON') { echo ' onsubmit="check_login(); return false;"';} ?>
		>
			<div>
				<span class="form_label"><label for="username"><?php echo $MESSAGE['homepage']['forms']['username']; ?></label></span>
				<input type="text" id="username" name="login1"/>
			</div>
			<div>
				<span class="form_label"><label for="password"><?php echo $MESSAGE['homepage']['forms']['password']; ?></label></span>
				<input type="password" id="password" name="pass1"/>
			</div>
			<?php if (!empty($PARAMETERS['themes']['available']) and count($PARAMETERS['themes']['available']) > 1): ?>
				<div style="white-space: nowrap;">
					<span class="form_label"><label for="theme"><?= gdrcd_filter('out', $MESSAGE['homepage']['forms']['theme_choice']) ?></label></span>
					<select name="theme" id="theme">
						<?php
						foreach ($PARAMETERS['themes']['available'] as $k => $name) {
							echo '<option value="' . gdrcd_filter('out', $k) . '"';
							if ($k == $PARAMETERS['themes']['current_theme']) {
								echo ' selected="selected"';
							}
							echo '>' . gdrcd_filter('out', $name) . '</option>';
						}
						?>
					</select>
				</div>
			<?php endif; ?>
			<?php if ($PARAMETERS['mode']['popup_choise'] == 'ON') { ?>
				<div>
					<span class="form_label"><label for="allow_popup"><?php echo $MESSAGE['homepage']['forms']['open_in_popup']; ?></label></span>
					<input type="checkbox" id="allow_popup"/>
					<input type="hidden" value="0" name="popup" id="popup">
				</div>
			<?php } ?>
			<input type="submit" value="<?php echo $MESSAGE['homepage']['forms']['login']; ?>"/>
		</form>
	</div>
</div>	