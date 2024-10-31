<?php
/*
Plugin Name: Q2W3 Screen Options Hack Demo
Plugin URI: http://www.q2w3.ru/2010/01/25/1204/
Description: This demo plugin shows how to create your own form for Screen Options panel
Version: 1.0
Author: Max Bond
Author URI: http://www.q2w3.ru/
*/

// Plugin initialization

add_action('init', array('q2w3_screen_options_hack_demo','reg_hooks'));

register_deactivation_hook(__FILE__, array('q2w3_screen_options_hack_demo', 'de_activate'));



if (class_exists('q2w3_screen_options_hack_demo', false)) return; // if class already loaded returns control to the main script

// Plugin class. PHP5 OOP style.

class q2w3_screen_options_hack_demo {

	public static $plugin_page_id;
	
	public static $user_option_name = 'sohdemo'; // option name must not contain digits!!!
	
	
	/**
 	 * Deletes all plugin entries in usermeta table
	 *
 	 */
	public static function de_activate() {
	
		global $wpdb;
		
		$wpdb->query('DELETE FROM '. $wpdb->usermeta ." WHERE meta_key = '". self::$user_option_name ."'");
				
	}
	
	/**
 	 * Registers plugin hooks
	 *
 	 */
	public static function reg_hooks() {
			
		if (is_admin()) { // admin actions

			add_action('admin_menu', array(__CLASS__, 'reg_menu')); 

			add_filter('set-screen-option', array(__CLASS__, 'screen_options_save'), 10, 3); // registers function used to prepare Screen Options form data before saving to db

		}
				
	}

	/**
 	 * Creates admin menu entries and registers functions which will display Screen Options and Help panels content
	 *
 	 */	
	public static function reg_menu() {
	
		self::$plugin_page_id = add_submenu_page('plugins.php', 'Q2W3 Screen Options Hack DEMO', 'Q2W3 SOH DEMO', 8, plugin_basename(__FILE__), array(__CLASS__,'settings_page')); // creates menu item under plugins section
		
		add_action('manage_'. self::$plugin_page_id .'_columns', array(__CLASS__, 'screen_options_form')); //  for Screen Options panel
		
		add_action('contextual_help_list', array(__CLASS__, 'help_form')); // for Help panel
						
	}
	
	/**
 	 * Displays plugin settings page
	 *
 	 */
	public static function settings_page() {
		
		$res = '<div class="wrap">'.PHP_EOL;
			
		$res .= '<h2>Q2W3 Screen Options Hack Demo</h2>'.PHP_EOL;
			
		$res .= '<p>Check screen options panel</p>';

		$res .= '</div><!--wrap-->'.PHP_EOL;
			
		echo $res; 	
		
	}
	
	/**
 	 * This function activates and modifies Screen Options panel.
 	 * 
 	 * Do not use this method if WP 2.8 metaboxes are used on your plugin settings page (functions add_meta_box and on_screen_layout_columns)!
 	 * 
 	 * Bind this function to "manage_{plugin_page_id}_columns" hook, 
 	 * where {plugin_page_id} is plugin settings page ID which returned by add_submenu_page function.
 	 * 
 	 * By default Screen Options panel is disabled on plugin settings page. To activate it, function binded to "manage_{plugin_page_id}_columns" hook must return array with at least one key-value pair.
 	 * This array will be used to generate checkboxes in Screen Options, where array keys will be used as IDs and array values will be used as checkbox labels. 
 	 * The main idea is to return only one key-value pare where value will contain html-code of new Screen Options form elements.
 	 * This is possible because WP does not stripe out tags from checkbox labels.
 	 * Checkbox automatically created by WP can be easily hidden with small jQuery code.
	 * 
 	 */
	public static function screen_options_form($columns) {
		
		$checkboxes = array('option_1'=>'Option 1', 'option_2'=>'Option 2', 'option_3'=>'Option 3'); // array of our custom checkboxes (keys used as IDs and values as labels)

		$text_fields = array('option_4'=>'Option 4'); // array of custom text fields 
		
		$default_values = array('option_1'=>'1', 'option_2'=>'0', 'option_3'=>'1', 'option_4'=>'This is a text'); // default values for all elements
		
		$values = get_user_option(self::$user_option_name); // load values from db
		
		if ($values !== false) { // if values exist
			
			$values = unserialize($values); // recreating variables from string
						
		} else { // if there are no values
			
			$values = $default_values; // using default values
			
		}
		
		$column_id = 'screen_options_hack'; // this id will be used to identify and hide checkbox which will be automatically created by WP
		
		$hack = '</label><script type="text/javascript">jQuery("label[for=\''. $column_id .'-hide\']").hide()</script>'.PHP_EOL; // using jQuery to hide unnecessary checkbox 
			
		foreach ($checkboxes as $key=>$name) { // display checkboxes
				
			$hack .= '<div style="float: left; margin-left: 9px">';

			if ($values[$key] == '1') $checked = ' checked="checked"'; else $checked = false;
				
			$hack .= '<input type="checkbox" name="wp_screen_options[value]['. $key .']" value="1" '. $checked .'/>'. $name; // note field name attribute value (do not change it)
			
			$hack .= '</div>';
		
		}
		
		$hack .= '<div style="clear: left; font-size: 0px">&nbsp;</div>'.PHP_EOL;
	
		$hack .= '<h5>Other options</h5><div class="screen-options">';
		
		foreach ($text_fields as $key=>$name) { // display text fields
		
			$hack .= $name .' <input type="text"  name="wp_screen_options[value]['. $key .']" value="'. $values[$key] .'" />'.PHP_EOL; // note field name attribute value (do not change it)
		
		}
		
		$hack .= '<input type="hidden" name="wp_screen_options[option]" value="'. self::$user_option_name .'" />'; // screen options db variable name (make sure it does not contain digits). Do not change field name attribute value!
		
		$hack .= '<input type="submit" class="button" value="Apply" />'; // submit button
		
		$hack .= '</div>';
	
		return array($column_id=>$hack); 
			
	}

	/**
 	 * Prepares Screen Options form data before saving to db
	 *
 	 */
	public static function screen_options_save($value, $option_name, $new_settings) {
		
		// here you can sanitize or change data
		
		$new_settings['option_4'] = htmlspecialchars(trim($new_settings['option_4']), ENT_QUOTES); 
		
		return serialize($new_settings); // return array must be serialized!
		
	}
	
	/**
 	 * Adds help text to plugin settings page Help panel
	 *
 	 */
	public static function help_form($help_content) {
		
		$help_content[self::$plugin_page_id] = 'Help for plugin settings page'; // using self::$plugin_page_id as array key adds your text only to plugin settings page. 		
		
		return $help_content;
		
	}
	
}

?>