<?php


class Cgn_DataItem_Test extends PHPUnit_Framework_TestCase {

	function testSaveAdminUser() {
		$x = new Cgn_DataItem('cgn_user');
		$x->username = 'deleteme';
		$x->email = 'deleteme';
		$res = $x->save();

		$this->assertEqual(TRUE, is_int($res) && ($res > 0));
	}

	function testFindAdminUser() {
		$x = new Cgn_DataItem('cgn_user');
		$x->_rsltByPkey = false;
		$x->andWhere('email', 'deleteme');
		$res = $x->find('1=1');
		$sql = $x->buildSelect();

		$email = $res[0]->email;
		$this->assertEqual( 'deleteme', $email);
	}

	function testDelete() {
		$x = new Cgn_DataItem('cgn_user');
		$x->username = 'deleteme';
		$x->email = 'deleteme';
		$x->save();

		$res = $x->delete();

		$this->assertEqual( TRUE, $res);
	}
}

?>
