<?php

class Cgn_Service {

	var $presenter = 'default';
	var $requireLogin = false;
	var $templateStyle = '';

	function processEvent($e,&$req,&$t) {
		$eventName = $e.'Event';
		if (method_exists($this, $eventName) ) {
			$this->$eventName($req,$t);
		} else {
			Cgn_ErrorStack::throwError('no such event', 580);
		}
	}

	/**
	 * Signal whether or not the user can access
	 * this service given event $e
	 */
	function authorize($e, $u) {
		if ($this->requireLogin && $u->isAnonymous() ) {
			return false;
		}
		return true;
	}

	/**
	 * Signal whether or not the user can perform the
	 *  specified action on the specified data item.
	 */
	function authorizeAction($e, $a, $d, $u) {
		return true;
	}
}



class Cgn_Service_Admin extends Cgn_Service {

	var $requireLogin = true;

	/**
	 * Signal whether or not the user can access
	 * this service given event $e
	 */
	function authorize($e, $u) {
		if (!$this->requireLogin ) {
			return true;
		}

		if (!$u->belongsToGroup('admin') ) {
			return false;
		}
		return true;
	}
}
?>
