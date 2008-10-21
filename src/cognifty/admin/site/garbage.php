<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');


class Cgn_Service_Site_Garbage extends Cgn_Service_AdminCrud {

	function Cgn_Service_Site_Garbage () {
		$this->displayName = 'Trash Can';
	}

	/**
	 * Create a toolbar for the garbage page.
	 */
	function eventBefore($req, &$t) {
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button("#", "Toggle Checkboxes");
		$btn1->setJavascript("$('.data_table_check').each(function() { this.checked=!this.checked;});");
		$btn2 = new Cgn_HtmlWidget_Button("#", "Select All");
		$btn2->setJavascript("$('.data_table_check').each(function() { this.checked=true;});");

		$btn3 = new Cgn_HtmlWidget_Button("#", "Erase Selected");
		$btn3->setJavascript("\$('#data_table_hidden').attr('value', '');\$('.data_table_check').each(function() { var \$hid = $('#data_table_hidden'); if(this.checked) {\$hid.attr('value', \$hid.attr('value') + ', ' + this.value);}});   \$('#data_table_form').submit();");
		$t['toolbar']->addButton($btn2);
		$t['toolbar']->addButton($btn1);
		$t['toolbar']->addButton($btn3);

		$t['data_table_form'] = '<form method="POST" id="data_table_form" action="'.cgn_adminurl('site', 'garbage', 'masserase').'"><input type="hidden" name="data_table_hidden" id="data_table_hidden" value=""/></form>';
	}

	function mainEvent(&$req, &$t) {
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_obj_trash');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				$db->record['cgn_obj_trash_id'],
				$db->record['table'],
				$db->record['title'],
				date('m-d-Y &\m\d\a\s\h; H:i',$db->record['deleted_on']),
				cgn_adminlink('restore','site','garbage','undo', array('undo_id'=>$db->record['cgn_obj_trash_id'], 'table'=>$db->record['table'])),
				cgn_adminlink('erase','site','garbage','erase',array('cgn_obj_trash_id'=>$db->record['cgn_obj_trash_id'], 'table'=>'cgn_obj_trash'))
			);
		}
		$list->headers = array('', 'Table','Title','Deleted','Restore','Erase');

		$t['dataGrid'] = new Cgn_Mvc_AdminTableView($list);

		$t['dataGrid']->setColRenderer(0, new Cgn_Mvc_Table_CheckboxRenderer() );
	}

	function eraseEvent(&$req, &$t) {

		$id = $req->cleanInt('cgn_obj_trash_id');

		$db = Cgn_Db_Connector::getHandle();
		$db->query('delete from cgn_obj_trash WHERE cgn_obj_trash_id = '.$id.' LIMIT 1');

		$this->redirectHome($t);
	}

	function masseraseEvent(&$req, &$t) {

		$hidden = $req->cleanString('data_table_hidden');
		$hidden = substr($hidden, 2);
		$ids = explode(',', $hidden);
		foreach ($ids as $k=>$v)
			$ids[$k] = intval($v);

		$finder = new Cgn_DataItem('cgn_obj_trash');
		$finder->andWhere('cgn_obj_trash_id', $ids, 'IN');
		$finder->delete();

		$this->redirectHome($t);
	}
}
