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
	var $timeout          = 3600;  //one hour timeout
	var $inactivityReAuth = 600;   //10 min timeout for auth
	var $authTime = -1;
	var $touchTime = -1;
	var $lastTouchTime = -1;

	function Cgn_Session() { }

	function start() {
		session_name($this->sessionName);
		//if ($this->started) Cgn_ErrorStack::throwError('double session');
		if ($this->started) trigger_error('double session');
			$this->started = TRUE;
		ini_set('session.gc_maxlifetime',7200); //4 hours
		@session_start();
		$this->sessionId = session_id();
		$this->touch();
	}

	function close() { 
		$this->started = FALSE;
	}

	function set($key, $val) { }

	function setArray($a) { }

	function get($key) { }

	function append($key, $val) { }
	
	function getSessionId() { 
		return $this->sessionId;
	}

	function isSessionStale() { }

	/**
	 * Set a usage time stamp for this session.
	 */
	function touch($t=0) {
		if ($this->touchTime === -1) {
			$this->begin();
		}

		if ($this->touchTime !== -1) {
			$this->lastTouchTime = $this->touchTime;
		}

		if ($t == 0) {
			$this->touchTime = time();
		} else {
			$this->touchTime = $t;
		}
		$this->set('_touch', $this->touchTime);
		$this->set('_lastTouch', $this->lastTouchTime);
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

		$this->set('_auth', $this->authTime);
	}

	/**
	 * Return true if the activity has been too long and
	 * the system wants re-authorization.  The session is still
	 * active, but this function recommends asking for a new
	 * password for more security.
	 */
	function needsReAuth() { 
		$lastTouch = $this->lastTouchTime;
		if ($lastTouch === -1) {
			$lastTouch = $this->touchTime;
		}
		if ( time() - $lastTouch >= $this->inactivityReAuth ) {
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
		$this->set('_lastTouch', $this->lastTouchTime);
	}

	/**
	 * This function pulls special variables out of the session storage.
	 *
	 * Opposite of commit, like magic __wakeup.
	 */
	function begin() {
		$touch = $this->get('_touch');
		if ($touch !== NULL) {
			$this->touchTime = $touch;
		}
		$auth = $this->get('_auth');
		if ($auth !== NULL) {
			$this->authTime = $auth;
		}
		$last = $this->get('_lastTouch');
		if ($last !== NULL) {
			$this->lastTouchTime = $last;
		}
	}

	/**
	 * Get Session Obj
	 *
	 * Return a reference to the default session layer object
	 */
	function &getSessionObj() {
		$mySession =& Cgn_ObjectStore::getObject("object://defaultSessionLayer");
//		$mySession->set('userId',$this->userId);
		return $mySession;
	}

	/**
	 * Completely erase a session
	 */
	function erase() {
		$this->clearAll();
		session_destroy();
		setcookie($this->sessionName,'');
		$this->started = FALSE;
	}

	/**
	 * Clear all session variables
	 */
	function clearAll() {
		$this->authTime = 0;
		$this->touchTime = 0;
		$this->lastTouchTime = 0;
	}

	/**
	 * If serialized, we won't call the constructor again
	 */
	public function __wakeup() {
		$this->started = FALSE;
	}
}


class Cgn_Session_Simple extends Cgn_Session {


	function start() { 
		if (Cgn_ObjectStore::hasConfig('config://default/session/path')) {
			session_save_path(Cgn_ObjectStore::getConfig('config://default/session/path'));
		}
		parent::start();
		$this->clear('_messages');
		//move saved session messages into regular messages
		if (isset($_SESSION['_sessionMessages']) && is_array($_SESSION['_sessionMessages']) ) {
			foreach ($_SESSION['_sessionMessages'] as $msg) {
				$this->append('_messages',$msg);
			}
		}
		$this->clear('_sessionMessages');
	}

	function close() { 
		session_write_close();
	}

	function clear($key) {
		unset($_SESSION[$key]);
	}

	function set($key, $val) {
		$_SESSION[$key] = $val;
	}

	function get($key) { 
		if (isset($_SESSION[$key])) {
			return @$_SESSION[$key];
		} else {
			return NULL;
		}
	}

	function append($key, $val) {
		$_SESSION[$key][] = $val;
	}

	function setArray($a) {
		foreach ($a as $key=>$val) {
			$_SESSION[$key] = $val;
		}
	}

	/**
	 * Clear all session variables
	 */
	function clearAll() {
		parent::clearAll();
		foreach ($_SESSION as $k=>$v) {
			unset($_SESSION[$k]);
		}
	}

}

/**
 * Write to DB
 */
class Cgn_Session_Db extends Cgn_Session_Simple {

	var $data = array();

	function start() { 
		session_set_save_handler(array(&$this, 'open'),
					array(&$this, 'close'),
					array(&$this, 'read'),
					array(&$this, 'write'),
					array(&$this, 'destroy'),
					array(&$this, 'gc'));
		register_shutdown_function('session_write_close');
		parent::start();

		/*
		$this->clear('_messages');
		//move saved session messages into regular messages
		if (isset($this->data['_sessionMessages']) && is_array($this->data['_sessionMessages']) ) {
			foreach ($this->data['_sessionMessages'] as $msg) {
				$this->append('_messages',$msg);
			}
		}
		$this->clear('_sessionMessages');
		 */
	}

	function destroy($id) {
		if (strlen($id) < 1) { return true; }
		//return false;
		include_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');
		$sess = new Cgn_DataItem('cgn_sess', 'cgn_sess_key');
//		$sess->andWhere('cgn_sess_key',$id);
		$sess->delete($id);
		return true;
	}

	function gc() {
		return true;
	}

	function read($id) {
		@include_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');
		$sess = new Cgn_DataItem('cgn_sess');
		$sess->andWhere('cgn_sess_key',$id);
		$sess->_rsltByPkey = false;
		$sessions = $sess->find();
		if (count($sessions)) {
			$sess = $sessions[0];
			if ( strlen($sess->data) ) {
				return (string) $sess->data;
			}
			return '';
		}
		return false;
	}


	function open($id) {
		return true;
	}

	function close() {
		$this->commit();
		return true;
	}

	function write ($id, $sess_data) {
		$sess = new Cgn_DataItem('cgn_sess');
		$sess->andWhere('cgn_sess_key', $id);
		$sess->_rsltByPkey = false;
		$sessions = $sess->find();
		if (count($sessions)) {
			$sess = $sessions[0];
		} else {
			$sess = new Cgn_DataItem('cgn_sess');
			$sess->cgn_sess_key = $id;
		}
		$sess->data = $sess_data;
		$sess->saved_on = time();
		$sess->save();
		return true;
	}
}
?>
