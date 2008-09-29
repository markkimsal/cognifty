<?php
var_dump(Cgn::loadAppLibrary('Lib_Cgn_Content'));

class Cgn_Article_Test extends PHPUnit_Framework_TestCase {


	function testWikiContent() {

		$x = new Cgn_Article();
		$x->setContentWiki('====Header====');
		$x->setExcerptWiki('===Header2===');

		$this->assertEqual('
<h3><a name="Header" id="Header">Header</a></h3>
<div class="level3">

</div>
'
, $x->dataItem->content);

		$this->assertEqual('
<h4><a name="Header2" id="Header2">Header2</a></h4>
<div class="level4">

</div>
'
, $x->dataItem->excerpt);

	}
}

?>
