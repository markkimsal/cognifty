<?php

global $cgnUser;
$cgnUser = new Cgn_User();

/**
 * Cgn_User class
 *
 * A user is defined only as a username, email, and password.
 * Extra info is stored in table 'profile'.  Cgn_User::saveSession()
 * should only allow one login under that username at a time.  * Call Cgn_User::bindSession() from any login script to log-in a user.
 */
class Cgn_User {
	 
	var $username = "anonymous";
	var $password;
	var $email;
	var $userId =0;
	 
	var $sessionvars;
	// store session data in this array; (e.x.: $u->sessionvars['voted'] = date();)
	var $groups = array();
	// array of group membership groups["public"], groups["admin"], etc.
	var $perms;
	// nested arrays of available services (key) and actions (values)
	var $loggedIn = FALSE;

	//account object
	var $account = NULL;

	//flag for lazy loading
	var $_accountLoaded = FALSE;

	/**
	 * Double encrypt the password
	 */
	function setPassword($p) {
		$this->password = $this->_hashPassword($p);
	}

	/**
	 * Simple getter
	 */
	function getUsername() {
		return $this->username;
	}

	/**
	 * Return a name suitable for display on a Web site.
	 *
	 * Try to load the user's account.  Combine first and last names if available.
	 * If no account is avaiable, compare the username and emails.  If they are the 
	 * same, return the first half of the email (username@example.com).
	 * If they are different, return the username by itself.
	 *
	 * @return String name for the user suitable for displaying
	 */
	function getDisplayName() {
		$this->fetchAccount();

		//check the account object
		if ($this->account->firstname != '' ||
			$this->account->lastname != '') {
				return $this->account->firstname. ' '.$this->account->lastname;
		}
		//check if emails are the same as usernames
		if ($this->username === $this->email && strpos($this->username, '@')) {
			return substr($this->username, 0, strpos($this->username, '@'));
		}
		return $this->username;
	}

	/**
	 * Returns true or false based on if the current user is
	 * logged into the site or not
	 */
	function isAnonymous() {
		return !$this->loggedIn;
	}

	function _hashPassword($p) {
		return md5(sha1($p));
	}

