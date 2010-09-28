<?php

require_once(CGN_LIB_PATH.'/lib_cgn_obj_store.php');
require_once(CGN_LIB_PATH.'/lib_cgn_user.php');
require_once(CGN_LIB_PATH.'/lib_cgn_db_connector.php');
require_once(CGN_LIB_PATH.'/lib_cgn_db_mysql.php');

Cgn::loadLibrary('lib_cgn_authc');
Cgn::loadLibrary('lib_cgn_ldap');
Cgn::loadLibrary('lib_cgn_error');

Mock::generate('Cgn_Db_Mysql');

Mock::generate('Cgn_Ldap');

class TestOfAuthentication extends UnitTestCase {

	function setUp() {
		static $count;


		$this->user = new Cgn_User();
		$this->user->username = 'testuser';
		$this->user->setPassword('testpass');


	}

	function testPassword() {
		//make sure md5 worked, 32 chars
		$this->assertEqual(strlen($this->user->password), 32);
		//test sha1 and md5
		$this->assertEqual($this->user->password, 
			'09202be3249d1bd81d509b9c9977da5b');
	}

	function testSubjectIsReturnedAsObject() {
		$uname = $this->user->username;
		$pass  = $this->user->password;

		$authenticator = new Cgn_Authentication_Mgr();
		$authenticator->login($uname, $pass);
		$subj = $authenticator->getSubject();

		$this->assertTrue(is_object($subj));
		$this->assertEqual('cgn_authentication_subject', strtolower(get_class($subj)));
	}

	function testUserIsLoggedIn() {
		$uname = $this->user->username;
		$pass  = 'testpass';
		$this->setupLogin();

		$authenticator = new Cgn_Authentication_Mgr();
		$loginRes = $authenticator->login($uname, $pass);
		$subj = $authenticator->getSubject();

		$this->assertTrue($loginRes);
	}


	function testSubjectHasAttributes() {
		$uname = $this->user->username;
		$pass  = 'testpass';
		$this->setupLogin();

		$authenticator = new Cgn_Authentication_Mgr();
		$loginRes = $authenticator->login($uname, $pass);
		$subj = $authenticator->getSubject();

		$this->assertTrue(is_array($subj->attributes));
		$this->assertEqual('foo@foo.localhost', $subj->attributes['email']);
		/*
		$this->assertEqual('EST', $subj->attributes['tzone']);
		$this->assertEqual('zh_CN', $subj->attributes['locale']);
		 */
	}

	function testLdapHandler() {
		$uname = 'mkimsal';
		$pass  = 'foobar';

		$ldap = new Cgn_Authentication_Handler_Ldap();
		$mockLdap = $this->setupLdap1();
		$ldap->setLdapConn($mockLdap);

		$authenticator = new Cgn_Authentication_Mgr(array('authDn'=>'ou=people,dc=example,dc=com', 'bindDn'=>'cn=%s,ou=people,dc=example,dc=com', 'dsn'=>"ldap://192.168.2.2"), $ldap);
		$authenticator->connectUser = FALSE;
		$loginRes = $authenticator->login($uname, $pass);
		$subj = $authenticator->getSubject();

		$this->assertTrue(is_array($subj->attributes));
		$this->assertEqual('foo@foo.localhost', $subj->attributes['email']);

		//no standard LDAP for locale and tzone settings
		/*
		$this->assertEqual('EST', $subj->attributes['tzone']);
		$this->assertEqual('zh_CN', $subj->attributes['locale']);
		 */
	}

	function testConnectUserToLocalDb() {
		$uname = 'mkimsal';
		$pass  = 'foobar';
		$entryDn = 'cn=m kimsal,ou=people,dc=example,dc=com';

		$ldap = new Cgn_Authentication_Handler_Ldap();
		$mockLdap = $this->setupLdap1();
		$ldap->setLdapConn($mockLdap);

		$authenticator = new Cgn_Authentication_Mgr(array('authDn'=>'ou=people,dc=example,dc=com', 'bindDn'=>'cn=%s,ou=people,dc=example,dc=com', 'dsn'=>"ldap://192.168.2.2"), $ldap);
		$authenticator->connectUser = TRUE;
		$loginRes = $authenticator->login($uname, $pass);
		$subj = $authenticator->getSubject();

		$this->assertTrue(is_array($subj->attributes));
		$this->assertEqual('foo@foo.localhost', $subj->attributes['email']);
		$this->assertEqual($entryDn, $subj->attributes['dn']);

		//no standard LDAP for locale and tzone settings
		/*
		$this->assertEqual('EST', $subj->attributes['tzone']);
		$this->assertEqual('zh_CN', $subj->attributes['locale']);
		 */
	}

	function setupLogin() {


		//setup the database
		require_once('../tests/testlib/lib_cgn_db_mock.php');
		$mockDbConnector = new Cgn_Db_MockConnector();
		Cgn_ObjectStore::storeObject('object://defaultDatabaseLayer',$mockDbConnector);
		$dbRec =  array('number'=>1, 'cgn_user_id'=>1, 'email'=>'foo@foo.localhost', 'tzone'=>'EST', 'locale'=>'zh_CN');

		$mysql = Cgn_Db_Connector::getHandle();

		/*
		$mysql->expectAtLeastOnce('query', array(
			"SELECT * FROM cgn_user   where username = \"".$this->user->username."\"  and password = \"".$this->user->password."\"     ",
			FALSE
			));
		 */
		$mysql->setReturnValue('query',true);
		$mysql->setReturnValue('nextRecord', false);
		$mysql->setReturnValueAt(0, 'nextRecord', $dbRec);
		$mysql->record = $dbRec;


		$mockDbConnector->_dsnHandles['default'] = $mysql;

		Cgn_ObjectStore::storeObject('object://defaultDatabaseLayer',$mockDbConnector);
		Cgn_DbWrapper::whenUsing('cgn_user', $mysql);
	}

	function setupLdap1() {
		$ldap = new MockCgn_Ldap();

		$ldapAttrs = 
			array(
		  	"entryUUID"=>
				array (
			  	"count"=>1,
				 0=> "4c91fca6-01f8-102f-8ac1-43e1ab2be51f"
  				),
			0=>"entryUUID",

			"mail"=>
				array(
			    "count"=>1,
		      	0=> "foo@foo.localhost"
				),
			1=>"mail",
			"entryDN"=>
				array(
					"count"=>1,
					0=>"cn=m kimsal,ou=people,dc=example,dc=com"
				),
			2=>"entryDN"
			);

		$ldap->setReturnValue('bind',true);
		$ldap->setReturnValue('search',true);
		$ldap->setReturnValue('getAttributes', false);
		$ldap->setReturnValueAt(0, 'getAttributes', $ldapAttrs);
		return $ldap;
	}
}
