<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');

class Cgn_Service_Users_View extends Cgn_Service_Admin {

	function Cgn_Service_Users_View () {

	}

	function mainEvent(&$req, &$t) {
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
