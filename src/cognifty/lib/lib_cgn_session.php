<?php

/**
 * Parent class for all session plugins: Cgn_Session_Simple, Cgn_Session_Db
 * 
 * To get access to the current session you can call
 * <code>
 * $req->getSession();
 * </code>
 * or
 * <code>
 * Cgn_Session::getSessionObj();
 * </code>
 *
 * You can access session variables directly from the request object like this
 * <code>
 * $req->setSessionVar('myvar', $myval);
 * $req->cleaSessionVar('myvar');
 * $req->getSessionVar('myvar');
 * </code>
 *
 * @abstract
 */

class Cgn_Session {

	var $sessionId = '';
	var $started = FALSE;
	var $sessionName = 'CGNSESSION';
	var $timeout          = 88200;  //24.5 hour timeout
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
		ini_set('session.gc_maxlifetime', $this->timeout);
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
	static function &getSessionObj() {
		$mySession =& Cgn_ObjectStore::getObject("object://defaultSessionLayer");
		return $mySession;
	}

	/**
	 * Completely erase a session
	 */
	function erase() {
		$this->clearAll();
		session_destroy();
		setcookie($this->sessionName, '');
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

	public function isNew() {
		return $this->lastTouchTime === -1;
	}
}


/**
 * The Simple session object handles "normal" sessions with the $_SESSION 
 * super global and stoes them however your PHP installation would.
 *
 * This session plugin provides consistency in design when you are not using 
 * the DB session.
 */
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
 * The Db session plugin uses php's session_set_save_handler to 
 * hook in to the session lifecycle and stores session information 
 * in the database.
 *
 * Turn on the database session in boot/local/core.ini
 *
 * [object]
 * session.handler=@lib.path@/lib_cgn_session.php:Cgn_Session_Db:defaultSessionLayer
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
		//some php.ini's don't use the gc setting, they assume
		//that a cron will clean up /var/lib/php/
		//We will set a gc func here 10% of the time
		if (rand(1,10) > 9)
			register_shutdown_function( array(&$this, 'gc') );

		parent::start();
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

	function gc($maxlifetime=0) {
		$sess = new Cgn_DataItem('cgn_sess');
		$sess->andWhere('saved_on', (time()- $this->timeout), '<');
		$sess->delete();
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
