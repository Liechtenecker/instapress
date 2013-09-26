jQuery(document).ready(function($)
{

	/**
	 * Initialisiert den Fancybox-Effekt für Fancybox-Links/Bilder
	 */
	function initFancyBox()
	{
		if(jQuery.fancybox)
		{
			$('.instagram-image a[rel=instagram-images],.instapress-shortcode a[rel=instagram-sc-images]').fancybox();
		}
	}
	
	/**
	 * Initialisiert den Highslide-Effekt, sofern dieses Plugin installiert wurde
	 */
	function initHighslide()
	{
		$('.instapress-highslide').click(function()
		{
			
			if(typeof(hs) !== 'undefined')
				return hs.expand(this);
			
			alert("You need a Highslide Plugin for WordPress!");
		});
	}
	
	/**
	 * Blättert für die angegebene Instanz auf die angegebene Seite
	 * @param page int			Seitennumer beginnend bei 1
	 * @param instanceId int	ID der Shortcode-Instanz
	 */
	function showPage(page, instanceId)
	{
		if(!instanceId)
			var instanceId = 1;
				
		$('.next-page-instapress-' + instanceId).attr('rel', (page*1+1) + '-' + instanceId);
		if(page > 1)
		{
			$('.prev-page-instapress-' + instanceId).attr('rel', page*1-1 + '-' + instanceId).show();
		}
		else
		{
			$('.prev-page-instapress-' + instanceId).hide();
		}
		$('#instapress-gallery-' + instanceId + ' .instapress-shortcode').hide();
		$('#instapress-shortcode-' + instanceId + '-page-' + page).show();
	}
	
	/**
	 * Zeigt bzw. verbirgt die Ladeanzeige für die angegebene Instanz
	 * @param instanceId int	ID der Shortcode-Instanz
	 * @param hide Boolean		true = ausblenden, false = einblenden
	 */
	function loading(instanceId, hide)
	{
		if(!hide)
		{
			if($('#instapress-gallery-loading').length == 0)
				$('body').append('<div id="instapress-gallery-loading" />');
			var gallery = $('#instapress-gallery-' + instanceId + ' .instapress-shortcode-page:visible');
			var offset = gallery.offset();
			var w = gallery.outerWidth() / 2;
			var h = gallery.outerHeight() / 2;
			var t = offset.top + (h/2);
			var l = offset.left + (w/2);
			
			$('#instapress-gallery-loading').css({ top : t, left: l, width : w, height : h}).show();
		}
		else
		{
			$('#instapress-gallery-loading').hide();
		}
	}
	
	/**
	 * @return Gibt die Konfiguration für die Instanz mit der angegebenen ID zurück
	 */
	function getConfig(instanceId)
	{
		if(!instanceId)
			var instanceId = 1;
		
		return eval('instapressConfig' + instanceId);
	}
	
	var preloadedImagesCount = 0;
	/**
	 * @param instanceId int			ID der Instanz für die ein Bild geladen wurde
	 * @param necessaryImageCount int	Anzahl der Fotos die geladen werden müssen
	 */
	function preloadedImage(instanceId, necessaryImageCount) {
		preloadedImagesCount++;
		if(preloadedImagesCount == necessaryImageCount) {
			loading(instanceId, true);
		}
	}
	
	// Zurückblättern in der Galerie
	$('.prev-page-instapress').live('click', function()
	{
		var rel = $(this).attr('rel');
		var tmp = rel.split(/\-/);
		var page = tmp[0];
		var instanceId = parseInt(tmp[1]);
		showPage(page, instanceId);
		
		return false;
	});
	
	// Weiterblättern in der Galerie
	$('.next-page-instapress').live('click', function()
	{
		var rel = $(this).attr('rel');
		var tmp = rel.split(/\-/);
		var page = tmp[0];
		var instanceId = parseInt(tmp[1]);
		var conf = getConfig(instanceId);
		conf.paging = page;
		var maxId = $('#instapress-' + conf.instanceid + '-next-max-id-' + page).val();
		
		if(!maxId || maxId.length == 0)
			page = 1;
		
		if($('#instapress-shortcode' + conf.instanceid + '-page-' + page).length)
		{
			showPage(page, instanceId);
			return false;
		}
		
		// Ladeanzeige
		loading(instanceId);
		// Seite vom Server laden
		$.post($(this).attr('href') + "/wp-admin/admin-ajax.php", 
		{
		    action : "instapress_paging",
		    config : conf,
		    nextMaxId : maxId 
		}, function(response)
		{
			$('#instapress-gallery-' + instanceId).append(response);
			var addedImages = $('#instapress-shortcode-' + instanceId + '-page-' + page + ' img');
			
			preloadedImagesCount = 0;
			$.each(addedImages, function() {
				var tmpImg = new Image();
				tmpImg.src = $(this).attr('src');
				tmpImg.onload = function() {
					preloadedImage(instanceId, addedImages.length);
				};
			});
			showPage(page, instanceId);
			switch(conf.effect)
			{
				case 'fancybox':
					initFancyBox();
					break;
				case 'highslide':
					initHighslide();
					break;
			}
		});   
		
		return false;
	});
	
	// Beim Laden der Seite immer die Effekte versuchen zu initialisieren
	initFancyBox();
	initHighslide();
	
});