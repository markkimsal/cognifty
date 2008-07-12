<?php


class Cgn_DataModel_Test extends PHPUnit_Framework_TestCase {


	function testValuesAsArray() {

		$x = new Model_SubItem();
		$x->set('email', 'testguy');

		$x->save();

	}

	function testPrimaryKey() {
	}

}

class Model_SubItem extends Cgn_Data_Model {

	var $table = 'cgn_user';
	var $useSearch = TRUE;
}
?>
