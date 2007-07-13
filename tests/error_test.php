<?php

require_once('../cognifty/lib/lib_cgn_obj_store.php');
require_once('../cognifty/lib/lib_cgn_error.php');


class TestOfErrors extends UnitTestCase {

	function testError() {

		trigger_error('php user error');
		$e2 = Cgn_ErrorStack::pullError();

		Cgn_ErrorStack::throwError('my error');
		$e1 = Cgn_ErrorStack::pullError();

//		echo "<pre>";
//		print_r($e2);

		$this->assertEqual('cgn_runtimeerror', strtolower( get_class( $e1 ) ));
		$this->assertEqual('cgn_runtimeerror', strtolower( get_class( $e2 ) ));
	}
}
?>
