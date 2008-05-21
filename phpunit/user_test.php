<?php

require_once(CGN_LIB_PATH.'/lib_cgn_obj_store.php');
require_once(CGN_LIB_PATH.'/lib_cgn_user.php');
require_once(CGN_LIB_PATH.'/lib_cgn_db_connector.php');
require_once(CGN_LIB_PATH.'/lib_cgn_db_mysql.php');


class TestOfUsers extends PHPUnit_Framework_TestCase {

	function setUp() {
		static $count;

		$this->user = new Cgn_User();
		$this->user->userId    = 2;
		$this->user->username  = 'testuser';
		$this->user->setPassword('testpass');
	}


	function testAddUser() {
		$pkey = $this->user->save();
		$this->assertGreaterThan(0, $pkey);
	}


	/**
	 * This test should fail as the "addUser" method 
	 * already added a user with the username "testuser"
	 */
	function testDoubleRegistration() {
		$pkey = Cgn_User::registerUser($this->user);
		$this->assertEqual(FALSE, $pkey);
	}

	/**
	 * This test should fail as the "addUser" method 
	 * already added a user with the username "testuser"
	 */
	function testEmailAsUsername() {
		$this->user->username  = '';
		$this->user->email  = 'testuser';
		$pkey = Cgn_User::registerUser($this->user);
		$this->assertEqual(FALSE, $pkey);
	}


	function testBindSession() {
		$simple = Cgn_ObjectStore::getObject('object://defaultSessionLayer');
		$this->user->bindSession();
	}
}
?>
