<div class="wrap" id="instagram-settings">
	<h2>Instapress <?php _e('Settings', 'instagram'); ?></h2>
	
	<?php
		$isPHP5 = (version_compare(phpversion(), '5.0.0', '>='));
		
		if(!$isPHP5)
		{
			$instapressErrors = array(sprintf(__('Instapress requires at least PHP 5.0 to work properly, your version is: %s', 'instagram'), phpversion()));
		}
		else
		{
			// Fehlermeldungen ausgeben 
			$instapressErrors = InstagramPlugin::getInstance()->getErrors();
		}
		
		if($instapressErrors): 
	?>
	<div class="error">
		<?php foreach($instapressErrors as $instapressError): ?>
		<p>
		 	<?php echo $instapressError; ?>
		</p>
		<?php endforeach; ?>
	</div>
	<?php 
		endif; // $instapressErrors 
	?>
	
	<?php if($isPHP5): ?>
	<form method="post">
	<?php 
	
		$instagramOptions = InstagramPlugin::getInstance()->getOptions();
			
		if(InstagramPlugin::getInstance()->getAccessToken()):
			echo '<div class="success">';
			_e('Your application is authorized, have fun!', 'instagram');
			echo '</div>';
		?>
			<div>
				<input type="submit" class="button-primary" name="instagram-reset-settings" value="<?php _e('Reset settings', 'instagram'); ?>" />
			</div>
		<?php
		else:
		?>
			<div class="error">
				<p>
				<?php _e('Instapress requires authentication by xAuth in order to display your friends, public or your own feeds.', 'instagram') ?>
				</p>
			</div>
			<div>
				<p>
					<?php _e('To activate Instapress just enter your Instagram username and password', 'instagram') ?>:
				</p>
				<div>
					<div>
						<label for="instagram-app-client-id"><?php _e('Username', 'instagram') ?></label>
						<input type="text" id="instagram-app-user-username" name="instagram-app-user-username" value="<?php echo esc_attr( $instagramOptions['app_user_username'] ) ?>" />
					</div>
					<div>
						<label for="instagram-app-client-secret"><?php _e('Password', 'instagram') ?></label>
						<input type="password" id="instagram-app-user-password" name="instagram-app-user-password" value="<?php echo esc_attr( $instagramOptions['app_user_password'] ) ?>" />
					</div>
					<div>
						<input type="submit" class="button-primary" name="instagram-update-auth-settings" value="<?php _e('Save settings', 'instagram'); ?>" />
					</div>
				</div>
			</div>
		<?php
			endif;
		?>
		<h3><?php _e('General settings', 'instagram'); ?></h3>
		<?php 
			if(InstagramPlugin::getInstance()->cacheIsWritable()): // Cache aktiv?
		?>
		<div>
			<label for="instagram-cache-time"><?php _e('Refresh cache after', 'instagram') ?></label>
			<?php 
				$possibleCacheTimes = array(0, 5, 10, 15, 30, 45, 60);
			?>
			<select id="instagram-cache-time" name="instagram-cache-time">
				<?php foreach($possibleCacheTimes as $value): ?>
					<option <?php echo esc_attr( $instagramOptions['app_cache_time'] ) == $value ? ' selected="selected"' : '' ?>><?php echo $value ?></option>
				<?php endforeach; ?>
			</select><?php _e('minutes', 'instagram') ?>
		</div>
		<?php 
			else: // Cache inaktiv
		?>
		<div>
			<p><?php _e('Cache is not active', 'instagram'); ?></p>
		</div>
		<?php 
			endif;
		?>
		<p>
			<input type="checkbox" class="button-primary" name="instagram-disable-fancybox" id="instagram-disable-fancybox" <?php echo esc_attr( $instagramOptions['app_disable_effects'] ) ? ' checked="checked"' : '' ?> />
			<label for="instagram-disable-fancybox"><?php _e('Disable any effects (e.g. fancybox)', 'instagram'); ?> </label>
			<span style="display: block">
				<i>(<?php _e('Note: Do only check this if you are having conflicts with other effects or if you do not want to use any effect', 'instagram'); ?>)</i>
			</span>
		</p>
		<p>
			<input type="checkbox" class="button-primary" name="instagram-show-backlink" id="instagram-show-backlink" <?php echo esc_attr( $instagramOptions['app_show_backlink'] ) ? ' checked="checked"' : '' ?> />
			<label for="instagram-show-backlink"><?php _e('Support Instapress by showing a backlink to http://instapress.it among widget or gallery', 'instagram'); ?> </label>
		</p>
		<div>
			<h4><?php _e('Professional settings', 'instagram'); ?></h4>
			<p>
				<?php _e('Those settings may only affect you if you have a basic understanding for HTML.', 'instagram'); ?>
			</p>
			<p>
				<input type="checkbox" class="button-primary" name="instagram-disable-image-attr" id="instagram-disable-image-attr" <?php echo esc_attr( $instagramOptions['app_disable_image_attributes'] ) ? ' checked="checked"' : '' ?> />
				<label for="instagram-disable-image-attr"><?php _e('Disable width and height attribute for images (e.g. for responsive layouts)', 'instagram'); ?> </label>
			</p>
		</div>
		<div>
			<input type="submit" class="button-primary" name="instagram-update-settings" value="<?php _e('Save settings', 'instagram'); ?>" />
		</div>
	</form>
</div>
<h3><?php _e('Trouble shooting', 'instagram'); ?></h3>
<div>
	<?php echo sprintf(__('If you\'re having troubles using this plugin, please be so kind as to report issues here: %s or by e-mail here: %s', 'instagram'), '<a href="http://wordpress.org/tags/instapress?forum_id=10" target="_blank">http://wordpress.org/tags/instapress?forum_id=10</a>', '<a href="mailto:office@liechtenecker.at">office@liechtenecker.at</a>'); ?>
</div>
<div>
	<h3><?php _e('You love this plugin?', 'instagram'); ?></h3>
	<p>
		<?php _e('Then feel free to show your love. Unfortunately we don\'t like flowers but we would appreciate if you show your love by donating for this development. :)', 'instagram'); ?>
	</p>
	<p>
		<?php _e('Thank you!', 'instagram'); ?>
	</p>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="5ELYZ5N7DMEEW">
		<input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110429-1/<?php _e('en_US', 'instagram'); ?>/AT/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="<?php _e('Jetzt einfach, schnell und sicher online bezahlen – mit PayPal.', 'instagram'); ?>'">
		<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110429-1/<?php _e('en_US', 'instagram'); ?>/i/scr/pixel.gif" width="1" height="1">
	</form>
	<?php endif; // ($isPHP5): ?>
</div>