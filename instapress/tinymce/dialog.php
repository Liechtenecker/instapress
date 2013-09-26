<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php _e('Insert Instagram', 'instagram'); ?></title>

	<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
	<?php
		wp_head();
	?>
	<script type="text/javascript">var ajaxurl = '<?php echo admin_url('admin-ajax.php') ?>';</script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo InstagramPlugin::getInstance()->getPluginDirUrl().'tinymce/js/dialog.js'; ?>"></script>
	
	<style type="text/css">
		.instapress-specific-settings
		{
			display: none;	
		}
	</style>
	
</head>
<!-- head end -->

<body id="link">
	<form name="form-instapress-tinymce" action="#" onsubmit="return InstapressTinyMCE.InsertInstapress();">
	
		<div>
			<p>
				<label for="instapress-implementation-version"><?php _e('Option:', 'instagram'); ?></label>
				<select id="instapress-implementation-version">
					<option value="instapress-tinymce-feed-settings"><?php _e('Instagram Feed') ?></option>
					<option value="instapress-tinymce-single-settings"><?php _e('Single Instagram') ?></option>
				</select>
			</p>
		</div>
	
		<!-- Spezifische Einstellungen für Feeds -->
		<div id="instapress-tinymce-feed-settings" class="instapress-specific-settings" style="display:block">
			<p>
				<label for="instapress-username"><?php _e('Username:', 'instagram'); ?></label>
				<input id="instapress-username" name="instapress-username" type="text" value="" />
			</p>
			
			<p>
				<label for="instapress-tag"><?php _e('Tag:', 'instagram'); ?></label>
				<input id="instapress-tag" name="instapress-tag" type="text" value="" />
			</p>
			
			<p>
				<label for="instapress-piccount"><?php _e('Pics:', 'instagram'); ?></label>
				<select id="instapress-piccount" name="instapress-piccount">
					<option value="0"><?php _e('All', 'instagram'); ?></option>
					<?php for($i = 1; $i <= 200; $i++): ?>
					<option value="<?php echo $i ?>"><?php echo $i ?></option>
					<?php endfor; ?>
				</select>
			</p>
			
			<p>
				<label for="instapress-effect"><?php _e('Effect:', 'instagram'); ?></label>
				<select id="instapress-effect" name="instapress-effect">
					<option value="0"><?php _e('None', 'instagram'); ?></option>
					<?php
					$effects = InstagramPlugin::getAvailableEffects();
					foreach($effects as $key=>$effect):
					?>
					<option value="<?php echo $key ?>"><?php echo $effect ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			
			<p>
				<label for="instapress-title"><?php _e('Title:', 'instagram'); ?></label>
				<input id="instapress-title" name="instapress-title" type="checkbox" />
			</p>
			
			<p>
				<label for="instapress-gallery"><?php _e('Image gallery:', 'instagram'); ?></label>
				<input id="instapress-gallery" name="instapress-gallery" type="checkbox" />
			</p>
		</div>
	
		<!-- Einstellungen für einzelne Bilder -->
		<div id="instapress-tinymce-single-settings" class="instapress-specific-settings">
			<p>
				<label><?php _e("URL", 'instagram'); ?>:</label>
				<input type="text" id="instapress-image-url" size="35" />
			</p>
			<p>
				<label for="instapress-image-likebutton"><?php _e("Facebook Like Button", 'instagram'); ?>:</label>
				<input type="checkbox" id="instapress-image-likebutton" size="35" />
			</p>
		</div>
		
		<p>
			<label><?php _e("Size (max. 612)", 'instagram'); ?>:</label>
			<input type="text" id="instapress-image-size" size="3" value="90" /> px
		</p>
		
		<input type="hidden" name="instapress-security" id="instapress-security" value="<?php echo wp_create_nonce('instapress-tinymce'); ?>" />

		<div class="mceActionPanel">
			<div style="float: left">
				<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'instagram'); ?>" onclick="tinyMCEPopup.close();" />
			</div>
	
			<div style="float: right">
				<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'instagram'); ?>" />
			</div>
	   </div>
		
	</form>
	<script type="text/javascript">tinyMCEPopup.executeOnLoad('init();');</script>
</body>
</html>