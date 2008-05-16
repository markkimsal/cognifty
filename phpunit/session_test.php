<?php

require_once(CGN_LIB_PATH.'/lib_cgn_session.php');

class TestOfSession extends PHPUnit_Framework_TestCase {
	var $name = 'foobar';

	function __construct() {
//		$this->simple = new Cgn_Session_Simple();
		$this->simple = Cgn_ObjectStore::getObject('object://defaultSessionLayer');
	}

	function testStart() {
		$this->assertEqual(FALSE, $this->simple->started);
		$this->simple->start();
		$this->assertEqual(TRUE, $this->simple->started);
		$this->assertGreaterThan(0, $this->simple->touchTime);
		$this->assertEqual(-1, $this->simple->lastTouchTime);

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

	function testAuth() {
		$this->assertGreaterThan(0, $this->simple->touchTime);
		$this->assertEqual(FALSE, $this->simple->needsReAuth());
		//sets current touch time to one second ago
		$this->simple->touch(time()-1);
		//sets last touch time to one second ago
		$this->simple->touch();
		$this->simple->inactivityReAuth = 1;
		$this->assertEqual(TRUE, $this->simple->needsReAuth());
	}

	/**
	 * resets everything
	 */
	function testClearVals() {
		$this->simple->set('foo','bar');
		$start = count($_SESSION);
		$this->assertNotEquals($start, 0);

		$this->simple->clear('foo');
		$middle = count($_SESSION);
		$this->assertEquals($middle, $start-1);

		$this->simple->clearAll();
		$end = count($_SESSION);
		$this->assertEquals($end, 0);
	}
}
?>
