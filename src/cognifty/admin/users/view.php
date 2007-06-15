<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/app-lib/lib_cgn_content.php');

class Cgn_Service_Users_View extends Cgn_Service_Admin {

	function Cgn_Service_Users_View () {

	}

	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$t['users'] = new Cgn_DataItem('cgn_user');
		$t['users']->load($id);
	}
}
?>
