<?php

class Cgn_Service_Main_Page extends Cgn_Service {

	var $pageObj;
	var $crumbs = NULL;

	function Cgn_Service_Main_Page () {
		$this->pageObj = NULL;
		Cgn::loadModLibrary('Content::Cgn_Content');
	}

	/**
	 * Return an array to be placed into the bread crumb trail.
	 *
	 * @return 	Array 	list of strings.
	 */
	function getBreadCrumbs() {
		if (! isset($this->pageObj) ) { return array(); }

		include_once(CGN_LIB_PATH.'/lib_cgn_site_breadcrumbs.php');
		$crumbs = new Cgn_Site_BreadCrumbs();
		$crumbs->loadTree();
		$ar = $crumbs->getTrailForId($this->pageObj->getContentId());
		$ar[] = $this->crumbs[0];
		return $ar;
		//return $this->crumbs;
	}

	/**
	 * Load up a number of pages and display them.
	 */
	function mainEvent(&$req, &$t) {
		$link = $req->cleanString(0);

		$web = new Cgn_DataItem('cgn_web_publish');
		$web->andWhere('link_text', $link);
		if (!$web->load()) {
			Cgn_ErrorStack::throwError("Cannot find the specified page", 401);
			return;
		}
		$this->pageObj = new Cgn_WebPage($web->cgn_web_publish_id);

		$t['web'] = $web;
		$t['caption'] = $web->caption;
		$t['title'] = $web->title;
		$t['content'] = $web->content;
		Cgn_Template::setPageTitle($web->title);

		$this->crumbs[] = $web->title;

		//post process template sections and layout callbacks.
		if ($this->pageObj->isPortal()) {
			$handler =& Cgn_Template::getDefaultHandler();
			$handler->regSectionCallback( array($this, 'templateSection') );
		} else {
			$sections = $this->pageObj->getSectionList();
			foreach ($sections as $_sect) {
				$rslt = $this->emit('content_page_section_'.$_sect);
				if ($rslt !== NULL && $rslt !== FALSE) {
				$t['content'] = str_replace(
					'<!-- BEGIN: '.$_sect.' -->', 
					$rslt.' <!-- BEGIN: '.$_sect.' -->',
					$web->content);
				}
			}
		}
	}

	function imageEvent(&$req, &$t) {
		$link = $req->getvars[0];
		// __ FIXME __ clean the link
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
