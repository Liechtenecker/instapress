<?php
	/**
	 * Widget to display instagram pictures in wpsidebar.
	 * @author tkrammer
	 * @see liechtenecker.at
	 *
	 */
	class Instagram_Widget extends WP_Widget 
	{
		
		function Instagram_Widget()
		{
			// Widget-Optionen
			$wOptions = array( 
								'classname' => 'instagram', // Name 
								'description' => __('Displays your instagram pictures.', 'instagram') // Anzeigetext 
			);
			// Control-Optionen
			$cOptions = array( 
								'width' => 300, 
								'height' => 350, 
								'id_base' => 'instagram-widget' 
			);
			
			// Widget instanzieren
			$this->WP_Widget( 'instagram-widget', __('Instagram', 'instagram'), $wOptions, $cOptions );
			
			// Stylesheets ergänzen
			add_action('init', array(&$this, 'stylesheet'));
		}
		
		/**
		 * Zeichnet das Widget in der Sidebar
		 * @param $args
		 * @param $instance
		 */
		function widget( $args, $instance ) 
		{						
			extract( $args );
			$title = apply_filters('widget_title', $instance['title']);
			
			// HTML vor dem Widget
			echo $before_widget; 
			// HTML vor dem Titel + Titel + HTML nach dem Titel
			echo $before_title . $title . $after_title;
			
			try
			{				
				// ID des Widgets
				//$args['widget_id'] 
				
				// Default-Größe ist 150x150
				$picSize = (intval($instance['size']) > 0) ? intval($instance['size']) : 150;
				
				$nextMaxId = '';
				
				$piccounter = 1;
				
				do
				{
				
					$max_id = $nextMaxId;
					
					if(!empty($instance['address']))
					{
						// Location-Feed
						$data = InstagramPlugin::getLocationBasedFeed($instance['address']);
					}
					else
					{
						if($instance['username'] == '')
							$uid = '';
						else if($instance['username'] == 'myfeed')
							$uid = $instance['username'];
						else
							$uid = $instance['userid'];
							
						// Feed ab der gegebenen max_id laden und nächsten max_id holen
						if(empty($instance['tag']))
							$data = InstagramPlugin::getFeedByUserId($uid, $max_id, &$nextMaxId);
						// Feed eines Users laden und nach Tag filtern
						else if(!empty($instance['tag']) && !empty($uid))
							$data = InstagramPlugin::getFeedByUserId($uid, $max_id, &$nextMaxId, 0, new InstapressFeedFilter('tags', $instance['tag'], InstapressFeedFilter::IN_ARRAY));
						else
							$data = InstagramPlugin::getFeedByTag($instance['tag'], $max_id, &$nextMaxId);
					}
					
					if(count($data) > 0)
					{
						
						if($instance['randomize'])
						{
							shuffle($data);
						}
						
						foreach($data as $obj)
						{
							// Nur die erste X Bilder anzeigen?
							if(intval($instance['piccount']) > 0 && $piccounter > $instance['piccount'])
								break;
		
							// Soll der Titel des Fotos gezeigt werden
							if($instance['show-title'])
							{
								$title = htmlentities(utf8_decode($obj->caption->text));
							}
							else
							{
								$title = "";
							}
								
							// Auf aktivierte Effekte überprüfen
							switch($instance['effect'])
							{
								// jQuery Fancybox
								case 'fancybox':
									$image = '<a href="'.$obj->images->standard_resolution->url.'" rel="instagram-images" title="'.$title.'">';
									break;
								case 'highslide':
									$image = '<a href="'.$obj->images->standard_resolution->url.'" class="highslide instapress-highslide" title="'.$title.'">';
									break;
								// Ohne Effekt
								default:
									$image = '<a href="'.$obj->link.'" target="_blank">';
									break;
							}
							
							// Welche Property soll für das Image verwendet werden
							$imageKey = InstagramPlugin::getImageKey($picSize);
							
							$image .= '<img src="'.$obj->images->$imageKey->url.'" ';
							// Überprüfen, ob width und height deaktiviert wurden (für responsive Designs)
							if(!InstagramPlugin::getInstance()->imageAttributesDisabled())
							{
								$image .= 'width="'.$picSize.'" height="'.$picSize.'" ';
							}
							$image .= 'border="0" title="'.$title.'" /></a>';
								
							// Platzhalter im HTML-Text ersetzen
							echo str_replace(	array('%index%', '%image%', '%title%'), 
												array($piccounter++, $image, $title), 
												$instance['image-container']);
						}
					}
					else
					{
						break;
					}
				}
				while($nextMaxId && ($piccounter <= $instance['piccount'] || intval($instance['piccount']) == 0));
				
				if(InstagramPlugin::getInstance()->mayShowBacklink())
				{
					echo InstagramPlugin::getBacklink();
				}
				
				echo '<span class="instagram-images-clear version-'.InstagramPlugin::getVersion().'">&nbsp;</span>';
			}
			catch(Zend_Http_Client_Adapter_Exception $ex)
			{
				_e('Instagram API did not respond.', 'instagram');
			}
			
			// HTML nach dem Widget 
			echo $after_widget;
		}

		/**
		 * Fügt die notwendigen Stylesheets im Header hinzu
		 */
		function stylesheet()
		{
			// Default-Stylesheet
			wp_enqueue_style('instapress', plugins_url('/instapress.css', __FILE__));
			
			if(InstagramPlugin::getInstance()->effectsEnabled()) 
			{
				// Fancybox
				wp_register_style('fancybox', plugins_url('/fancybox/jquery.fancybox.css', __FILE__), null, "1.3.4");
				wp_enqueue_style('fancybox');
			}
		}
		
		/**
		 * Fügt die notwendigen Javascript-Dateien im Header hinzu
		 */
		function javascript()
		{
			if(!is_admin())
			{
				// Fancybox
				wp_register_script('fancybox', plugins_url('/fancybox/jquery.fancybox-1.3.4.pack.js', __FILE__), array('jquery'), "1.3.4");
	
				// Default JS
				wp_enqueue_script('instapress', plugins_url('/instapress.js', __FILE__), array('jquery', 'fancybox'), InstagramPlugin::getVersion(), true);
			}
		}
		
		/**
		 * --------------------------- B A C K E N D ----------------------------
		 */
		
		/**
		 * Speichert die Einstellungen aus dem Backend
		 * @param $new_instance Instanz mit der neuen Konfiguration
		 * @param $old_instance Instanz mit der alten Konfiguration
		 * 
		 * @return array Instanz die gepseichert wurde
		 */
		function update( $new_instance, $old_instance ) 
		{
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['username'] = strip_tags( $new_instance['username'] );
			$instance['tag'] = strip_tags( $new_instance['tag'] );
			$instance['userid'] = InstagramPlugin::getUserIdByName(strip_tags( $new_instance['username'] ));
			$instance['address'] = $this->getLatLng($new_instance['address']);
			$instance['size'] = strip_tags( $new_instance['size'] );
			$instance['piccount'] = strip_tags( $new_instance['piccount'] );
			$instance['image-container'] = $new_instance['image-container'];
			$instance['effect'] = $new_instance['effect'];
			$instance['show-title'] = isset($new_instance['show-title']);
			$instance['randomize'] = isset($new_instance['randomize']);
			
			if(!intval($instance['size']))
			{
				$instance['size'] = "";
			}
			
			
			return $instance;
		}
		
		/**
		 * Erstellt das Formular für die Einstellungen im Backend
		 * @param $instance
		 */
		function form( $instance ) 
		{
			$defaults = array( 	'title' => __('Instagrams', 'instagram'), 
								'username' => __('', 'instagram'), 
								'size' => __('150', 'instagram'),
								'image-container' => __('<div class="instagram-image" id="instagram-image-%index%">%image%</div>')
			);
			
			
			$instance = wp_parse_args( (array) $instance, $defaults );
			
			// Wenn noch kein Access-Token gespeichert wurde, den User darauf hinweisen
			if(!InstagramPlugin::getInstance()->getAccessToken())
			{
				?>
				<div><?php echo sprintf(__('Before you can use this widget, update your <a href="%s">Instagram settings</a>!'), InstagramPlugin::getInstance()->getPluginUrl()); ?></div>
				<?php
			}
			
			if(intval($instance['userid']))
				echo __('Display Instagrams from User: ').$instance['userid'];
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'instagram'); ?></label>
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e('Picture size:', 'instagram'); ?>
					<input id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" type="text" size="3" value="<?php echo $instance['size']; ?>" /> px
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e('Username:', 'instagram'); ?></label>
				<input id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo $instance['username']; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'tag' ); ?>"><?php _e('Tag (Currently only one tag.):', 'instagram'); ?></label>
				<input id="<?php echo $this->get_field_id( 'tag' ); ?>" name="<?php echo $this->get_field_name( 'tag' ); ?>" type="text" value="<?php echo $instance['tag']; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'address' ); ?>"><?php _e('Address/Coordinates:', 'instagram'); ?></label>
				<input id="<?php echo $this->get_field_id( 'address' ); ?>" name="<?php echo $this->get_field_name( 'address' ); ?>" type="text" value="<?php echo (!empty($instance['address']) ? implode(',', $instance['address']) : ''); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'piccount' ); ?>"><?php _e('Pics:', 'instagram'); ?></label>
				<select id="<?php echo $this->get_field_id( 'piccount' ); ?>" name="<?php echo $this->get_field_name( 'piccount' ); ?>">
					<option value="0"<?php echo ($instance['piccount']== 0 ? ' selected="selected"' : ''); ?>><?php _e('All', 'instagram'); ?></option>
					<?php for($i = 1; $i <= 200; $i++): ?>
					<option value="<?php echo $i ?>"<?php echo ($instance['piccount']== $i ? ' selected="selected"' : ''); ?>><?php echo $i ?></option>
					<?php endfor; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'effect' ); ?>"><?php _e('Effect:', 'instagram'); ?></label>
				<select id="<?php echo $this->get_field_id( 'effect' ); ?>" name="<?php echo $this->get_field_name( 'effect' ); ?>">
					<option value="0"<?php echo ($instance['effect']== 0 ? ' selected="selected"' : ''); ?>><?php _e('None', 'instagram'); ?></option>
					<?php
					$effects = InstagramPlugin::getAvailableEffects();
					foreach($effects as $key=>$effect):
					?>
					<option value="<?php echo $key ?>"<?php echo ($instance['effect']== $key ? ' selected="selected"' : ''); ?>><?php echo $effect ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'show-title' ); ?>"><?php _e('Show title:', 'instagram'); ?></label>
				<input type="checkbox" id="<?php echo $this->get_field_id( 'show-title' ); ?>" name="<?php echo $this->get_field_name( 'show-title' ); ?>"<?php echo ($instance['show-title'] ? ' checked="checked"' : ''); ?> />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'randomize' ); ?>"><?php _e('Random order:', 'instagram'); ?></label>
				<input type="checkbox" id="<?php echo $this->get_field_id( 'randomize' ); ?>" name="<?php echo $this->get_field_name( 'randomize' ); ?>"<?php echo ($instance['randomize'] ? ' checked="checked"' : ''); ?> />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'image-container' ); ?>"><?php _e('Image container:', 'instagram'); ?></label>
				<textarea id="<?php echo $this->get_field_id( 'image-container' ); ?>" name="<?php echo $this->get_field_name( 'image-container' ); ?>"><?php echo htmlentities2($instance['image-container']); ?></textarea>
			</p>
		<?php
		}
		
		
		/**
		 * 
		 * @param $address sting Addresse oder Koordinaten
		 * 
		 * @return array Koordinaten (0 = lat, 1 = lng)
		 */
		function getLatLng($address)
		{
			// Den PowerHour Geocoder laden
			require_once("Geocoder.php");			
			// Wenn es sich um Koordinatenhandelt
			if(preg_match('/\d+\.d+,\d+\.\d+/', $address) > 0)
			{
				$result = explode(',', $address);
			}
			else if(strlen($address) > 0)
			{
				$result = array();
				// Ansonsten versuchen die Adresse zu geocoden
				try
				{
					// Instanz erzeugen
					$geocoder = new PowerHour_Geocoder();
					// Versuchen die Adresse zu codieren
					$geocoder->mapFromAddress($address);
					// Ergebnis in Array speichern
					$result[0] = $geocoder->getLatitude();
					$result[1] = $geocoder->getLongitude();
				}
				catch(Exception $ex){}
			}
			
			return $result;
		}
		
	}
?>