<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');


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
		$values['textline_01'] = 'Use this tool to create or edit a Menu Group. A Menu Group will 
			contain Parent & Child Links. You will need to "activate" a new Menu Group by
			placing the appropriate programming in the desired template and referencing the
			Code entered here. <br />';
		$values['textline_02'] = '<br />Check this box if you want the Title displayed on the website.'; 

		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('menus');
		$f->action = cgn_adminurl('menus','main','save');
		$f->width="600px";
		$f->label = 'Menu Groups';
		$f->formHeader = $values['textline_01'];
		$radio = new Cgn_Form_ElementInput('name','Title');
		$f->appendElement($radio, $values['title']);
		$f->appendElement( new Cgn_Form_ElementInput('code','Code'), $values['code_name'] );
		$f->appendElement(new Cgn_Form_ElementContentLine(), $values['textline_02']);
		$check = new Cgn_Form_ElementCheck('show_title','Show Menu Title');
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
