<?php

	/**
	 * Verantwortlich für den TinyMCE-Button im Backend
	 * 
	 * @author tkrammer
	 * @access public
	 */
	class Instapress_TinyMCE 
	{
		
		var $pluginName = 'Instapress';
		
		/**
		 * 
		 * @return Instapress_TinyMCE
		 */
		function Instapress_TinyMCE()  
		{			
			// TinyMCE-Version anpassen wenn ein Plugin hinzugefügt wird
			add_filter('tiny_mce_version', array (&$this, 'tiny_mce_version') );
	
			// init
			add_action('init', array (&$this, 'init') );
		}
	
		/**
		 * 
		 * @return void
		 */
		function init() 
		{
		
			// Don't bother doing this stuff if the current user lacks permissions
			if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) 
				return;
			
			// Nur im Rich-Editor hinzufügen
			if ( get_user_option('rich_editing') == 'true') {
			 
				add_filter("mce_external_plugins", array (&$this, 'mce_external_plugins' ));
				add_filter('mce_buttons', array (&$this, 'mce_buttons' ));
			}
		}
		
		/**
		 * Button einfügen
		 * 
		 * @return $buttons
		 */
		function mce_buttons($buttons) 
		{
			array_push($buttons, 'separator', $this->pluginName);
		
			return $buttons;
		}
		
		/**
		 * TinyMCE-Plugin laden
		 * 
		 * @return array
		 */
		function mce_external_plugins($plugin_array) 
		{    
			$pluginPath = InstagramPlugin::getInstance()->getPluginDirUrl().'tinymce/';
			$plugin_array[$this->pluginName] = $pluginPath.'editor_plugin.js';
			
			return $plugin_array;
		}
		
		/**
		 * Versionsnummer verändern um den Cache zu flushen
		 * 
		 */
		function tiny_mce_version($version) 
		{
			return $version + 100;
		}
		
	}
	
	/**
	 * AJAX ACTIONS
	 */
	
	add_action('wp_ajax_instapress_tinymce', 'instapress_ajax_tinymce');
	
	/**
	 * Instapress TinyMCE Dialog
	 */
	function instapress_ajax_tinymce() 
	{
	
	    if (!current_user_can('edit_pages') && !current_user_can('edit_posts')) 
	    	die(__("Forbidden"));
	        	
	   	include_once(InstagramPlugin::getInstance()->getPluginDirPath().'tinymce/dialog.php');
	    
	    die();	
	}
	
	if(class_exists('Instapress_TinyMCE'))
	{
		$Instapress_TinyMCE = new Instapress_TinyMCE ();			
	}