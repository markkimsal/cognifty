<?php

/*
require_once('../cognifty/lib/lib_cgn_obj_store.php');
require_once('../cognifty/lib/lib_cgn_db_connector.php');
require_once('../cognifty/lib/lib_cgn_db_mysql.php');
 */

require_once('../cognifty/lib/lib_cgn_util.php');

require_once('../cognifty/app-lib/lib_cgn_content.php');

/*
Mock::generate('Cgn_Db_Connector');
Mock::generate('Cgn_Db_Mysql');
 */

class TestOfContent extends UnitTestCase {

	function setUp() {
	}

	function testWebPage() {
		$page = new Cgn_Content_WebPage();
		$this->assertTrue ( $page->getTitle() == '');

		$page2 = Cgn_Content_WebPage::createNew('Blank Page');
		$this->assertTrue ( $page2->getTitle() == 'Blank Page');
		$this->assertTrue ( $page2->usedAs('web') );

	}

	function testUseAs() {
		$content = new Cgn_Content();
		$this->assertFalse($content->usedAs('web'));

		$page = Cgn_Content_WebPage::make($content);
		$this->assertTrue($page->usedAs('web'));

		$page2 = new Cgn_Content_WebPage();
		$this->assertTrue($page2->usedAs('web'));
	}


	function testWebMeta() {
		$page = new Cgn_Content_WebPage();

		$this->assertFalse($page->isPortal());

		$page->setPortal(true);
		$this->assertTrue($page->isPortal());

		$this->assertFalse($page->isHp());

		$page->setHp(true);
		$this->assertTrue($page->isHp());

		$page->setHp(false);
		$this->assertFalse($page->isHp());
	}
}
?>
