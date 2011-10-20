<?php


/**
 * Handles the inner template "sections"
 */
class Cgn_Layout_Manager {

	public function showLayoutSection($sectionName, $templateMgr) {
		if ($sectionName == 'content.main') {
			$this->showMainContent($sectionName, $templateMgr);
			return true;
		}
		//section id did not match 'content.main', look for object store variables
		$key = str_replace('.','/',$sectionName);
//		Cgn_ObjectStore::debug();
		if (Cgn_ObjectStore::hasConfig("object://layout/".$key.'/name') ) {
			$x = Cgn_ObjectStore::getConfig('object://layout/'.$key.'/name');
			$obj = Cgn_ObjectStore::getObject('object://'.$x);
			$meth = Cgn_ObjectStore::getConfig('object://layout/'.$key.'/method');
			// echo '<h2>'.$sectionId.'</h2>';      SCOTTCHANGE 20070619  Didn't want to see this in NAV BAR MENU AREA
			//echo '<BR/>';
			echo $obj->{$meth}($sectionName);
			//Cgn_ObjectStore::debug();
			//list($module,$service,$event) = explode('.', Cgn_ObjectStore::getConfig('object://layout/'.$key));
			//$x = Cgn_ObjectStore::getConfig('object://layout/'.$key);
		} else {
//			echo $sectionId;
//			echo "N/A";
		}

//		echo "Layout engine parsing content for [$sectionName]";
	}

	function showMainContent($sectionName, $templateMgr) {
		//show errors if there are any
		if (Cgn_ErrorStack::count()) {
			$terminate = false;
			$errors = array();
			$stack =& Cgn_ErrorStack::_singleton();
			for ($z=0; $z < $stack->count; ++$z) {
				if ($stack->stack[$z]->type != 'error' 
					&& $stack->stack[$z]->type != 'php'
					&& $stack->stack[$z]->type != 'sec') {
					continue;
				}
				//do we really want to halt on any errors?
				/*
				if ($stack->stack[$z]->type == 'php' ) {
					$terminate = true;
				}
				 */
				$errors[] = $stack->stack[$z]->message;
				//TODO: do I need to pull it off the stack like this?
//						$stack->pullError();
			}
			echo $templateMgr->doShowMessage($errors);
			if ($terminate) { return true; }
		}


		$systemHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
		list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));

		$req = Cgn_SystemRequest::getCurrentRequest();
		if (!$req->isAdmin()) {
			//look for module override
			if ( Cgn_ObjectStore::hasConfig('path://default/override/module/'.$module)) {
				$modulePath = Cgn_ObjectStore::getConfig('path://default/override/module/'.$module);
			} else {
				$modulePath = Cgn_ObjectStore::getString("path://default/cgn/module").'/'.$module;
			}
		} else {

			$modulePath = Cgn::getModulePath($module, 'admin');
			/*
			//look for module override
			if ( Cgn_ObjectStore::hasConfig('path://admin/override/module/'.$module)) {
				$modulePath = Cgn_ObjectStore::getConfig('path://admin/override/module/'.$module);
			} else {
				$modulePath = Cgn_ObjectStore::getString("path://admin/cgn/module").'/'.$module;
			}
			 */
		}

		if ($templateMgr->contentTpl != '') {
			$templateMgr->parseTemplateFile( $modulePath ."/templates/".$templateMgr->contentTpl.".html.php");
		} else {
			$templateMgr->parseTemplateFile( $modulePath ."/templates/$service"."_$event.html.php");
		}

//		echo "Layout engine parsing content for [$sectionName]";
	}


	/**
	 * Wrap layout contents into divs.
	 * Use a default class of "$sectionName" in the div
	 *
	 * @return void
	 */
	public function showDiv($sectionName) {
		$key = str_replace('.','/',$sectionName);
		if (!Cgn_ObjectStore::hasConfig("config://layout/".$key)) {
			return;
		}
		$layouts = Cgn_ObjectStore::getArray("config://layout/".$key);
		foreach ($layouts as $layoutKey => $objectToken) {
			$x = $this->includeObject($objectToken);
			$obj = Cgn_ObjectStore::getObject('object://'.$x[2]);
			$meth = $x[3];
			echo "<div class=\"$sectionName\">";
			echo $obj->{$meth}($sectionName);
			echo "</div>";
		}
	}

	public function debugName($sectionName) {
		return "Layout engine parsing content for [$sectionName]";
	}

	public function debugTree($sectionName) {
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
		include_once(CGN_LIB_PATH.'/lib_cgn_mvc_tree.php');

		$list = new Cgn_Mvc_TreeModel();
		
		$treeItem = new Cgn_Mvc_TreeItem('node #1');
		$list->appendChild($treeItem, NULL);
		$treeItem2 = new Cgn_Mvc_TreeItem('node #2');
		$list->appendChild($treeItem2,$treeItem);
		$treeItem3 = new Cgn_Mvc_TreeItem('node #3');
		$list->appendChild($treeItem3,$treeItem);
		$treeItem4 = new Cgn_Mvc_TreeItem('node #4');
		$list->appendChild($treeItem4, NULL);
		$treeItem5 = new Cgn_Mvc_TreeItem('node #5');
		$list->appendChild($treeItem5,$treeItem4);

		for ($q=6; $q < 20; $q++) {
			$treeItemX = new Cgn_Mvc_TreeItem('node #'.$q);
			$list->appendChild($treeItemX,$treeItem4);
			unset($treeItemX);
		}

//		Cgn::debug($treeItem);
//		Cgn::debug($list->itemList);

		$view = new Cgn_Mvc_TreeView2($list);
		$view->title = 'Links';
		return $view->toHtml();
	}

	/**
	 * Include an object and return its name
	 */
	public function includeObject($objectToken, $scheme='object') {
		static $libPath, $pluginPath, $sysPath, $filterPath = '';
		if ($sysPath == '') { $sysPath = Cgn_ObjectStore::getConfig('config://cgn/path/sys'); }
		if ($libPath == '') { $libPath = Cgn_ObjectStore::getConfig('config://cgn/path/lib'); }
		if ($pluginPath == '') { $pluginPath = Cgn_ObjectStore::getConfig('config://cgn/path/plugin'); }
		if ($filterPath == '') { $filterPath = Cgn_ObjectStore::getConfig('config://cgn/path/filter'); }

		$filePackage = explode(':', $objectToken);

		$fileName = str_replace('@lib.path@', $libPath, $filePackage[0]);
		$fileName = str_replace('@sys.path@', $sysPath, $fileName);
		$fileName = str_replace('@plugin.path@', $pluginPath, $fileName);
		$fileName = str_replace('@filter.path@', $filterPath, $fileName);

		//if ($fileName == '') { print_r(debug_backtrace());}
//		$included_files[] = $fileName;
		if (!Cgn_ObjectStore::hasConfig('object://'.$filePackage[2])) {
			Cgn_ObjectStore::includeObject($objectToken, $scheme);
		}
		return $filePackage;
//		var_dump($filePackage[2]);
	}
}

