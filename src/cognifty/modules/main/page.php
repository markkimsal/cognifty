<?php


class Cgn_Service_Main_Page extends Cgn_Service {

	var $pageObj;

	function Cgn_Service_Main_Page () {
		$this->pageObj = null;
		include_once('../cognifty/app-lib/lib_cgn_content.php');
	}


	/**
	 * Load up a number of pages and display them.
	 */
	function mainEvent(&$req, &$t) {
		$link = $req->getvars[0];
		// __ FIXME __ clean the link
		$link = trim(addslashes($link));

		$web = new Cgn_DataItem('cgn_web_publish');
		$web->andWhere('link_text', $link);
		$web->load();
		if ($web->_isNew) {
			Cgn_ErrorStack::throwError("Cannot find the specified page", 401);
			return;
		}
		$this->pageObj = new Cgn_WebPage($web->cgn_web_publish_id);
		if ($this->pageObj->isPortal()) {
			$handler =& Cgn_Template::getDefaultHandler();
			$handler->regSectionCallback( array($this, 'templateSection') );
		}


		$t['web'] = $web;
		$t['caption'] = $web->caption;
		$t['title'] = $web->title;
		$t['content'] = $web->content;
	}

	function imageEvent(&$req, &$t) {
		$link = $req->getvars[0];
		// __ FIXME __ clean the link
		$link = trim(addslashes($link));
		$image = new Cgn_DataItem('cgn_image_publish');
		$image->andWhere('link_text', $link);
		$image->load();
		header('Content-type: '. $image->mime);
		echo $image->web_image;
		exit();
	}

	/**
	 * Connect parts of the template to parts of the 
	 * advanced page object.
	 *
	 * This function is only registered if the desired page is advanced
	 */
	function templateSection($name, &$templateHander) {
		return $this->pageObj->getSectionContent($name);
	}

	/**
	 * Connect parts of the template to parts of the 
	 * advanced page object.
	 *
	 * This function is only registered if the desired page is advanced
	 */
	function sectionHasContent($name, &$templateHander) {

	}
}

?>
