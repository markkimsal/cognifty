<?php

require_once('../src/cognifty/lib/lib_cgn_session.php');

class TestOfSession extends UnitTestCase {


	function testCreateSession() {
		$simple = new Cgn_Session_Simple();
		$this->assertEqual(32, strlen($simple->sessionId));
	}
}
?>
