<?php

/**
 * parent class
 * @abstract
 * 
 * intended to be subclassed by different session handlers
 * 
 */

class Cgn_Session {


	var $sessionId = '';
	var $started = FALSE;
	var $sessionName = 'CGNSESSION';
	var $timeout = 3600; //one hour timeout
	var $authTime = -1;
	var $touchTime = -1;

	function Cgn_Session() { }

	function start() {
       		$this->started = TRUE;
		session_start();
		$this->sessionId = session_id();
		$this->touch();
	}

	function close() { }

	function set($key, $val) { }

	function setArray($a) { }

	function get($key) { }
	
	function getSessionId() { }

	function isSessionStale() { }

	/**
	 * Set a usage time stamp for this session.
	 */
	function touch($t=0) {
		if ($t == 0) {
			$this->touchTime = time();
		} else {
			$this->touchTime = $t;
		}
	}

	/**
	 * Set the last time a session was authorized, as in
	 * a user submitting a password
	 */
	function setAuthTime($t=0) { 
		if ($t == 0) {
			$this->authTime = time();
		} else {
			$this->authTime = $t;
		}
	}

	/**
	 * Return true if the activity has been to long and
	 * the system wants re-authorization.  The session is still
	 * active, but this function recommends asking for a new
	 * password for more security.
	 */
	function needsReAuth() { 
		if ( (time() - $this->get('_touch')) >= $this->timeout ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Flush needed variables to the session storage.
	 * For DB based sessions these values might need to go
	 * into the table and not the session itself.
	 */
	function commit() { 
		$this->set('_auth', $this->authTime);
		$this->set('_touch', $this->touchTime);
	}
}


class Cgn_Session_Simple extends Cgn_Session {


	function Cgn_Session_Simple() { 
		session_name($this->sessionName);
		$this->start();
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

	function setArray($a) {
		foreach ($a as $key=>$val) {
			$_SESSION[$key] = $val;
		}
	}
}
?>
