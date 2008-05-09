<?php

require_once(CGN_LIB_PATH.'/lib_cgn_util.php');

require_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');
require_once(CGN_SYS_PATH.'/app-lib/lib_cgn_content.php');


class TestOfContent  extends PHPUnit_Framework_TestCase {
	var $name = 'foobar';

	function setUp() {
	}

	function test_Cgn_Content() {
		$content = new Cgn_Content();
		$this->assertTrue( is_object($content->dataItem) );
		$this->assertEqual( $content->dataItem->version, 0 );

		$content->setType('file');
		$this->assertEqual( $content->isFile(), TRUE );
		$this->assertEqual( $content->isText(), FALSE );

		$content->setMime('application/x-tar');
		$content->setCaption('file caption');
		$binary = 'this should be binary content';
		$content->setContent( $binary );
		$this->assertEqual( $content->dataItem->version, 0 );
		$this->assertEqual( $content->dataItem->edited_on, NULL );

		$content->save();

		$this->assertEqual( $content->dataItem->version, 1 );
		$this->assertTrue ( $content->dataItem->edited_on > 1 );

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

	function testWebPublish() {
		$content = new Cgn_Content_WebPage();
		$content->dataItem->cgn_content_id=1;
		$content->dataItem->_isNew=FALSE;
		$page = Cgn_ContentPublisher::publishAsWeb($content);
		$this->assertEqual( strtolower(get_class($page)), 'cgn_webpage');

	}
}
?>
