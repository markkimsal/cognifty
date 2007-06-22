<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');


class Cgn_Service_Menus_Main extends Cgn_Service_Admin {

	function Cgn_Service_Menus_Main () {

	}


	function mainEvent(&$sys, &$t) {
		$t['titleBar'] = 'Menus: &nbsp;&nbsp; add | edit';

		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_menu');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				cgn_adminlink($db->record['title'],'menus','main','edit',array('id'=>$db->record['cgn_menu_id'])),
				$db->record['code_name'],
				cgn_adminlink('Menu Links','menus','item','',array('mid'=>$db->record['cgn_menu_id'])),
				cgn_adminlink('delete','menus','main','delete',array('id'=>$db->record['cgn_menu_id']))
			);
		}
		$list->headers = array('Title','Code','Links','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}

}

?>
