<?php

/**
 * parent class
 * @abstract
 * 
 * intended to be subclassed by different session handlers
 * 
 */

class Cgn_Session {


	var $sessionID = '';
	var $started = FALSE;

	function Cgn_Session() {

	}

	function start() {

	}

	function close() { 

	}

	function set($key, $val) {

	}

	function get($key) { 

	}
	
	function getSessionID() { 

	}

	function isSessionStale() {

	}

	function needsReAuth() {

	}
}


class Cgn_Session_Simple {


	function Cgn_Session_Simple() { 
		$this->start();
	}

	function start() {
		$this->started = TRUE;
		session_start();
		$this->sessionID = session_id();
	}

	function close() { 
		session_write_close();
	}

	function set($key, $val) {
		$_SESSION[$key] = $val;
	}

	function get($key) { 
		return $_SESSION[$key];
	}
}
?>
