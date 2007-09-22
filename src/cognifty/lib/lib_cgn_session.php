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
		//if ($this->started) Cgn_ErrorStack::throwError('double session');
		if ($this->started) trigger_error('double session');
       		$this->started = TRUE;
		session_start();
		$this->sessionId = session_id();
		$this->touch();
	}

	function close() { }

	function set($key, $val) { }

	function setArray($a) { }

	function get($key) { }

	function append($key, $val) { }
	
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
		$this->set('_touch', $this->touchTime);
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
}


class Cgn_Session_Simple extends Cgn_Session {


	function Cgn_Session_Simple() { 
		session_name($this->sessionName);
		$this->start();
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
		return @$_SESSION[$key];
	}

	function append($key, $val) {
		$_SESSION[$key][] = $val;
	}

	function setArray($a) {
		foreach ($a as $key=>$val) {
			$_SESSION[$key] = $val;
		}
	}
}

/**
 * Write to DB
 */
class Cgn_Session_Db extends Cgn_Session_Simple {

	var $data = array();

	function Cgn_Session_Db() { 
		session_set_save_handler(array(&$this, 'open'),
					array(&$this, 'close'),
					array(&$this, 'read'),
					array(&$this, 'write'),
					array(&$this, 'destroy'),
					array(&$this, 'gc'));
		register_shutdown_function('session_write_close');
		session_name($this->sessionName);
		$this->start();

		$this->clear('_messages');
		//move saved session messages into regular messages
		if (isset($this->data['_sessionMessages']) && is_array($this->data['_sessionMessages']) ) {
			foreach ($this->data['_sessionMessages'] as $msg) {
				$this->append('_messages',$msg);
			}
		}
		$this->clear('_sessionMessages');
	}

	function destroy($id) {
		return false;
		include_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');
		$sess = new Cgn_DataItem('cgn_sess');
//		$sess->andWhere('cgn_sess_key',$id);
		$sess->delete($id);

	}

	function gc() {
		return true;
	}

	function read($id) {
		include_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');
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
