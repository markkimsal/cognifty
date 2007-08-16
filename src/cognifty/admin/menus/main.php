<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/html_widgets/lib_cgn_toolbar.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');


class Cgn_Service_Menus_Main extends Cgn_Service_Admin {

	function Cgn_Service_Menus_Main () {

	}


	function mainEvent(&$req, &$t) {
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('menus','main','edit'),"New Menu");

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$t['toolbar']->addButton($btn1);


		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_menu ORDER BY title');

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

		$t['dataGrid'] = new Cgn_Mvc_AdminTableView($list);
	}


	function editEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$dataItem = new Cgn_DataItem('cgn_menu');
		if ($id > 0 ) {
			$dataItem->load($id);
		}
		$values = $dataItem->valuesAsArray();

		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('menus');
		$f->width="400px";
		$f->action = cgn_adminurl('menus','main','save');
		$f->label = 'Menu Settings';
		$radio = new Cgn_Form_ElementInput('name','Menu Name');
		$f->appendElement($radio, $values['title']);
		$f->appendElement( new Cgn_Form_ElementInput('code','Code Name'), $values['code_name'] );
		$check = new Cgn_Form_ElementCheck('show_title','Show menu name');
		$check->addChoice('','',$values['show_title']);

		$f->appendElement( $check );
		$f->appendElement( new Cgn_Form_ElementHidden('id'),$values[$dataItem->_pkey] );
		$t['form'] = $f;
	}


	function saveEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$dataItem = new Cgn_DataItem('cgn_menu');
		if ($id > 0 ) {
			$dataItem->load($id);
		} else {
			$dataItem->created_on = time();
		}

		$dataItem->edited_on = time();
		$dataItem->title = $req->cleanString('name');
		$dataItem->code_name = $req->cleanString('code');
		$dataItem->show_title = intval($req->postvars['show_title'][0]+0);
		$id = $dataItem->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'menus','main','',array('id'=>$id));
	}
}
?>
