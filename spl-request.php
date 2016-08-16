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
	$request = new SPL_Request();

	return $request->getDashboard();
}

add_shortcode('spl_request', 'wp_spl_request');

class SPL_Request {
	var $rt;

	function __construct() {
		require_once 'RTPHPLib/RequestTracker.php';

		$url = "http://rt.spokanelibrary.org/";
		$user = getenv('SPL_RT_USER');
		$pass = getenv('SPL_RT_PASS');

		$this->rt = new RequestTracker($url, $user, $pass);
	}

	public function getDashboard() {
		$open = $this->rt->search("Queue='Automation'ANDStatus='open'",'-Created', 's');
		unset($open['']);

		$keys = explode(',',array_keys($open));

		return '<pre>'.print_r($keys, true).'</pre>';
	}

}

?>