<?php

class TestSystem extends UnitTestCase {

	function TestSystem() {
		$this->UnitTestCase('System test');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testRequirements() {
		$this->assertFalse(get_magic_quotes_runtime(), "Magic quotes runtime is on");
		$this->assertFalse(get_magic_quotes_gpc(), "Magic quotes GPC is on");
	}

	function testRequestParsing() {

		// test the core request intialization process
		$_SERVER['PATH_INFO'] = "/module.server.event/foo1=1/bar/foo2=abc";

		// initialize an apache request
		initRequestInfo("apache");	
		$get = Cgn_ObjectStore::getObject('request://get');
		$this->assertTrue(is_array($get));
		$this->assertTrue(count($get)==5);
		$this->assertTrue($get['foo1']==1);
		$this->assertTrue($get['foo2']=='abc');
		$this->assertTrue($get[1]=='bar');

		$mse = Cgn_ObjectStore::getObject('request://mse');
		$this->assertTrue($mse=='module.server.event');

	}

	function testObjectStoring() {
		// make an object we know we have - this one
		$x = new TestSystem();		
		$x->sampleProperty = 5;
		Cgn_ObjectStore::storeObject("test://sampleObject", $x);
		$j = Cgn_ObjectStore::getObject("test://sampleObject");
		$this->assertTrue($x==$j, "Storing and getting object returns different result");
		$this->assertTrue(($j->sampleProperty==5), "Storing and getting object returns object with different properties");
	}
}
?>
