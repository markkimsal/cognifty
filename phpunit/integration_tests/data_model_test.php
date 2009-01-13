<?php


class Cgn_DataModel_Test extends PHPUnit_Framework_TestCase {


	function testValuesAsArray() {
		$x = new Model_SubItem();
		$x->set('email', 'testguy');
		$res = $x->save();

		$this->assertEqual(TRUE, is_int($res) && ($res > 0));
	}

	function testPrimaryKey() {
		$x = new Model_SubItem();
		$di = new Cgn_DataItem('no_table');
		$di->set('no_table_id', 999);
		$x->setDataItem($di);

		$res = $x->getPrimaryKey();
		$this->assertEqual(TRUE, is_int($res));
		$this->assertEqual(TRUE,  ($res == 999));

		$res = $x->get('no_table_id');
		$this->assertEqual(TRUE, is_int($res));
		$this->assertEqual(TRUE,  ($res == 999));
	}

}

class Model_SubItem extends Cgn_Data_Model {

	var $tableName = 'cgn_user';
	var $useSearch = TRUE;
}
?>
