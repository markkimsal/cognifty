<?php

require_once(CGN_LIB_PATH.'/lib_cgn_db_connector.php');
require_once(CGN_LIB_PATH.'/lib_cgn_db_mysql.php');

class Cgn_DataItem_Test extends PHPUnit_Framework_TestCase {

	function setUp() {
		$db = Cgn_Db_Connector::getHandle();
		$db->close();
		$db->driverId = NULL;
		$db->database = 'mysql';
		$db->connect();
		Cgn_DbWrapper::setHandle($db);
	}


	/**
	 * Values as array should give back an array of only non-private vars
	 */
	function testValuesAsArray() {
		$item = new Cgn_DataItem('user');
		$item->andWhere('User', 'root');
		$item->load();
		$values = $item->valuesAsArray();
		$this->assertGreaterThan(1, count($values));
		$this->assertEqual('root', $values['User']);
	}

	function testPrimaryKey() {
		$item = new Cgn_DataItem('random_table');
		$item->setPrimaryKey(999);
		$this->assertEqual( 999, $item->random_table_id);
		$pkey = $item->getPrimaryKey();
		$this->assertEqual( 999, $pkey);
	}

	function testWhere() {
		$item = new Cgn_DataItem('random_table');
		$item->andWhere('key', 100, '>');
		$clause = $item->buildWhere();
		$controlClause = ' where key > 100 ';
		$this->assertEqual( $controlClause, $clause);
	}

	function testSelect() {
		$item = new Cgn_DataItem('random_table');
		$item->andWhere('key', 100, '>');
		$clause = $item->buildSelect();
		$controlQuery = 'SELECT * FROM random_table   where key > 100     ';
		$this->assertEqual( $controlQuery, $clause);
	}

}
?>
