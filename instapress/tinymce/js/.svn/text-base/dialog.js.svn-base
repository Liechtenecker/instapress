var InstapressTinyMCE = 
{
		InitPopup : function()
		{
			tinyMCEPopup.resizeToInnerSize();
			document.getElementById('instapress-implementation-version').focus();
			
			jQuery('#instapress-implementation-version').change(function()
			{
				jQuery('.instapress-specific-settings').hide();
				jQuery('#' + jQuery('#instapress-implementation-version option:selected').val()).show();
			});
		},
		
		InsertInstapress : function()
		{
			if(window.tinyMCE) 
			{
				var url = jQuery('#instapress-image-url').val(); // URL des Images
				var size = parseInt(jQuery('#instapress-image-size').val()); // Größe des Images
				var mediasize = 't'; // Kürzel um die richtige Größe zu laden
				var version = jQuery('.instapress-specific-settings:visible').attr('id');
				var shortcode = "";
				
				if(!size)
					size = 90;
				else if(size > 612)
					size = 612;
				
				switch(version)
				{
					// Einzelnes Instagram
					case 'instapress-tinymce-single-settings':
						// Parameter für die Bildgröße suchen
						if(size <= 150)
							mediasize = 't';
						else if(size <= 306)
							mediasize = 'm';
						else
							mediasize = 'l';
						
						// Trailingslash an die URL hängen
						if(url.length > 0 && url[url.length -1] != '/')
							url += '/';
						// Shortcode
						shortcode = '';
						/*if(jQuery('#instapress-effect').val() == 'fancybox')
							shortcode += '<span class="instapress-shortcode"><a rel="instagram-sc-images" href="'+url+'media?size=l">';*/
						shortcode += '<img src="' + url + 'media?size='+mediasize+'" width="'+size+'" height="'+size+'"';
						shortcode += ' />';
						if(jQuery('#instapress-image-likebutton').is(':checked'))
						{
							shortcode += '<div><iframe src="http://www.facebook.com/plugins/like.php?app_id=162539390478243&amp;href=' + encodeURIComponent(url) + 
							'&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;action=like&amp;colorscheme=light' +
							'&amp;font&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:85px; ' +
							'height:21px;" allowTransparency="true"></iframe></div>';
						}
						/*if(jQuery('#instapress-effect').val() == 'fancybox')
							shortcode += '</a></span>';*/
						break;
						
					// Instagram Feed
					case 'instapress-tinymce-feed-settings':
						shortcode = '[instapress ';
						if(jQuery('#instapress-tag').val().length <= 0)
							shortcode += 'userid="' + jQuery('#instapress-username').val() + '" ';
						else if(jQuery('#instapress-tag').val().length > 0)
							shortcode += 'tag="' + jQuery('#instapress-tag').val() + '" ';
						shortcode += 'piccount="' + jQuery('#instapress-piccount').val() + '" ';
						shortcode += 'size="' + size + '" ';
						shortcode += 'effect="' + jQuery('#instapress-effect').val() + '"';
						if(jQuery('#instapress-title').is(':checked') && jQuery('#instapress-effect').val() != 0)
							shortcode += ' title="1"';
						if(jQuery('#instapress-gallery').is(':checked'))
							shortcode += ' paging="1"';
						shortcode += ']';
						
						// Titel machen nur mit ausgewähltem Effekt Sinn
						if(jQuery('#instapress-title').is(':checked') && jQuery('#instapress-effect').val() == 0)
						{
							// User informieren
							alert('Select an effect to show titles!');
							// Fokus auf Dropdown setzen
							jQuery('#instapress-effect').focus();
							return false;
						}
						
						// Gallery macht nur mit ausgewählter Foto Anzahl Sinn
						if(jQuery('#instapress-gallery').is(':checked') && jQuery('#instapress-piccount').val() == 0)
						{
							// User informieren
							alert('Select a number of pictures to display per page!');
							// Fokus auf Dropdown setzen
							jQuery('#instapress-piccount').focus();
							return false;
						}
						
						break;
				}			
				
				window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, shortcode);
				tinyMCEPopup.editor.execCommand('mceRepaint');
				tinyMCEPopup.close();
				return true;
			}
			return true;
		}
};

function init()
{
	InstapressTinyMCE.InitPopup();
}