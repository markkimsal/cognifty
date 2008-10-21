<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');


class Cgn_Service_Site_Garbage extends Cgn_Service_AdminCrud {

	function Cgn_Service_Site_Garbage () {
		$this->displayName = 'Trash Can';
	}


	function mainEvent(&$req, &$t) {
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_obj_trash');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				$db->record['table'],
				$db->record['title'],
				date('m-d-Y &\m\d\a\s\h; H:i',$db->record['deleted_on']),
				cgn_adminlink('restore','site','garbage','undo', array('undo_id'=>$db->record['cgn_obj_trash_id'], 'table'=>$db->record['table'])),
				cgn_adminlink('erase','site','garbage','erase',array('cgn_obj_trash_id'=>$db->record['cgn_obj_trash_id'], 'table'=>'cgn_obj_trash'))
			);
		}
		$list->headers = array('Table','Title','Deleted','Restore','Erase');

		$t['dataGrid'] = new Cgn_Mvc_AdminTableView($list);

	}

	function eraseEvent(&$req, &$t) {

		$id = $req->cleanInt('cgn_obj_trash_id');

		$db = Cgn_Db_Connector::getHandle();
		$db->query('delete from cgn_obj_trash WHERE cgn_obj_trash_id = '.$id.' LIMIT 1');

		$this->redirectHome($t);
	}
}

?>
