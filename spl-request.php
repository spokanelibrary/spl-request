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
	var $url = "http://rt.spokanelibrary.org";

	function __construct() {
		require_once 'RTPHPLib/RequestTracker.php';

		
		$user = getenv('SPL_RT_USER');
		$pass = getenv('SPL_RT_PASS');

		$this->rt = new RequestTracker($this->url, $user, $pass);
	}

	public function getDashboard() {
		$open = $this->rt->search("Queue='Automation'AND(Status='new'ORStatus='open'ORStatus='stalled')",'-Created', 's');
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
				$tickets[$id]['history'] = $this->rt->getTicketHistory($id);
			}	
			$i = 0;
			foreach ( $tickets as $id => $ticket ) {
				switch ( $ticket['properties']['Status'] ) {
					case 'new':
						$status = 'danger';
						break;
					case 'open':
						$status = 'warning';
						break;
					case 'resolved':
						$status = 'success';
						break;
					default:
						$status = 'info';
						break;
				}

				if ( 0 == $i%2  ) {
					$dash .= '<div class="row">'.PHP_EOL;	
				}
				$dash .= '<div class="col-sm-6">'.PHP_EOL;
				$dash .= '<div class="panel panel-default">'.PHP_EOL;
				$dash .= '<div class="panel-body">'.PHP_EOL;
				
				$dash .= '<div class="row">'.PHP_EOL;
				$dash .= '<div class="col-sm-2">'.PHP_EOL;
				$dash .= '<h5 class=""><span class="label label-'.$status.'">'.$ticket['properties']['Status'].'</span></h5>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '<div class="col-sm-9">'.PHP_EOL;
				$dash .= '<h5 class="text-right"><small class="help-block">'.$ticket['properties']['Created'].'</small></h5>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '<div class="col-sm-1">'.PHP_EOL;
				$dash .= '<a rel="external" class="btn btn-default btn-xs pull-right" href="'.$this->url.'/Ticket/Display.html?id='.$id.'"><i class="glyphicon glyphicon-wrench"></i></a>';
				$dash .= '</div>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				
				$dash .= '<div class="row">'.PHP_EOL;
				$dash .= '<div class="col-sm-12">'.PHP_EOL;
				$dash .= '<h5>';
				$dash .= '<a href="#" data-toggle="modal" data-target="#spl-rt-'.$id.'"><i class="glyphicon glyphicon-share"></i> '.$ticket['subject'].'</a>';
				$dash .= '</h5>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;

				$dash .= '<div class="row">'.PHP_EOL;
				$dash .= '<div class="col-sm-4">'.PHP_EOL;
				$dash .= '<h6><span class="text-muted">Owner:</span> '.$ticket['properties']['Owner'].'</h6>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '<div class="col-sm-8">'.PHP_EOL;
				$dash .= '<h6><span class="text-muted">Requestor:</span> '.$ticket['properties']['Creator'].'</h6>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;



				$dash .= '<div class="modal fade" id="spl-rt-'.$id.'" tabindex="-1" role="dialog">'.PHP_EOL;
				$dash .= '<div class="modal-dialog" role="document">'.PHP_EOL;
				$dash .= '<div class="modal-content">'.PHP_EOL;
				$dash .= '<div class="modal-header">'.PHP_EOL;
				$dash .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
				$dash .= '<h4 class="modal-title">'.$ticket['subject'].' ['.$id.']</h4>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '<div class="modal-body">'.PHP_EOL;
				foreach ( $ticket['history'] as $c=>$comment ) {
					if ( !in_array($comment['Type'], array('Set', 'SetWatcher', 'Status', 'AddLink'))  ) {	
						$dash .= '<h5 class="text-right"><span class="label label-primary">'.$comment['Type'].'</span></h5>'.PHP_EOL;
						$dash .= '<h4>'.$comment['Creator'].' <small>'.$comment['Created'].'</small></h5>'.PHP_EOL;
						
						$text = $comment['Content'];
						$text = preg_replace("/[\r\n]/", '<span class="text-danger">\r\n</span>', $text);

						$text = preg_replace("/[\r\n\r\n]/", '<br>', $text);

						//$text = preg_replace("/[\r\n]+/", "\n", $text);
						//$text = preg_replace('/(\r\n|\n|\r){3,}/', "$1$1", $text);
						//$text = nl2br($text);
						$dash .= '<p style="border-left:1px solid #666; padding-left: 6px;">'.$text.'</p>'.PHP_EOL;
					}
				}
				$dash .= '</div>'.PHP_EOL;
				$dash .= '<div class="modal-footer">'.PHP_EOL;
				$dash .= '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				

				$dash .= '</div>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				$dash .= '</div>'.PHP_EOL;
				if ( 0 != $i%2  ) {
					$dash .= '</div>'.PHP_EOL;	
				}
				$i++;
			}
		}
		$dash .= '</div>'.PHP_EOL;
		$dash .= '</div>'.PHP_EOL;

		//$dash .= '<pre>'.print_r($tickets, true).'</pre>';

		return $dash;
	}

}

?>