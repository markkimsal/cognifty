<?php

require_once(CGN_LIB_PATH.'/lib_cgn_active_formatter.php');

class Cgn_ActiveFormatter_Test extends PHPUnit_Framework_TestCase {

	function setUp() {
	}

	function testFormatPhone() {
		/*
		$f = new Cgn_ActiveFormatter(8881234567);
		$phone =  $f->printAs('phone');

		$this->assertEquals('(888) 123-4567', $phone);
		 */

		$ff = new Cgn_ActiveFormatter('888.123.4567');
		$phone =  $ff->printAs('phone');

		$this->assertEquals('(888) 123-4567', $phone);

        setlocale(LC_ALL, 'en_US.UTF-8');
		$clean =  $ff->cleanVar(utf8_encode('abc ABC 999 '.chr(0xF6) .'()()') );
		$this->assertEquals($clean, 'abc ABC 999 ');

	}

	function testFormatEmail() {

		$ff = new Cgn_ActiveFormatter('jason j@example.com');
		$email =  $ff->printAs('email');

		$this->assertEquals('jason <j@example.com>', $email);

		$ff = new Cgn_ActiveFormatter('j@example.com');
		$email =  $ff->printAs('email');

		$this->assertEquals('j@example.com', $email);
	}

}
?>
