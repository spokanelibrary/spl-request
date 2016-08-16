<?php

/**
 * @package SPL_Request
 * @version 0.1
 */

/*
Plugin Name: SPL Request
Plugin URI: http://www.spokanelibrary.org/
Description: Hooks the <code>[spl_request]</code> shortcode to show a Request Tracker dashboard.
Author: Sean Girard
Author URI: http://seangirard.com
Version: 0.1
*/

function wp_spl_request($config=null) {
	
	return 'This is a test.';
}

add_shortcode('spl_request', 'wp_spl_request');


?>