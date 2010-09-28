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
	public $connectUser = TRUE;

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
		if ($handler == NULL) {
			$handler = new Cgn_Authentication_Handler_Default();
		}

		$this->handler = $handler;
		$this->handler->initContext($configs);
	}

	public function login($username, $password, $params=array()) {
		$this->subject = Cgn_Authentication_Subject::createFromUsername($username, $password);
		$err = $this->handler->authenticate($this->subject);
		if ($err) {
			Cgn_ErrorStack::throwError('LOGIN INVALID', $err);
			return false;
		}

		if ($this->connectUser) {
			$u = Cgn_SystemRequest::getUser();
			$err = $this->handler->connectUser($this->subject, $u);
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

	/**
	 * Save a connection to this user in the local user database.
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function connectUser($subject, $existingUser);

}

class Cgn_Authentication_Handler_Default implements Cgn_Authentication_Handler {

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

	/**
	 * Save a connection to this user in the local user database.
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function connectUser($subject, $existingUser) { return 0; }

}

class Cgn_Authentication_Handler_Ldap implements Cgn_Authentication_Handler {

	public $dsn        = '';
	public $bindBaseDn = '';
	protected $ldap    = NULL;

	public function initContext($ctx) {
		$this->dsn        = $ctx['dsn'];
		$this->bindBaseDn = $ctx['bindDn'];
		$this->authDn     = $ctx['authDn'];
		return $this;
	}

	public function setLdapConn($l) {
		$this->ldap = $l;
	}

	public function getLdapConn() {
		if ($this->ldap === NULL) {
			$this->ldap = new Cgn_Ldap($this->dsn);
		}
		return $this->ldap;
	}

	/**
	 * Return any positive number other than 0 to indicate an error
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function authenticate($subject) {
		Cgn::loadLibrary('lib_cgn_ldap');

		if (!isset($subject->credentials['passwordhash'])) {
			$subject->credentials['passwordhash'] = $this->hashPassword($subject->credentials['password']);
		}

		$rdn = sprintf($this->bindBaseDn, $subject->credentials['username']);
		$ldap = $this->getLdapConn();

//		$ldap->setBindUser($rdn, $subject->credentials['password']);
		$result = $ldap->bind();

		$basedn = $this->authDn;
		//query for attributes
		$res = $ldap->search($basedn, '(userid='.$subject->credentials['username'].')', array('entryUUID', 'mail', 'tzone', 'locale', 'dn', 'entryDN'));

		if ($res === FALSE) {
			//search failed
			$ldap->unbind();
			return 501;
		}

		$ldap->nextEntry();
		$attr = $ldap->getAttributes();
		$ldap->unbind();
		foreach ($attr as $_attr => $_valList) {
			if ($_attr == 'mail')
				$subject->attributes['email'] = $_valList[0];

			if ($_attr == 'entryDN')
				$subject->attributes['dn'] = $_valList[0];
		}

//		$subject->attributes = array_merge($subject->attributes, $results[0]);
		return 0;
	}

	public function hashPassword($p) {
		return md5(sha1($p));
	}

	/**
	 * Save a connection to this user in the local user database.
	 *
	 * @return int  number greater than 0 is an error code, 0 is success
	 */
	public function connectUser($subject, $existingUser) {
		$existingUser->username = $subject->credentials['username'];
		$existingUser->password = $subject->credentials['passwordhash'];
		$existingUser->active_on = time();

		$existingUser->idProviderToken = $subject->attributes['dn'];
		Cgn_User::registerUser($existingUser, 'ldap');
		//tell the subject that what its new ID is
		$subject->attributes['cgn_user_id'] = $existingUser->userId;
		return 0;
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

