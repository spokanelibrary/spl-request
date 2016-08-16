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

		$tickets = array();
		
		$dash = '';
		$dash .= '<div class="panel spl-hero-intranet spl-hero-brand-blue-a">'.PHP_EOL;
		$dash .= '<div class="panel-heading">'.PHP_EOL;
		$dash .= '<h4>';
		$dash .= '<i class="glyphicon glyphicon-cog"></i> ';
		$dash .= 'IT Request Tracker <small>dashboard view</small>';
		$dash .= '</h4>'.PHP_EOL;
		$dash .= '</div>'.PHP_EOL;
		$dash .= '<div class="panel-body">'.PHP_EOL;
		if ( empty($dash) ) {
			'<h4 class="text-success">No open tickets!</h4>'.PHP_EOL;
		} else {
			foreach ( $open as $id => $subject ) {
				$tickets[$id]['subject'] = $subject;
				$tickets[$id]['properties'] = $this->rt->getTicketProperties($id);
				//$tickets[$id]['history'] = $this->rt->getTicketHistory($id);
			}	
			$i = 1;
			foreach ( $tickets as $id => $ticket ) {
				$dash .= '<div class="col-col-sm-6">'.PHP_EOL;
				$dash .= '<div class="panel panel-default">'.PHP_EOL;
				$dash .= '<div class="panel panel-body">'.PHP_EOL;
				$dash .= '<h4>'.$ticket['subject'].'</h4>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;

				$i++;
			}
		}
		$dash .= '</div>'.PHP_EOL;
		$dash .= '</div>'.PHP_EOL;
		
		$dash .= '<pre>'.print_r($tickets, true).'</pre>';

		return $dash;
	}

}

?>