<?php


class Cgn_ObjectStore_Test extends PHPUnit_Framework_TestCase {


	function setUp() {
		Cgn_ObjectStore::init();
	}


	/*
	function testGetMethodName() {
	}
	 */

	function testArrays() {
		$ar = array(1,2,3,4);
		Cgn_ObjectStore::setArray('config://default/my/test/array', $ar);

		$foo = Cgn_ObjectStore::getArray('config://default/my/test/array');

		$this->assertEqual($ar, $foo);


		/*
		$parent = Cgn_ObjectStore::getArray('config://default/my/test/');

		var_dump($parent);
//		$foo2 = array('array' => $foo);

//		$this->assertEqual($foo2, $parent);
			*/
	}

	function testObject() {
		$o = new Cgn_ObjectStore_Test();
		Cgn_ObjectStore::storeObject('foobar://myTest/', $o);
		$this->assertTrue(Cgn_ObjectStore::hasConfig('foobar://myTest/'));

		Cgn_ObjectStore::storeObject('foobar://yourTest/m', $o);
		$this->assertTrue(Cgn_ObjectStore::hasConfig('foobar://yourTest/m'));

//		$x = Cgn_ObjectStore:
	}
}
?>
