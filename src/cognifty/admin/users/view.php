<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');

class Cgn_Service_Users_View extends Cgn_Service_Admin {

	function mainEvent(&$req, &$t) {
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('users','main','loginas', array('id'=>$req->cleanInt('id'))),"Login as User");
		//make this button open in a new windows
		$btn1->setJavascript('window.open(\''.htmlspecialchars($btn1->href).'\');return false;');

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$t['toolbar']->addButton($btn1);

		$id = $req->cleanInt('id');
		$t['users'] = new Cgn_DataItem('cgn_user');
		$t['users']->load($id);

		$db = Cgn_Db_Connector::getHandle();
		$db->query("SELECT A.display_name , A.cgn_group_id
			FROM cgn_group as A
			LEFT JOIN cgn_user_group_link ON A.cgn_group_id = cgn_user_group_link.cgn_group_id
			WHERE cgn_user_group_link.cgn_user_id = $id");

		while ($db->nextRecord()) {
			$t['groups'][] = $db->record;
		}
	}
}
?>
