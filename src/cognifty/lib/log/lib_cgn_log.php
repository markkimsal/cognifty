<?php

class Cgn_Log_Manager { }


/**
 * Record visits to a page.
 */
class Cgn_Log_Visitor extends Cgn_Log_Manager {

	/*
	function collectStats() {
	}
	 */

	/**
	 * Record one visit to a page.
	 */
	function record($req, &$tk, &$u) {
	//	$stats = Cgn_Log_Visitor::collectStats();
		$visit = new Cgn_DataItem('cgn_log_visitor');
		$visit->ip_addr = $_SERVER['REMOTE_ADDR'];

		$baseUri = Cgn_ObjectStore::getString("config://template/base/uri");
		$visit->url = substr(str_replace( 'index.php', '', $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']), strlen($baseUri));
		if ($visit->url == '') {
			$visit->url = '/';
		}
//		$visit->url = str_replace( cgn_url(), '', $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
		$visit->user_id = $u->userId;
		$visit->recorded_on = time();
		$defSession =& Cgn_ObjectStore::getObject("object://defaultSessionLayer");
		$visit->session_id = $defSession->getSessionId();
		//swallow up errors if the table doesn't exist
		return  @$visit->save();
		//swallow up errors if the table doesn't exist
		//UPDATE: custom error handler throws away notices.
//		$e = Cgn_ErrorStack::pullError();
	}
}
