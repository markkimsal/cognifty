<?php

/**
 * Cgn_Authentication_Mgr
 *
 * Handles loading of specific handlers.
 */
class Cgn_Authentication_Mgr {


	public $handler  = NULL;
	public $ctx      = NULL;
	public $subject  = NULL;
	public $configs  = array();

	/**
	 * Initialize a new handler for the given context.
	 * If no context is supplied, a default handler will be created.
	 * The default handler is based on the local mysql installation.
	 *
	 * @param  Array $ctx  Dynamic array of attributes
	 * @param  Object $handler  A custom Authentication Handler if the default should not be used
	 * @return Object Cgn_Authentication_Module
	 */
	public function __construct($configs = array(), $handler = NULL) {
		$this->handler = new Cgn_Authentication_Handler_Database();
		$this->handler->initContext($configs);
	}

	public function login($username, $password, $params=array()) {
		$this->subject = Cgn_Authentication_Subject::createFromUsername($username, $password);
		$err = $this->handler->authenticate($this->subject);
		if ($err) {
			Cgn_ErrorStack::throwError('LOGIN INVALID', $err);
			return false;
		}
		return true;
	}

	/**
	 * Return the subject of this login
	 */
	public function getSubject() {
		return $this->subject;
	}

	public static function createFromContext($ctx=NULL) {
	}
}

interface Cgn_Authentication_Handler { 

	/**
	 * Must return a reference to this
	 *
	 * @return Object Cgn_Authentication_Handler
	 */
	public function initContext($ctx);

	/**
	 * Return any positive number other than 0 to indicate an error
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function authenticate($subject);
}

class Cgn_Authentication_Handler_Database implements Cgn_Authentication_Handler {

	public function initContext($ctx) {
		return $this;
	}


	/**
	 * Return any positive number other than 0 to indicate an error
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function authenticate($subject) {

		if (!isset($subject->credentials['passwordhash'])) {
			$subject->credentials['passwordhash'] = $this->hashPassword($subject->credentials['password']);
		}

		$finder = new Cgn_DataItem('cgn_user');
		$finder->andWhere('username', $subject->credentials['username']);
		$finder->andWhere('password', $subject->credentials['passwordhash']);
		$finder->_rsltByPkey = FALSE;
		$results = $finder->findAsArray();

		if (!count($results)) {
			return 501;
		}

		if( count($results) !== 1) {
			//too many results, account is not unique
			return 502;
		}

		$subject->attributes = array_merge($subject->attributes, $results[0]);
		return 0;
	}

	public function hashPassword($p) {
		return md5(sha1($p));
	}

}

class Cgn_Authentication_Subject {

	public $credentials = array();
	public $attributes  = array();
	public $domain      = '';
	public $domainId    = 0;

	public static function createFromUserName($uname, $pass) {

		$subj = new Cgn_Authentication_Subject();
		$subj->credentials['username'] = $uname;
		$subj->credentials['password']   = $pass;
		return $subj;
	}
}

