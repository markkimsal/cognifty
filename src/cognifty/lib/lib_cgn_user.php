<?php

global $cgnUser;
$cgnUser = new Cgn_User();
$cgnUser->startSession();

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
	var $loggedIn = false;


	/**
	 * Double encrypt the password
	 */
	function setPassword($p) {
		$this->password = $this->_hashPassword($p);
	}

	function getUsername() {
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
		$db->query("SELECT cgn_user_id FROM cgn_user
			WHERE username ='".$uname."' 
			AND password = '".$this->_hashPassword($pass)."'");
		if (!$db->nextRecord()) {
			Cgn_ErrorStack::throwError('NO VALID ACCOUNT',501);
			return false;
		}
		if( $db->getNumRows() == 1) {
			$this->username = $uname;
			$this->password = $this->_hashPassword($pass);
			$this->groups = array('admin');
			$this->userId = $db->record['cgn_user_id'];
			return true;
		} else {
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
		$user->password = $user->_hashPassword($item->password);
		$user->email  = $item->email;
		$user->userId = $item->cgn_user_id;
		$user->username = $item->username;
		return $user;
	}

	function addSessionMessage($msg,$type = 'msg_info') {
		$session = Cgn_Session::getSessionObj();
		$session->append('_sessionMessages', array('text'=>$msg, 'type'=>$type));
	}

	/**
	 * @return object new lcUser
	 * @static
	 */
	/*
	function getUserByUsername($uname) {
		$db = DB::getHandle();
		$temp = new lcUser();

		$db->query("SELECT *,lcUsers.lc_user_id from lcUsers 
				LEFT JOIN lc_user_group ON lcUsers.lc_user_id = lc_user_group.lc_user_id
				LEFT JOIN lc_group on lc_user_group.lc_group_id = lc_group.lc_group_id
				WHERE username = '$uname'");
		$db->RESULT_TYPE=MYSQL_ASSOC;

		while ($db->nextRecord() ) {
			$temp->username = $db->record['username'];
			$temp->password = $db->record['password'];
			$temp->email = $db->record['email'];
			$temp->userId = sprintf('%d',$db->record['lc_user_id']);
			if (strlen($db->record['lc_group_id']) > 0 )
			$temp->groups[$db->record['lc_group_id']] = $db->record['group_key'];
		}

		$temp->loadProfile();
		return $temp;
	}
	 */


	/**
	 * returns a new lcUser with associated session data in <i>$sessionvars</i>.
	 * @return object  new lcUser
	 * @static
	 */
	/*
	function getUserBySesskey($sessID) {
		$db = DB::getHandle();
		if ($sessID == "") {
			return new lcUser();
		}
		 
		if (rand(1, 10) >= 9 ) {
			//gc cleanup - TIMESTAMP is YYYYMMDDHHMMSS
			$db->query("delete from lcSessions where unix_timestamp(gc) < ( unix_timestamp(NOW())- 86400 )");
		}
		$db->query("select * from lcSessions where sesskey = '$sessID'");
		if ($db->nextRecord() ) {
			$sessArr = unserialize(base64_decode($db->record["sessdata"]));
			$origSession = crc32($db->record['sessdata']);
			if ($sessArr["_username"] != "") {
				$temp = lcUser::getUserByUsername($sessArr["_username"]);
				$temp->sessionvars = $sessArr;
				$temp->_sessionKey = $sessID;
				$temp->_origSessionData = $origSession;
				$temp->loggedIn = true;
			} else {
				$temp = new lcUser();
				$temp->sessionvars = $sessArr;
				$temp->_sessionKey = $sessID;
				$temp->_origSessionData = $origSession;
				$temp->userId = 0;
			}
			return $temp;
		} else {
			//none found, make new session, return new user
			sess_open(DB::getHandle(), $sessID);
			$temp = new lcUser();
			$temp->userId = 0;
			return $temp;
		}
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
		$db = DB::getHandle();
		$or = join("  or lc_user_id = ", $keys);
		$db->query("SELECT *,lcUsers.lc_user_id FROM lcUsers 
				LEFT JOIN lc_user_group ON lcUsers.lc_user_id = lc_user_group.lc_user_id
				LEFT JOIN lc_group on lc_user_group.lc_group_id = lc_group.lc_group_id
				WHERE lc_user_id = $or 
				ORDER BY lc_user_id");


		$lastUserId = '';
		$retarray = array();

		while ($db->nextRecord() ) {
			if ($lastUserId != $db->record['lc_user_id']) {
				$retarray[] = $temp;
				unset($temp);
				$temp = new lcUser();
			}
			$temp->username = $db->record['username'];
			$temp->password = $db->record['password'];
			$temp->email = $db->record['email'];
			$temp->userId = sprintf('%d',$db->record['lc_user_id']);
			$temp->groups[$db->record['lc_group_id']] = $db->record['group_key'];
			 
			 
		}
		return $retarray;
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
		if (($this->username == "anonymous") || ($this->username == '') ) {
			return;
		}
		$db = DB::getHandle();
		$db->RESULT_TYPE = MYSQL_ASSOC;
		$db->queryOne("select * from profile where username='".$this->username."'");
		while (list($k, $v) = @each($db->record) ) {
			$this->profile[$k] = $v;
		}
	}
	 */
	 
	 
	/**
	 * Save the user to the lcUser table
	 *
	 * @return  boolean  True if the SQL statement was sent (i.e. not an anonymous user)
	 */
	 
	/*
	function update() {
		if ($this->username == "anonymous" ) {
			return 0;
		}
		$db = DB::getHandle();
		 
		// getting rid of duplicate groups
		$db->query("delete from lc_user_group where  lc_user_id = ".sprintf('%d',$this->userId));
		$g = array();
		if (is_array($this->groups)) {
			while (list($k, $v) = each($this->groups)) {
				//fix this later, all groups would need a key and value loaded
//				if (!@in_array($k, $g)) {

					if ($k < 1 ) continue;//bypass public group
					$g[$k] = $v;
					$db->query("insert into lc_user_group (lc_group_id,lc_user_id) 
					VALUES ('".$k."','".$this->userId."')");

//				}
			}

			unset($this->groups);
			$this->groups = $g;
			$g = "";
		}

		if (is_array($this->groups)) {
			$g = implode("|", $this->groups);
		} else {
			$g = $this->groups;
		}
		$sql = "update lcUsers set username = '".$this->username."',password='".$this->password."',email='".$this->email."' where username='".$this->username."'";
		$db->query($sql);

		return true;
	}
	 */


 
	/**
	 * Save the current user's profile to the 'profile' table
	 *
	 * @return boolean  Anonymous user will return false
	 */
	 
	function updateProfile($profile = '') {
		if ($this->username == "anonymous" ) {
			return 0;
		}
		unset($this->profile['username']);
		if ($profile == '') {
			$profile = $this->profile;
		}
		$db = DB::getHandle();
		 
		$sql = "replace into profile (username %s) VALUES ('".$this->username."' %s)";
		 
		foreach($profile as $k => $v) {
			$set .= ", $k";
			$vals .= ", '$v'";
		}
//		$set = substr($set, 0, -2);
//		$vals = substr($vals, 0, -2);
		 
		$db->query(sprintf($sql, $set, $vals) );
		return true;
	}
	 
	 
	/**
	 * @return boolean returns false if username is already taken
	 */
	function addUser($db) {
		$sql = "select count(username) as cnt from lcUsers where username = '".$this->username."'";
		$db->query($sql);
		$db->nextRecord();
		$count = $db->record['cnt'];
		if ($count != 0) {
			return false;
		}
		$sql = "insert into lcUsers (username,password,email,createdOn) values ('".$this->username."','".$this->password."','".$this->email."',NOW())";
		$db->query($sql);
		$pkey = $db->getInsertID();

                $g = array();

                if (is_array($this->groups)) {
			reset($this->groups);
                        while (list($k, $v) = each($this->groups)) {
                                if (!@in_array($v, $g)) {
                                        $g[$k] = $v;
                                        $db->query("insert into lc_user_group (lc_user_id,lc_group_id)
                                        VALUES ('".$pkey."','".$k."')");
                                }
                        }
                        unset($this->groups);
                        $this->groups = $g;
                        $g = "";
                }

		$this->bindSession();
		$this->_origSessionData = '';
		return $pkey;
	}


	/**
	 * Saves current session (user->sessionvars) into lcSession table
	 * prevents multiple logins because it overwrites the session key - necassary
	 * for persistent userinfo - after every page.
	 */
	 /*
	function saveSession() {
		if ($this->_sessionKey == "") {
			return;
		}
		if ($this->username == "") {
			print "no username";
			exit();
		}
		$val = serialize($this->sessionvars);
		if (crc32($val) == $this->_origSessionData) {
			return;
		}

		$db = DB::getHandle();
		$sessid = $this->_sessionKey;
		$val = base64_encode($val);
		$s = "replace into lcSessions (username,sessdata,sesskey) values ('".$this->username."','$val','$sessid')";
		if ($this->username == "anonymous" ) {
			$s = "replace into lcSessions (sessdata,sesskey) values ('$val','$sessid')";
		}
		 
		$db->query($s);
		//sess_close(DB::getHandle(),$this->uid,serialize($this->session));
	}
	*/


	/**
	 * links an already started session with a registered user
	 * sessions can exist w/anonymous users, this function
	 * will link userdata to the open session;
	 * also destroys multiple logins
	 */
	/*
	function bindSession() {
		if ($this->_sessionKey == "" ) {
			return false;
		}
		if ($this->username == "anonymous" ) {
			return false;
		}
		if ($this->username == "" ) {
			print "fatal error";
			exit();
		}
		$this->sessionvars[_username] = $this->username;
		$val = base64_encode(serialize($this->sessionvars));
		$this->_origSessionData = crc32($val);
		$sessid = $this->_sessionKey;
		 
		$s = "replace into lcSessions (username,sessdata,sesskey) values ('".$this->username."','$val','$sessid')";
		$db = DB::getHandle();
		$db->query($s);
		//$s = "update lcUsers set uid = '".$this->_sessionKey."' where username = '".$this->username."'";
		//$db->query($s);
	}
	 */


	/**
	 * removes session from database
	 * if you simply want to end a session, but keep the
	 * data in the db for records, use $u->endSession($db);
	 */
	function destroySession($db = "") {
		if (!is_object($db)) {
			$db = DB::getHandle();
		}
		$db->query("DELETE fROM cgn_sessions WHERE sesskey = '".$this->_sessionKey."'");
		$this->sessionvars = array();
		$this->_sessionKey = "";
	}



	/**
	 * invalidates a session but keeps the data in
	 * the db for debugging/logging
	 */
	function endSession($db) {
		$this->sessionvars["_username"] = "";
		setCookie("PHPSESSID", "", 0);
	}



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
		$x = Cgn_Db_Connector::getHandle();
		Cgn_DbWrapper::setHandle($x);
		$user = new Cgn_DataItem('cgn_user');
		$user->_pkey = 'cgn_user_id';
		$user->andWhere('email',$u->email);
		$user->andWhere('username',$u->email);
		$user->load();
		if ($user->username == $u->username) {
			//username exists
			return false;
		}
		$user->username = $u->username;
		$user->email    = $u->email;
		$user->password = $u->password;
		$user->save();
		return true;
	}


	function startSession() {
		$mySession =& Cgn_ObjectStore::getObject("object://defaultSessionLayer");
		if ($mySession->get('userId') != 0 ) {
			$this->userId = $mySession->get('userId');
			$this->username = $mySession->get('username');
			$this->email = $mySession->get('email');
			$this->loggedIn = true;
			$this->groups = unserialize($mySession->get('groups'));
		}
	}


	function bindSession() {
		$mySession =& Cgn_Session::getSessionObj();
		$mySession->setAuthTime();
		$mySession->set('userId',$this->userId);
		$mySession->set('lastBindTime',time());
		$mySession->set('username',$this->username);
		$mySession->set('email',$this->email);
		$mySession->set('groups',serialize( $this->groups ));
		$this->loggedIn = true;
	}

	function unBindSession() {
		$mySession =& Cgn_Session::getSessionObj();
		$mySession->erase();
		$mySession = new Cgn_Session_Db();
		$this->loggedIn = false;
	}
}
?>
