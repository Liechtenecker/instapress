<?php
	/*
	Plugin Name: Instapress
	Plugin URI: http://instapress.it
	Description: <b>Highly customizable</b> plugin to display a feed of pictures uploaded via <a href="http://instagr.am" target="_blank">Instagram</a>. Display a users media, your own media or the popular media feed. Choose whether to integrate the Instagrams as a widget or directly in your posts.
	Version: 1.5.4
	Author: liechtenecker
	Author URI: http://liechtenecker.at/
	License: GPL2
	*/

	define('INSTAPRESS_VERSION', '1.5.4');

	/*  Copyright 2011 Thomas Krammer  (email : t.krammer@liechtenecker.at)

	    This program is free software; you can redistribute it and/or modify
	    it under the terms of the GNU General Public License, version 2, as 
	    published by the Free Software Foundation.
	
	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU General Public License for more details.
	
	    You should have received a copy of the GNU General Public License
	    along with this program; if not, write to the Free Software
	    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/

	/**
	 * Sprachdatei laden
	 */
	if(!load_plugin_textdomain('instagram','/wp-content/languages/')) // Nach Sprachdatei im wp-languages-Ordner suchen 
		load_plugin_textdomain('instagram','/wp-content/plugins/instapress/languages/'); // Default Sprachdatei laden
		
		
	$instapressIncludePath = get_include_path().PATH_SEPARATOR.
							plugin_dir_path(__FILE__).'instagram-php-api/'.PATH_SEPARATOR.
							plugin_dir_path(__FILE__).'classes/'.PATH_SEPARATOR.
							plugin_dir_path(__FILE__).'PowerHour_Geocoder/';
		
	// Include-Path für Zend-Library, Geocoder oder API setzen
	if(!set_include_path($instapressIncludePath)) // Wenn set_include_path nicht funktioniert
		ini_set('include_path',	$instapressIncludePath); // ini_set versuchen
	
	require_once 'Instagram_XAuth.php';
	
	require_once 'Instapress_TinyMCE.php';

	class InstagramPlugin
	{
				
		// Key in der Tabelle wp_options, unter dem die Einstellungen gespeichert werden
		var $dbOptionKey = 'InstagramPlugin_Options';
		
		// Pfad in dem die Cache-Dateien liegen
		var $cachePath = '';
		
		// Nummer der aktuellen Instanz
		static $CURRENTINSTANCENUMBER = 1;
		
		
		/**
		 * Constructor
		 */
		function InstagramPlugin()
		{
			// Menü im Backend hinzufügen
			add_action('admin_menu', array($this, 'admin_menu'));
			
			// Shortcode registrieren
			add_shortcode('instapress', array($this, 'shortcode'));
			
			// Link in der Plugins-Liste zu den Einstellungen
			add_filter('plugin_action_links', array($this, 'plugin_page_link'), 5, 2);
			
			// Links unterhalb der Plugin-Beschreibung
			add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);
			
			// Pfad in dem Cache-Dateien abgespeichert werden
			$this->cachePath = ABSPATH.'wp-content/cache/';
			
			// Javascripts ergänzen
			add_action('init', array($this, 'javascript'));
			
			add_action('wp_ajax_instapress_paging', array($this, 'ajax_instapress_paging'));
			add_action('wp_ajax_nopriv_instapress_paging', array($this, 'ajax_instapress_paging'));
		}
		
		/**
		 * @return InstagramPlugin Die aktive Instanz des Plugins
		 */
		function getInstance()
		{
			global $InstagramPlugin;
			if(!isset($InstagramPlugin))
			{
				$InstagramPlugin = new InstagramPlugin();
			}
			
			return $InstagramPlugin;
		}
		
		/**
		 * @return Instagram eine konfigurierte Instanz der API
		 */
		function getAPIInstance()
		{
			// Konfiguration laden
			$config = InstagramPlugin::getConfiguration();
			        
			// API instanzieren
			$instagram = new Instagram_XAuth($config);
			
			$instagram->setAccessToken(InstagramPlugin::getAccessToken());
			
			return $instagram;
		}
		
		/**
		 * Wird im register_actication_hook aufgerufen
		 */
		function install()
		{
			$this->getOptions();
		}
		
		/**
		 * Wird bei der Implementierung von shortcode aufgerufen
		 */
		function shortcode($params)
		{
			$values = shortcode_atts(array
									(
										'userid' => '',
										'size' => 85,
										'piccount' => 9,
										'effect' => false,
										'url' => false,
										'title' => 0,
										'paging' => 0,
										'max_id' => '',
										'like' => 0,
										'tag' => '',
										'instanceid' => null
									), 
									$params);
			
			// Wenn für die Instanz noch keine ID angegeben/vergeben wurde
			if(empty($values['instanceid']))
			{
				// Neue Instanz-ID zuweisen
				$values['instanceid'] = self::$CURRENTINSTANCENUMBER++;
			}
			
			$instanceId = $values['instanceid'];
			
			// Default-Groesse ist 150x150
			$picSize = (intval($values['size']) > 0) ? intval($values['size']) : 150;
			// Seite die gezeigt werden soll
			$page = intval($values['paging']);
			
			// HTML das vor dem Image eingefügt wird
			$beforeImage = '<div class="instapress-shortcode-image %1$s" id="instapress-shortcode-'.$instanceId.'-image-%2$d">';
			// HTML in dem URL des Images und die gewünschte Größe eingefügt werden
			$imageHtml = '<img src="%1$s" ';
			// width und height aktiviert
			if(!$this->imageAttributesDisabled())
			{
				$imageHtml .= 'width="%2$d" height="%2$d" ';
			}
			$imageHtml .= 'border="0" /></a></div>';
			// HTML für den Paginator
			$paginatorHtml = '<div class="instapress-shortcode-pager">%s</div>';
			// HTML für den Weiter-Button
			$buttonNextHtml = '<a href="'.get_bloginfo( 'wpurl' ).'" class="next-page-instapress next-page-instapress-'.$instanceId.'" rel="%d-'.$instanceId.'">'.__('Next', 'instagram').' &gt;&gt;</a>';
			// HTML für den Zurück-Button
			$buttonPrevHtml = '<a href="'.get_bloginfo( 'wpurl' ).'" class="prev-page-instapress prev-page-instapress-'.$instanceId.'" rel="%d-'.$instanceId.'">&lt;&lt; '.__('Previous', 'instagram').'</a>';
			
			// Attribut id=instapress-shortcode-page-$page nur zuweisen, wenn Paging aktiviert ist
			$result = '<div class="instapress-shortcode version-'.InstagramPlugin::getVersion().($page ? ' instapress-shortcode-page" id="instapress-shortcode-'.$instanceId.'-page-'.$page.'' : '').'">';
			
			if(!$values['url']) // Nicht via oEmbed?
			{
				$result .= $this->getFeed($values, $imageHtml, $beforeImage, $picSize, $values['max_id']);
								
				// Paging aktiviert und Intialer Request (kein AJAX-Request) ==> keine max_id
				if($page && strlen($values['max_id']) == 0)
				{
					$buttons = '';
					//if($page > 1)
						$buttons .= sprintf($buttonPrevHtml, $page-1);
					$buttons .= sprintf($buttonNextHtml, $page+1);
					
					$paginator = sprintf($paginatorHtml, $buttons);
					
					
					// Script vor dem Container
					$result = 	'<script type="text/javascript">var instapressConfig'.$instanceId.' = '.json_encode($values).';</script>'.
								$paginator.
								'<div class="instapress-gallery" id="instapress-gallery-'.$instanceId.'">'.
								$result;
				}
			}
			else // via oEmbed
			{
				$oEmbed = $this->getOEmbedImage($values['url']);
				
				$result .= sprintf($beforeImage, 'oembed', 0);
				
				// Auf aktivierte Effekte überprüfen
				switch($values['effect'])
				{
					// jQuery Fancybox
					case 'fancybox':
						$result .= '<a href="'.$oEmbed->url.'" rel="instagram-sc-images" title="'.htmlentities($oEmbed->title).'">';
						break;
					case 'highslide':
						$result .= '<a href="'.$oEmbed->url.'" class="highslide instapress-highslide" title="'.htmlentities($oEmbed->title).'">';
						break;
					// Ohne Effekt
					default:
						$result .= '<a href="'.$values['url'].'" target="_blank">';
						break;
				}
				
				$result .= sprintf($imageHtml, $oEmbed->url, $picSize);
			}
			
			if(InstagramPlugin::getInstance()->mayShowBacklink())
			{
				$result .= InstagramPlugin::getBacklink();
			}
			
			$result .= '</div>';
						
			if($page)
			{
				$result .= '</div>'.$paginator;
			}
			
			return $result;
		}
		
		/**
		 * Funktion die beim AJAX-Request für die Gallery-Funktion
		 */
		function ajax_instapress_paging()
		{
			$values = $_POST['config'];
			if(is_array($values))
			{
				foreach($values as $key=>$value)
				{
					if(is_numeric($value))
					{
						$values[$key] = intval($value);
					}
				}
				$values['url'] = false;
				$values['max_id'] = $_POST['nextMaxId'];
				
				echo $this->shortcode($values);
			}
			else
			{
				_e("Your jQuery version seems to be out of date. Please update jQuery at least to version 1.4.", 'instagram');
			}
			
			die(); // this is required to return a proper result
		}
		
		/**
		 * Erstellt den HTML-Quelltext für den Fotofeed mit den angegebenen Parametern
		 * @param $values array			Siehe Instapress-shortcode-Parameter
		 * @param $imageHtml string		HTML-Template für das Image
		 * @param $beforeImage string	HTML das vor dem Image gezeigt werden soll
		 * @param $picSize int			Größe des Fotos in Pixel
		 * @param $nextMaxId int		ID des Fotos, ab dem der Feed erstellt werden soll (Default: 0)
		 * 
		 * @todo Für Multiple Instanzen kompatibel machen (duplicate ID-Attrbiutes...)
		 */
		function getFeed($values, $imageHtml, $beforeImage, $picSize, $nextMaxId = '')
		{
			$tagFeed = (!empty($values['tag']));
			$result = "";
			if(isset($values['userid']) && !empty($values['userid']))
			{
				$userid = $values['userid'];
				if(!is_numeric($values['userid']) && $values['userid'] != 'self' && $values['userid'] != 'myfeed' && strlen($values['userid']))
					$userid = InstagramPlugin::getUserIdByName($values['userid']);
			}
				
			$piccounter = 1;
		
			// "Ungerades" Bild
			$odd = true;
			
			$lastShownId = $nextMaxId;
			
			do
			{
				$max_id = $nextMaxId;
				// Feed eines Users ab der gegebenen max_id laden und nächsten max_id holen
				if(!$tagFeed)
					$data = InstagramPlugin::getFeedByUserId($userid, $max_id, $nextMaxId, intval($values['piccount']));
				// Feed eines Users nach Tag gefiltert ab der gegebenen max_id laden und nächsten max_id holen
				else if($tagFeed && $userid)
					$data = InstagramPlugin::getFeedByUserId($userid, $max_id, $nextMaxId, intval($values['piccount']), new InstapressFeedFilter('tags', $values['tag'], InstapressFeedFilter::IN_ARRAY));
				else // Feed nach angegebenem Tag laden
					$data = InstagramPlugin::getFeedByTag($values['tag'], $max_id, $nextMaxId, intval($values['piccount']));

				// Daten im Feed gefunden
				if(count($data) > 0)
				{
					foreach($data as $obj)
					{
						// Nur die ersten X Bilder anzeigen?
						if(intval($values['piccount']) > 0 && $piccounter > $values['piccount'])
							break;
							
						// Image-Titel anzeigen, wenn title auf 1 gesetzt wurde
						$title = (intval($values['title']) == 1) ? $obj->caption->text : "";
						$title = htmlentities(utf8_decode($title));
						
						// Klasse für gerade/ungerade und ID mit Index hinzufügen
						$result .= sprintf($beforeImage, (($odd) ? 'odd' : 'even'), $piccounter++);
						
						$odd = !$odd;
						
						// Welche Property soll für das Image verwendet werden
						$imageKey = InstagramPlugin::getImageKey($picSize);
						
						// Auf aktivierte Effekte überprüfen
						switch($values['effect'])
						{
							// jQuery Fancybox
							case 'fancybox':
								$result .= '<a href="'.$obj->images->standard_resolution->url.'" class="fancybox instapress-fancybox" rel="instagram-sc-images" title="'.$title.'">';
								break;
							case 'highslide':
								$result .= '<a href="'.$obj->images->standard_resolution->url.'" class="highslide instapress-highslide" title="'.$title.'">';
								break;
							// Ohne Effekt
							default:
								$result .= '<a href="'.$obj->link.'" target="_blank">';
								break;
						}
						
						$result .= sprintf($imageHtml, $obj->images->$imageKey->url, $picSize);
												
						if($nextMaxId)
							$lastShownId = $obj->id;
						else
							$lastShownId = '';
					}
				}
				else
				{
					break;
				}
			}
			while($nextMaxId && ($piccounter <= $values['piccount'] || intval($values['piccount']) == 0));
			
			$result .= '<input type="hidden" id="instapress-'.$values['instanceid'].'-next-max-id-'.(intval($values['paging'])+1).'" value="'.$nextMaxId.'" />';
			
			return $result;
		}
		
		/**
		 * Lädt eine einzelnes Image anhand der Instagram-URL
		 * @param $url string Instagram-URL des Images
		 */
		function getOEmbedImage($url)
		{
			$json = @file_get_contents('http://api.instagram.com/oembed?url='.$url);
			return json_decode($json);
		}
		
		/**
		 * Lädt den Feed für den angegebenen User
		 * @param $userid mixed 	User-Id, 'self' oder 0/null/false
		 * @param $max_id int 		ID ab der der Feed geladen werden soll
		 * @param $nextMaxId int	max_id die für den Aufruf der nächsten Seite des Feeds benötigt wird
		 * @param $filter array		Liste mit Filter (z.B. 'tag' => 'myhashtag')
		 * 
		 * @return array der Feed (siehe Instagram API Dokumentation)
		 */
		function getFeedByUserId($userid, $max_id = '', $nextMaxId = 0, $count = 0, $filter = null)
		{	
			$writeToCache = true;
									
			$cacheid = $userid.($max_id ? "_".$max_id : "");
			
			if(InstagramPlugin::getInstance()->getFeedFromCache($cacheid))
			{
				$json = InstagramPlugin::getInstance()->getFeedFromCache($cacheid);
				$writeToCache = false;
			}
			// Wenn es eine User-Id gibt bzw. 'self' hinterlegt wurde, diesen Feed laden
			else if(intval($userid) != 0 || $userid == 'self')
			{
				$json = InstagramPlugin::getAPIInstance()->getUserRecent($userid, $max_id, $count);
			}
			// Wenn statt der UserId 'myfeed' eingetragen wurde, den Feed des Users laden
			else if($userid == 'myfeed')
			{
				$json = InstagramPlugin::getAPIInstance()->getUserFeed($max_id);
			}
			// ansonsten einfach den Popular-Media-Feed laden
			else
			{
				$json = InstagramPlugin::getAPIInstance()->getPopularMedia();
			}
						
			$response = json_decode($json);
			$result = null;
			
			if($response->data)
			{
				$result = $response->data;
			}
			
			// Wenn ein Filter definiert wurde
			if(!empty($filter))
			{
				// diesen anwenden
				$result = $filter->filter($result);
			}
			
			if($writeToCache && $result)
				InstagramPlugin::getInstance()->writeFeedToCache($cacheid, $json);
				
			// Wenn es noch weitere Fotos gibt
			if($response->pagination)
				$nextMaxId = $response->pagination->next_max_id; // max_id für nächsten Request setzen
			else // Keine weiteren Fotos mehr
				$nextMaxId = null;
				
				
			return $result;
		}
		
		/**
		 * Lädt den Feed für den angegebenen Tag
		 * @param $tag string	 	Hashtag nach dem gesucht werden soll
		 * @param $max_id int 		ID ab der der Feed geladen werden soll
		 * @param $nextMaxId int	max_id die für den Aufruf der nächsten Seite des Feeds benötigt wird
		 * 
		 * @return array der Feed (siehe Instagram API Dokumentation)
		 */
		function getFeedByTag($tag, $max_id = '', $nextMaxId = 0, $count = 0)
		{	
			$writeToCache = true;
									
			$cacheid = $tag.($max_id ? "_".$max_id : "");
			
			if(InstagramPlugin::getInstance()->getFeedFromCache($cacheid))
			{
				$json = InstagramPlugin::getInstance()->getFeedFromCache($cacheid);
				$writeToCache = false;
			}
			else
			{
				$json = InstagramPlugin::getAPIInstance()->getRecentTags($tag, $max_id);
			}
						
			$response = json_decode($json);
			
			if($writeToCache && $response->data)
				InstagramPlugin::getInstance()->writeFeedToCache($cacheid, $json);
				
			// Wenn es noch weitere Fotos gibt
			if($response->pagination)
				$nextMaxId = $response->pagination->next_max_id; // max_id für nächsten Request setzen
			else // Keine weiteren Fotos mehr
				$nextMaxId = null;
				
			return $response->data;
		}
		
		function getCacheFilename($cachename)
		{
			if(!$cachename)
				$cachename = 'popular-media';
			return $this->cachePath.'cache-'.$cachename.'.json';
		}
		
		function getDataFromCache($cachename)
		{
			// Dateiname der Cache-Datei
			$cacheFile = $this->getCacheFilename($cachename);
			
			// Wenn die Cache-Datei lesbar ist und nicht �lter als die maximal erlaubte Cache-Zeit
			if($this->cacheIsEnabled() && is_readable($cacheFile) && filemtime($cacheFile) > strtotime('- '.$this->getOption('app_cache_time').' Minutes', time()))
			{
				// Cache laden
				return @file_get_contents($cacheFile);	
			}
			
			return false;	
		}
		
		/**
		 * Versucht die angegebenen Daten in den Cache zu schreiben
		 * @param $cachename string Name des Caches
		 * @param $json string JSON-Daten
		 * 
		 * @return bool true = in Cache geschrieben, false = Fehler beim Cache schreiben
		 */
		function writeDataToCache($cachename, $json)
		{
			// Dateiname der Cache-Datei
			$cacheFile = $this->getCacheFilename($cachename);
			
			// Beschreibbarer Cache?
			if($this->cacheIsEnabled())
			{
				@file_put_contents($cacheFile, $json);
				return true;
			}
			
			return false;
		}
		
		/**
		 * Überprüft ob der Inhalt gecached werden soll und kann
		 * 
		 * @return bool true = Cache aktiv, false = Cache inaktiv
		 */
		function cacheIsEnabled()
		{
            if($this->getOption('app_cache_time') == 0)
                return false;
			
            return $this->cacheIsWritable();
		}
        
        /**
		 * Überprüft ob der Inhalt gecached werden kann bzw. es ein beschreibbares Cache-Verzeichnis gibt
		 * http://codex.wordpress.org/Changing_File_Permissions
		 * 
		 * @return bool true = Cache beschreibbar, false = Cache nicht beschreibbar
		 */
        function cacheIsWritable()
        {
            // Wenn es das Cache-Verzeichnis noch nicht gibt und wp-content aber beschreibbar ist
			if(!is_dir($this->cachePath) && is_writable(ABSPATH.'wp-content/'))
			{
				// Versuchen Cache-Verzeichnis mit Schreibrechten anzulegen
				return @mkdir($this->cachePath, 0755);
			}
			
			// Beschreibbares Cache-Verzeichnis
			return is_writable($this->cachePath);
        }
		
		/**
		 * Lädt einen Feed aus dem Cache
		 * @param $cachename
		 */
		function getFeedFromCache($cachename)
		{
			return $this->getDataFromCache($cachename);
		}
		
		/**
		 * Speichert einen Feed im Cache
		 * @param $cachename string
		 * @param $json string
		 */
		function writeFeedToCache($cachename, $json)
		{
			return $this->writeDataToCache($cachename, $json);
		}
		
		/**
		 * Lädt ein Image aus dem Cache
		 * @param $mediaId int ID des Images
		 */
		function getMediaFromCache($mediaId)
		{
			return $this->getDataFromCache('media-'.$mediaId);
		}
		
		/**
		 * Speichert ein Image im Cache
		 * @param $mediaId int ID des Images
		 * @param $json string JSON-Daten
		 */
		function writeMediaToCache($mediaId, $json)
		{
			return $this->writeDataToCache('media-'.$mediaId, $json);
		}
		
		/**
		 * 
		 * @param $coordinates array Koordinaten
		 * 
		 * @return array Feed mit Bildern
		 */
		function getLocationBasedFeed($coordinates)
		{
			// Wenn Koordinaten gespeichert wurden
			if(!empty($coordinates))
			{
				// Name für den Cache erstellen
				$cachename = implode('-', $coordinates);
				$cachename = str_replace('.', '_', $cachename);
				
				// Versuchen Feed aus dem Cache zu laden
				if(InstagramPlugin::getInstance()->getFeedFromCache($cachename))
				{
					$json = InstagramPlugin::getInstance()->getFeedFromCache($cachename);
				}
				else // Wenn kein passender Feed im Cache war
				{
					// Feed von dem API laden
					$json = InstagramPlugin::getAPIInstance()->mediaSearch($coordinates[0], $coordinates[1], null, null, 250);
					// Im Cache speichern
					InstagramPlugin::getInstance()->writeFeedToCache($cachename, $json);
				}
				
				$response = json_decode($json);
				
				return $response->data;
			}
			
			return array();
		}
		
		/**
		 * Lädt den Titel des Bildes
		 * @param $imageId int ID des Bildes, von dem der Titel geladen werden soll
		 * 
		 * @return string Titel des Bildes
		 */
		function getImageTitle($imageId)
		{
			// Image aus Cache laden
			$json = $this->getMediaFromCache($imageId);
			
			// Noch nicht im Cache?
			if(!$json)
			{
				$json = $this->getAPIInstance()->getMedia($imageId);
				$writeToCache = true;
			}
			
			$media = json_decode($json);
			
			if($writeToCache && $media->data)
				InstagramPlugin::getInstance()->writeMediaToCache($imageId, $json);
			
			return $media->data->caption->text;
		}
		
		/**
		 * 
		 * @param $name string Instagram-Username
		 * 
		 * @return int User-Id des gesuchten Users, 'self' oder 0, wenn kein passender User gefunden wurde
		 */
		function getUserIdByName($name)
		{			
			if($name && $name != 'self')
			{
				$json = InstagramPlugin::getAPIInstance()->searchUser($name);
				
				$response = json_decode($json);
								
				$data = $response->data;
				
				if(count($data) > 0)
				{
					return $data[0]->id;
				}
			}
			else if($name == 'self')
			{
				return $name;
			}
			return 0;
		}
		
		/**
		 * Lädt die gespeicherten Einstellungen bzw. die Standardeinstellungen
		 * 
		 * @return array Gespeicherte Einstellungen
		 */
		function getOptions()
		{
			// Default-Werte
			$options = array
			(
				'app_access_token' => '',
				'app_cache_time' => 30
			);
			
			// Gespeicherte Werte laden
			$saved = get_option($this->dbOptionKey);
			
			// Wenn es gespeicherte Werte gibt
			if(!empty($saved))
			{
				// Gespeicherte Werte über Default-Werte schreiben
				foreach($saved as  $key => $option)
				{
					$options[$key] = $option;
				}
			}
			
			//
			if($saved != $options)
				update_option($this->dbOptionKey, $options);
				
			return $options;
		}
		
		function getPluginUrl()
		{
			return get_admin_url(null, 'options-general.php?page=instagram.php');
		}
		
		function getPluginDirUrl()
		{
			return trailingslashit(plugins_url('', __FILE__));
		}
		
		function getPluginDirPath()
		{
			return trailingslashit(plugin_dir_path(__FILE__));
		}
		
		/**
		 * Lädt eine einzelne Einstellung
		 * 
		 * @param $key string Key der Option
		 * 
		 * @return mixed
		 */
		function getOption($key)
		{
			$options = $this->getOptions();
			
			return $options[$key];
		}
		
		/**
		 * Handelt das Formular
		 */
		function handleOptions()
		{
			$options = $this->getOptions();
			
			//Formular abgesendet
			if(isset($_POST['instagram-update-auth-settings']))
			{			
				$options = array();
				//$options['app_client_id'] = trim($_POST['instagram-app-client-id']);
				//$options['app_client_secret'] = trim($_POST['instagram-app-client-secret']);
				$options['app_user_username'] = trim($_POST['instagram-app-user-username']);
				$options['app_user_password'] = trim($_POST['instagram-app-user-password']);
				
				// Einstellungen in der DB speichern
				update_option($this->dbOptionKey, $options);
				
				// API instanzieren
				$instagram = InstagramPlugin::getAPIInstance();
				
				// Wenn es noch keinen Token gibt
				if(!$options['app_access_token'])
				{
					// In dieser Variable werden eventuelle Fehlermeldungen während der Autorisierung gespeichert
					$errorMessage = "";
					// Request an das API schicken, um einen Access-Token zu erhalten
					$token = $instagram->getAccessToken($errorMessage);
				
					// Wenn es einen Access-Token gibt
					if($token)
					{
						// Access-Token speichern
						$options['app_access_token'] = $token;
						// Einstellungen in der DB speichern
						update_option($this->dbOptionKey, $options);
						//
						echo '<div class="updated"><p>'.__('Settings saved.', 'instagram').'</p></div>';
					}
					else if($errorMessage) // Es ist ein Fehler aufgetreten
					{
						echo '<div class="error"><p>'.__('Instagram API reported the following error', 'instagram').': <b>';
						echo $errorMessage;
						echo '</b></p></div>';
					}
				}
			}
			// Einstellung zurücksetzen
			else if(isset($_POST['instagram-reset-settings']))
			{
				// Einstellungen in der DB zurücksetzten / löschen
				delete_option($this->dbOptionKey);
			}
			
			// Allgemeine Einstellungen
			if(isset($_POST['instagram-update-settings']))
			{
				// Cache-Interval speichern
				$cacheTime = intval($_POST['instagram-cache-time']);
				$options['app_cache_time'] = $cacheTime;
				$options['app_disable_effects'] = isset($_POST['instagram-disable-fancybox']);
				$options['app_disable_image_attributes'] = isset($_POST['instagram-disable-image-attr']);
				$options['app_show_backlink'] = isset($_POST['instagram-show-backlink']);
				// Einstellungen in der DB speichern
				update_option($this->dbOptionKey, $options);
			}
			
			// Die Instagram-Authorize-URI
			$authorizeUrl = $this->getOAuthRedirectUrl();
			
			include('instagram-options.php');
		}
		
		/**
		 * @return array Gibt die Einstellungen für die API zurück
		 */
		function getConfiguration()
		{
			$options = InstagramPlugin::getInstance()->getOptions();
			return array(
							'site_url' 		=> 'https://api.instagram.com/oauth/access_token',
				            'client_id' 	=> '0a344b64448b43e5bb8e1c22acffc0ef',
				            'client_secret' => 'ff62e43965be4a48b83a32261cd540bc',
							'username' 		=> $options['app_user_username'],
							'password' 		=> $options['app_user_password'],
				            'grant_type' 	=> 'password',
				            'redirect_uri'	=> InstagramPlugin::getOAuthRedirectUrl()
				        );
		}
		
		/**
		 * Fügt den Menüpunkt Instapress im Backend hinzu
		 */
		function admin_menu()
		{
			add_options_page('Instapress '.__('Settings', 'instagram'), 'Instapress', 8, basename(__FILE__), array($this, 'handleOptions'));
		}
		
		/**
		 * @return string Name des Plugins
		 */
		function getPluginName()
		{
			return plugin_basename(__FILE__);
		}
		
		/**
		 * Hook für die Link in der Plugin-Liste
		 * @param $links
		 * @param $file
		 */
		function plugin_page_link($links, $file) 
		{			
			// Wenn es dieses Plugin ist
			if($file == $this->getPluginName())
			{
				// Link zu Einstellungs-Seite
				$settingsLink = '<a href="'.$this->getOAuthRedirectUrl().'">'.__('Settings', 'instagram').'</a>';
				// Vorne anhängen
				array_unshift( $links, $settingsLink );
			}
			
			return $links;
		}
		
		/**
		 * Hook für Daten unterhalb der Plugin-Becshreibung in der Plugin-Liste
		 * @param $links
		 * @param $file
		 */
		function plugin_row_meta($links, $file)
		{
			// Wenn es dieses Plugin ist
			if($file == $this->getPluginName())
			{
				// Facebook Page
				$links[] = '<a href="http://www.facebook.com/instapress" target="_blank">'.__('Like it on Facebook', 'instagram').'</a>';
				// Blog-Beitrag
				$links[] = '<a href="http://liechtenecker.at/instapress-das-instagram-plugin-fur-wordpress/" target="_blank">'.__('Visit our blog', 'instagram').'</a>';
			}
			
			return $links;
		}
		
		/**
		 * @return bool		true = Fancybox-Effekt aktiviert, false = Fancybox-Effekt deaktiviert
		 */
		function effectsEnabled()
		{
			return (!$this->getOption('app_disable_effects'));
		}
		
		/**
		 * @return bool		true = Darf Backlink anzeigen, false = keinen Backlink anzeigen
		 */
		function mayShowBacklink()
		{
			return ($this->getOption('app_show_backlink'));
		}
		
		function getBacklink()
		{
			return '<a class="instagram-backlink" href="http://instapress.it" target="_blank">Powered by Instapress</a>';
		}
		
		/**
		 * @return bool		true = width und height für img-Tags deaktivieren, false = width und height in img-Tag setzen
		 */
		function imageAttributesDisabled()
		{
			return $this->getOption('app_disable_image_attributes');	
		}
		
		function javascript()
		{
			if($this->effectsEnabled())
			{
				if(!is_admin())
				{
					// Fancybox
					wp_register_script('fancybox', plugins_url('/fancybox/jquery.fancybox-1.3.4.pack.js', __FILE__), array('jquery'), "1.3.4");
					// Default JS
					wp_enqueue_script('instapress', plugins_url('/instapress.js', __FILE__), array('jquery', 'fancybox'), InstagramPlugin::getVersion(), true);
				}
			}
			else // Wenn Effekte deaktiviert wurden, muss die Gallery trotzdem noch funktionieren
			{
				//wp_enqueue_script('instapress', plugins_url('/instapress.js', __FILE__), array('jquery'), InstagramPlugin::getVersion(), true);
			}
		}
		
		/**
		 * @return string die URI an die nach der Autorisierung weitergeleitet wird
		 */
		function getOAuthRedirectUrl()
		{
			return get_admin_url().'options-general.php?page=instagram.php';//.'instagram/oauth.php';
		}
		
		/**
		 * @return string Access-Token der in der Datenbank gespeichert ist bzw. null
		 */
		function getAccessToken()
		{
			$options = InstagramPlugin::getInstance()->getOptions();
			
			return $options['app_access_token'];
		}
		
		function getVersion()
		{
			return INSTAPRESS_VERSION;
		}
		
		/**
		 * Gibt den Namen der JSON-Eigenschaft zurück, die für die angegebenen Bildergröße verwendet werden sollte
		 * @param $size
		 */
		function getImageKey($size)
		{
			if($size <= 150)
				return 'thumbnail';
			if($size <= 306)
				return 'low_resolution';
			
			return 'standard_resolution';
		}
		
		/**
		 * @return array Liste mit verfügbaren Lightbox-Effekten
		 */
		function getAvailableEffects()
		{
			return array(	'fancybox' => 'Fancybox', 
							'highslide' => 'Highslide (plugin required)');
		}
				
		function isCurlInstalled() 
		{
			return in_array('curl', get_loaded_extensions());
		}
		
		/**
		 * @return array|bool	Liste mit Fehlermeldungen oder false wenn es keine Probleme gibt
		 */
		function getErrors()
		{
			$errors = array();
			if(!InstagramPlugin::getInstance()->cacheIsWritable())
				$errors[] = sprintf(__('To improve performance of this plugin, it is highly recommended to make the directory wp-content or wp-content/cache writable. For further information click <a target="_blank" href="%s">here</a>' , 'instagram'), 'http://codex.wordpress.org/Changing_File_Permissions');
			if(!InstagramPlugin::getInstance()->isCurlInstalled())
				$errors[] = __('Instapress requires <a href="http://php.net/manual/en/book.curl.php" target="_blank">PHP cURL</a> extension to work properly', 'instagram');
			if(!function_exists('mb_detect_encoding'))
				$errors[] = __('Instapress Geocoding won\'t work unless <a href="http://www.php.net/manual/en/mbstring.installation.php" target="_blank">mbstring</a> is activated', 'instagram');
			if(!extension_loaded('openssl'))
				$errors[] = sprintf(__('Instapress needs to communicate with Instagram\'s API via SSL. If you are having troubles, please %sread that topic.%s'), '<a href="http://wordpress.org/support/topic/fatal-error-plugin-instapress-version-131?replies=6" target="_blank">', '</a>');
				
			return (count($errors) > 0 ? $errors : false);
		}
		
	}
	
	/**
	 * Repräsentiert einen Filter um die Ergebnisse des Instagram API zu filtern
	 * @author tkrammer
	 *
	 */
	class InstapressFeedFilter
	{
		/**
		 * verwendet in_array() zum filtern
		 * @var int
		 */
		const IN_ARRAY = 0;
		/**
		 * verwendet == zum Filtern
		 * @var int
		 */
		const EQUALS = 1;
		
		protected $type = null;
		
		protected $filter = null;
		
		protected $filterName = "";
		
		public function __construct($filterName = null, $filter = null, $type = null)
		{
			$this->filterName = $filterName;
			$this->filter = $filter;
			$this->type = $type;
		}
		
		public function setType($type)
		{
			$this->type = $type;
		}
		
		public function setFilter($filter)
		{
			$this->filter = $filter;
		}
		
		public function setFilterName($name)
		{
			$this->filterName = $name;
		}
		
		/**
		 * Filtert das angegebene Array (aus z.B. $response->data) nach den angegeben Kriterien
		 * @param array $data
		 */
		public function filter(array $data)
		{
			$result = array();
			// Name der zu filternden Eigenschaft
			$filterName = $this->filterName;
			// Bild im Ergebnis behalten?
			$keepImage = false;
			// Alle Images durchgehen
			foreach($data as $image)
			{
				// Filter je nach Typ anwenden
				switch($this->type)
				{
					case self::IN_ARRAY:
						$keepImage = (in_array($this->filter, $image->$filterName));
						break;
						
					case self::EQUALS:
						$keepImage = ($this->filter == $image->$filterName);
						break;
				}
				
				if($keepImage)
				{
					$result[] = $image;
				}
			}
			
			return $result;
		}
	}
		
	/**
	 * Widget registrieren und laden
	 */
	if(!function_exists('load_instagram')):		
		add_action( 'widgets_init', 'load_instagram' );
		function load_instagram() 
		{
			register_widget( 'Instagram_Widget' );
		}
	endif;
		
	/**
	 * Plugin instanzieren
	 */
	if (class_exists('InstagramPlugin')): 
		$InstagramPlugin = InstagramPlugin::getInstance();
		if (isset($InstagramPlugin)) 
		{
			register_activation_hook(__FILE__, array($InstagramPlugin, 'install'));
		}
	endif;
	

	include('widget.php');
