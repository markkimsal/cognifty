<?php


class Cgn_DataItem_Test extends PHPUnit_Framework_TestCase {


	function testFindAdminUser() {
		$x = new Cgn_DataItem('cgn_user');
		$x->_rsltByPkey = false;
		$res = $x->find('1=1');
		$sql = $x->buildSelect();

		$email = $res[0]->email;
		$this->assertEqual( 'testguy', $email);
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