	function login($uname, $pass) {
		$db= Cgn_Db_Connector::getHandle();
		$db->query("SELECT cgn_user_id, email FROM cgn_user
			WHERE username ='".$uname."' 
			AND password = '".$this->_hashPassword($pass)."'");
		if (!$db->nextRecord()) {
			Cgn_ErrorStack::throwError('NO VALID ACCOUNT',501);
			return false;
		}
		if( $db->getNumRows() == 1) {
			$this->username = $uname;
			$this->email    = $db->record['email'];
			$this->password = $this->_hashPassword($pass);
			$this->userId = $db->record['cgn_user_id'];
			$this->loadGroups();
			return true;
		} else {
			Cgn_ErrorStack::throwError('ACCOUNT PROBLEMS',502);
			return false;
		}
		// look up uname and passwrd in db
	}


	/**
	 * Return one user given the database key
	 *
	 * @return  object  new lcUser
	 * @static
	 */
	function load($key) {
		if ($key < 1) { return null; }

		$item = new Cgn_DataItem('cgn_user');
		$item->load($key);
		$user = new Cgn_User();
		$user->setPassword($item->password);
		$user->email  = $item->email;
		$user->userId = $item->cgn_user_id;
		$user->username = $item->username;
		return $user;
	}

	/**
	 * Load group association from the database
	 */
	function loadGroups() {
		$finder = new Cgn_DataItem('cgn_user_group_link');
		$finder->andWhere('cgn_user_id',$this->userId);
		$finder->hasOne('cgn_group', 'cgn_group_id', 'cgn_group_id', 'cgn_group_id');
		$groups = $finder->find();
		$this->groups = array();
		foreach ($groups as $_group) {
			$this->groups[ $_group->cgn_group_id ] = $_group->code;

		}
	}

	/**
	 * Load the account object if it is not already loaded.
	 *
	 * The account object shall be a simple Cgn_DataItem.
	 */
	function fetchAccount() {
		if ($this->_accountLoaded) {
			return;
		}
		$this->account = new Cgn_DataItem('cgn_account');
		$this->account->andWhere('cgn_user_id', $this->userId);
		$this->account->load();

		$this->_accountLoaded = TRUE;
	}

	function addSessionMessage($msg,$type = 'msg_info') {
		$session = Cgn_Session::getSessionObj();
		$session->append('_sessionMessages', array('text'=>$msg, 'type'=>$type));
	}

	function addMessage($msg,$type = 'msg_info') {
		$session = Cgn_Session::getSessionObj();
		$session->append('_messages', array('text'=>$msg, 'type'=>$type));
	}

	/**
	 * @return object new lcUser
	 * @static
	 */
	/*
	function getUserByUsername($uname) {
	}
	 */


	/**
	 * returns a new lcUser with associated session data in <i>$sessionvars</i>.
	 * @return object  new lcUser
	 * @static
	 */
	/*
	function getUserBySesskey($sessID) {
	}
	 */



	/**
	 * Return an array of user objects
	 *
	 * @return      array   lcUser array
	 * @static
	 */
	/*
	function getListByPkey($keys) {
	}
	 */
	 
	 
	 
	/**
	 * loads the current user's profile from the 'profile' table
	 *
	 * the table's columns will become the key's of the $this->profile profile
	 * @return  void
	 */
	 
	/*
	function loadProfile() {
	}
	 */
	 
	 
	/**
	 * Save the user to the lcUser table
	 *
	 * @return  boolean  True if the SQL statement was sent (i.e. not an anonymous user)
	 */
	 
	/*
	function update() {
	}
	 */


 
	/**
	 * Save the current user's profile to the 'profile' table
	 *
	 * @return boolean  Anonymous user will return false
	 */
	 
	/*
	function updateProfile($profile = '') {

	}
	 */
	 
	 
	/**
	 * @return boolean returns false if username is already taken
	 */
	/*
	function addUser($db) {
	}
	 */


	/**
	 * Saves current session (user->sessionvars) into lcSession table
	 * prevents multiple logins because it overwrites the session key - necassary
	 * for persistent userinfo - after every page.
	 */
	 /*
	function saveSession() {

	}
	*/







	/**
	 * loads permissions for this user and module
	 */
	/*
	function loadPerms ($mid) {
		 
		if (is_array($this->perms) ) {
			return;
		}
		 
		$db = DB::getHandle();

		$sql = "select action from lcPerms where moduleID = '$mid' and (%s)";
		for ($z = 0; $z < count($this->groups); ++$z) {
			$where .= "groupID = '".$this->groups[$z]."' or ";
		}
		$where = substr($where, 0, -3);
		 
		$sql = sprintf($sql, $where);
		$db->query($sql);
		while ($db->nextRecord() ) {
			$ret[] = $db->record['action'];
		}
		$this->perms = $ret;
	}
	 */


	/**
	 * Returns true or false if this user is in a group
	 */
	function belongsToGroup($g) {
		if (!is_array($this->groups) ) { return false;} 
		return in_array($g,$this->groups);
	}


	/**
	 * used to include user into current namespace
	 * @static
	 * @deprecated see Cgn_SystemRequest::getUser()
	 */
	/*
	function & getCurrentUser() {
		global $cgnUser;
		return $cgnUser;
	}
	 */


	function getUserId() {
		return @$this->userId;
	}


	/**
	 * @static
	 */
	function registerUser($u) {
		$user = new Cgn_DataItem('cgn_user');
		$user->andWhere('email',$u->email);
		if ($u->username == '') {
			$user->orWhere('username',$u->email);
		} else {
			$user->orWhere('username',$u->username);
		}
		$user->load();
		if (!$user->_isNew && 
			($user->username == $u->username ||
			$user->email == $u->email ||
			$user->username == $u->email)) {
			//username exists
			return false;
		}
		$user->username = $u->username;
		$user->email    = $u->email;
		$user->password = $u->password;
		if( $user->save() > 0 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 */
	function save() {
		$user = new Cgn_DataItem('cgn_user');
//		$user->_nuls = array('email');
		$user->_pkey = 'cgn_user_id';
		$user->load($this->userId);
		$user->email    = $this->email;
		$user->username = $this->username;
		$user->password = $this->password;
		$result = $user->save();
		if ($result !== FALSE) {
			$this->userId = $result;
		}
		return $result;
	}


	function startSession() {
		$mySession =& Cgn_ObjectStore::getObject("object://defaultSessionLayer");
		if ($mySession->get('userId') != 0 ) {
			$this->userId = $mySession->get('userId');
			$this->username = $mySession->get('username');
			$this->email = $mySession->get('email');
			$this->password = $mySession->get('password');
			$this->loggedIn = true;
			$this->groups = unserialize($mySession->get('groups'));
		}
	}

	/**
	 * links an already started session with a registered user
	 * sessions can exist w/anonymous users, this function
	 * will link userdata to the open session;
	 * also destroys multiple logins
	 */
	function bindSession() {
		$mySession =& Cgn_Session::getSessionObj();
		$mySession->setAuthTime();
		$mySession->set('userId',$this->userId);
		$mySession->set('lastBindTime',time());
		$mySession->set('username',$this->username);
		$mySession->set('email',$this->email);
		$mySession->set('password',$this->password);
		$mySession->set('groups',serialize( $this->groups ));
		$this->loggedIn = true;
	}

	/**
	 * Erases the link between a logged in user ID and the session, 
	 * but keeps the data for debugging/logging.
	 */
	function unBindSession() {
		$mySession =& Cgn_Session::getSessionObj();

		$mySession->clear('userId');
		$mySession->clear('lastBindTime');
		$mySession->clear('username');
		$mySession->clear('email');
		$mySession->clear('password');
		$mySession->clear('groups');
		$this->loggedIn = false;
	}


	/**
	 * Erases the users current session.
	 * if you simply want to end a session, but keep the
	 * data in the db for records, use $u->unBindSession();
	 */
	function endSession($db) {
		$mySession =& Cgn_Session::getSessionObj();
		$mySession->erase();
	}
}
?>
