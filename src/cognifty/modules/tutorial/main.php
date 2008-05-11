<?php


class Cgn_Service_Tutorial_Main extends Cgn_Service {

	function Cgn_Service_Tutorial_Main () {

	}


	function mainEvent(&$req, &$t) {
	//	Cgn_Template::assignString('Message1','This is the main event!');
	}

	function pageEvent(&$req, &$t) {
		//secure the input
		if (isset($req->getvars['p'])) {
			$filename = basename(@$req->getvars['p']);
		} else {
			$filename = basename(@$req->getvars[0]);
		}
		//fix old style URLs
		$aliases = 
			array ('concept1'=> 'Framework_Concepts.html'
			);

		if (in_array($filename, array_keys($aliases))) {
			$filename = $aliases[$filename];
		}

		if (substr($filename,-5) === '.html') {
			$filename = substr($filename,0, -5);
		} else {
			//sub-directory simulation
			$filename = basename(@$req->getvars[1]);
		}

		//get our location
		$modDir = Cgn_ObjectStore::getConfig('path://default/cgn/module');
		$t['contents'] = @file_get_contents($modDir.'/tutorial/tut/'.$filename.'.html');
		if(!$t['contents']) {
			$t['contents'] = 'Sorry, file not found.';
		}
	}
}

?>
