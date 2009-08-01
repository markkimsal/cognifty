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
	var $userId          = 0;
	var $idProvider      = 'self';
	var $idProviderToken = NULL;

	var $enableAgent = NULL;
	var $agentKey    = NULL;
	 
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
		$finder = new Cgn_DataItem('cgn_user');

		$finder->andWhere('username', $uname);
		$finder->andWhere('password', $this->_hashPassword($pass));
		$finder->_rsltByPkey = FALSE;
		$results = $finder->findAsArray();
		if (!count($results)) {
			Cgn_ErrorStack::throwError('NO VALID ACCOUNT',501);
			return false;
		}
		if( count($results) == 1) {
			$record = $results[0];
			$this->username = $uname;
			$this->email    = $record['email'];
			$this->password = $this->_hashPassword($pass);
			$this->userId = $record['cgn_user_id'];
			$this->loadGroups();
			$this->_recordLogin();
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
		if ($key < 1) { return NULL; }

		$item = new Cgn_DataItem('cgn_user');
		$item->load($key);
		$user = new Cgn_User();
		$user->setPassword($item->password);
		$user->email  = $item->email;
		$user->userId = $item->cgn_user_id;
		$user->username = $item->username;
		$user->enableAgent = $item->enable_agent == '1'? TRUE : FALSE;
		return $user;
	}

	/**
	 * Load group association from the database
	 */
	function loadGroups() {
		$finder = new Cgn_DataItem('cgn_user_group_link');
		$finder->andWhere('cgn_user_id',$this->userId);
		$finder->hasOne('cgn_group', 'cgn_group_id', 'Tgrp', 'cgn_group_id');
		$groups = $finder->find();
		$this->groups = array();
		foreach ($groups as $_group) {
			if ($_group->code != '')
			$this->groups[ $_group->cgn_group_id ] = $_group->code;
		}
	}

	/**
	 * Return an array of cgn_group_id integers
	 *
	 * @return 	Array 	list of primary keys of groups this user belongs to
	 */
	function getGroupIds() {
		if (count($this->groups)) {
			return array_keys($this->groups);
		} else {
			return array(0);
		}
	}

	/**
	 * Add a user to a group
	 *
	 * @param int $gid 		internal database id of the group
	 * @param string $gcode 		special code for the group
	 */
	function addToGroup($gid, $gcode) {
		$this->groups[(int)$gid] = $gcode;
	}


	/**
	 * Remove a user to a group
	 *
	 * @param int $gid 		internal database id of the group
	 * @param string $gcode 		special code for the group
	 */
	function removeFromGroup($gid, $gcode) {
		unset($this->groups[$gid]);
	}

	/**
	 * Write groups to the database and the session.
	 *
	 * If this user has a session, update it as well.
	 */
	function saveGroups() {
		$finder = new Cgn_DataItem('cgn_user_group_link');
		$finder->andWhere('cgn_user_id', $this->getUserId());
		$items = $finder->find();
		$oldGids = array();
		if (is_array($items))foreach ($items as $_item) {
			$oldGids[] = $_item->cgn_group_id;
		}
		$newGids = $this->getGroupIds();
		$delGids = array_diff($oldGids, $newGids);
		$addGids = array_diff($newGids, $oldGids);

		/*
		var_dump($delGids);
		var_dump($newGids);
		exit();
		// */
		foreach ($addGids as $_g) {
			if ($_g == 0) { continue; }
			$newGroup = new Cgn_DataItem('cgn_user_group_link');
			//table doesn't have a primary key
			unset($newGroup->cgn_user_group_link_id);
			$newGroup->cgn_group_id = $_g;
			$newGroup->cgn_user_id = $this->getUserId();
			$newGroup->active_on = time();
			$newGroup->save();
		}

		foreach ($delGids as $_g) {
			$oldGroup = new Cgn_DataItem('cgn_user_group_link');
			$oldGroup->andWhere('cgn_group_id', $_g);
			$oldGroup->andWhere('cgn_user_id', $this->getUserId());
			$oldGroup->delete();
		}

		$this->updateSessionGroups();
	}

	/**
	 * If this user is the logged in user of the session, save the groups 
	 * to the session.
	 */
	function updateSessionGroups() {
		$mySession =& Cgn_Session::getSessionObj();
		if ($this->getUserId() == $mySession->get('userId')) {
			$mySession->set('groups',serialize( $this->groups ));
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

	/**
	 * Add a message to the session, it will be displayed on the next template render.
	 *
	 * This is usefull for adding messages before a redirect.
	 *
	 * valid types include:
	 *  msg_info
	 *  msg_warn
	 */
	function addSessionMessage($msg,$type = 'msg_info') {
		$session = Cgn_Session::getSessionObj();
		$session->append('_sessionMessages', array('text'=>$msg, 'type'=>$type));
	}

	function addMessage($msg,$type = 'msg_info') {
		$session = Cgn_Session::getSessionObj();
		$session->append('_messages', array('text'=>$msg, 'type'=>$type));
	}

	/**
	 * Turn on the API agent feature.
	 *
	 * If $createKey is true make a new key only if none exists
	 */
	function enableApi($createKey = FALSE) {
		$this->enableAgent = TRUE;

		//peek directly at the db, because we don't keep the 
		// agent key loaded in memory normally
		$d = new Cgn_DataItem('cgn_user');
		$d->load( $this->getUserId());

		if ($d->agent_key == '') {
			if(!$this->regenerateAgentKey()) {
				//failed, turn off agent api
				$this->enableAgent = FALSE;
			}
		}
		$this->save();
		return $this->enableAgent;
	}


	/**
	 * Turn on the API agent feature.
	 *
	 * If $createKey is true make a new key only if none exists
	 */
	function disableApi() {
		$this->enableAgent = FALSE;
		$this->save();
		return $this->enableAgent === FALSE;
	}



	/**
	 * Create a new, unique agent key string
	 */
	function regenerateAgentKey($deep=0) {
		if ($deep == 3) {
			$this->agentKey = '';
			return FALSE;
		} 
		$rand = rand(100000000, PHP_INT_MAX);
		$crc = sprintf('%u',crc32($rand));
		$tok =  base_convert( $rand.'a'.$crc, 11,26);

		$d = new Cgn_DataItem('cgn_user');
		$d->andWhere('agent_key', $tok);
		$t = $d->find();
		if (is_array($t) && count($t) > 0) {
			$this->regenerateAgentKey($deep+1);
		} else {
			$this->agentKey = $tok;
		}
		return TRUE;
	}

 
	 
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


	function getUserId() {
		return @$this->userId;
	}


	/**
	 * @static
	 */
	static function registerUser($u, $idProvider='self') {
		//check to see if this user exists
		$user = new Cgn_DataItem('cgn_user');
		$user->andWhere('email', $u->email);
		if ($u->username == '') {
			$user->orWhere('username', $u->email);
		} else {
			$user->orWhere('username', $u->username);
		}
		$user->andWhereSub('id_provider', $idProvider);
		$user->load();
		if (!$user->_isNew && 
			($user->username == $u->username ||
			$user->email == $u->email ||
			$user->username == $u->email)) {
			//username exists
			return false;
		}
		//save
		$u->idProvider = $idProvider;
		if( $u->save() > 0 ) {
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

		if (!$this->userId) {
			$this->_prepareRegInfo($user);
		}

		//only if there's been a change
		if ($this->agentKey !== NULL) {
			$user->agent_key = $this->agentKey;
		}
		//only if there's been a change
		if ($this->enableAgent !== NULL) {
			$user->enable_agent = $this->enableAgent? 1 : 0;
		}

		$result = $user->save();
		if ($result !== FALSE) {
			$this->userId = $result;
		}
		return $result;
	}

	/**
	 * Save some user session data into the $dataItem
	 *
	 * This method does not set reg_cpm, that is left up to user scripts.
	 * @param Object $dataItem  Cgn_DataItem class from cgn_user table
	 */
	protected function _prepareRegInfo($dataItem) {
		$mySession = Cgn_ObjectStore::getObject("object://defaultSessionLayer");
		$dataItem->_nuls[] = 'reg_cpm';
		$dataItem->_nuls[] = 'reg_id_addr';
		$dataItem->_nuls[] = 'id_provider_token';
		$dataItem->set('reg_date', time());
		$dataItem->set('login_date', time());

		if ($mySession->get('_sess_referrer') != NULL ) {
			$dataItem->set('reg_referrer', $mySession->get('_sess_referrer'));
			$dataItem->set('login_referrer', $mySession->get('_sess_referrer'));
		}
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$dataItem->set('reg_ip_addr', $_SERVER['REMOTE_ADDR']);
			$dataItem->set('login_ip_addr', $_SERVER['REMOTE_ADDR']);
		}

		//handle ID Providers
		$dataItem->set('id_provider', $this->idProvider);
		$dataItem->set('id_provider_token', $this->idProviderToken);
	}

	/**
	 * Save login info back to the user table
	 *
	 * This method does not set reg_cpm, that is left up to user scripts.
	 * @param Object $dataItem  Cgn_DataItem class from cgn_user table
	 */
	protected function _recordLogin() {
		if (!$this->userId) {
			var_dump($this->userId);
			return;
		}
		$dataItem = new Cgn_DataItem('cgn_user');
		$dataItem->set('login_date', time());
		$mySession = Cgn_ObjectStore::getObject("object://defaultSessionLayer");
		if ($mySession->get('_sess_referrer') != NULL ) {
			$dataItem->set('login_referrer', $mySession->get('_sess_referrer'));
		}
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$dataItem->set('login_ip_addr', $_SERVER['REMOTE_ADDR']);
		}
		$dataItem->_isNew = FALSE;
		$dataItem->set('cgn_user_id', $this->userId);
		$dataItem->save();
	}

	/**
	 * Grab the current session and apply values to the current user object.
	 *
	 * This is to avoid a database hit for most commonly accessed user 
	 * properties.
	 */
	function startSession() {
		$mySession = Cgn_ObjectStore::getObject("object://defaultSessionLayer");
		if ($mySession->get('userId') != 0 ) {
			$this->userId   = $mySession->get('userId');
			$this->username = $mySession->get('username');
			$this->email    = $mySession->get('email');
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
	function endSession() {
		$mySession =& Cgn_Session::getSessionObj();
		$mySession->erase();
	}
}
