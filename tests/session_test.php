<?php

require_once('../cognifty/lib/lib_cgn_session.php');

class TestOfSession extends UnitTestCase {

	function setUp() {
		$this->simple = new Cgn_Session_Simple();
	}

	function testName() {
		$this->assertEqual(session_name(), 'CGNSESSION');
	}

	function testCreateSession() {
		$this->assertEqual(32, strlen($this->simple->sessionId));
	}

	function testAddVals() {
		$start = count($_SESSION);
		$this->simple->set('foo','bar');
		$end = count($_SESSION);
		$this->assertEqual($start+1, $end);
	}

}
?>
