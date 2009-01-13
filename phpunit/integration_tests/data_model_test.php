<?php


class Cgn_DataModel_Test extends PHPUnit_Framework_TestCase {


	function testValuesAsArray() {
		$x = new Model_SubItem();
		$x->set('email', 'testguy');
		$res = $x->save();

		$this->assertEqual(TRUE, is_int($res) && ($res > 0));
	}

	function testPrimaryKey() {
	}

}

class Model_SubItem extends Cgn_Data_Model {

	var $tableName = 'cgn_user';
	var $useSearch = TRUE;
}
?>
