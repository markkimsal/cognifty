<?php

require_once(CGN_LIB_PATH.'/lib_cgn_db_connector.php');
require_once(CGN_LIB_PATH.'/lib_cgn_db_mysql.php');

class Cgn_DbMysql_Test extends PHPUnit_Framework_TestCase {

	function setUp() {
	}

	function testExec() {
		$db = Cgn_Db_Connector::getHandle();
		//$db->connect();
		mysql_select_db('test', $db->driverId);
		$db->isSelected = true;
		$result = $db->exec('SET NAMES "UTF8"');

		$this->assertNotEquals($result, FALSE);
	}


	/**
	 * Query one should array_pop the result stack, leaving an empty array
	 */
	function testQueryOne() {
		$db = Cgn_Db_Connector::getHandle();
		mysql_select_db('mysql', $db->driverId);
		$db->queryOne('select * from user');
		$this->assertTrue( is_array($db->record) );
		$this->assertLessThan(  1, count($db->resultSet) );
	}

	/**
	 * The close method should unset the driverId as a resource
	 */
	function testClose() {
		$db = Cgn_Db_Connector::getHandle();
		$this->assertEqual(is_resource($db->driverId), TRUE);
		$db->close();
		$this->assertEqual(is_resource($db->driverId), FALSE);
	}

}
?>
