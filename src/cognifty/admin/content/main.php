<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');

class Cgn_Service_Content_Main extends Cgn_Service_Admin {

	function Cgn_Service_Content_Main () {

	}


	function mainEvent(&$req, &$t) {
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_content');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				$db->record['title'],
				$db->record['caption'],
				cgn_adminlink('edit','content','edit','',array('id'=>$db->record['cgn_content_id']))
			);
		}
		$list->headers = array('title','sub-title','content');
//		$list->columns = array('title','caption','content');

		/*
		$list->data = array(
			0=> array('link 1','foobar.php'),
			1=> array('link 2','foobar.php'),
			2=> array('link 3','foobar.php')
		);
		 */

//		$t['listPanel'] = new Cgn_ListView($list);
//		Cgn_Template::assignObject('listPanel',$t['listPanel']);

//		$t['menuPanel'] = new Cgn_Menu('Sample Menu',$list);
		$t['menuPanel'] = new Cgn_Mvc_TableView($list);
		$t['menuPanel']->style['width'] = 'auto';
		$t['menuPanel']->style['border'] = '1px solid black';



		$t['link'] = '<a href="'.cgn_adminurl('content','main','add').'">Add</a>';
	}


	function addEvent(&$req, &$t) {
//		Cgn_Template::assignString('Message1','This is the main event!');

		$t['form'] = $this->_loadContentForm();
	}


	function saveEvent(&$req, &$t) {
		$user = new Cgn_DataItem('cgn_content');
		$user->_pkey = 'cgn_content_id';
		$user->content = $req->cleanString('content');
		$user->title = $req->cleanString('title');
		$user->caption = $req->cleanString('caption');
		$user->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','main');
	}


	function _loadContentForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('reg');
		$f->action = cgn_adminurl('content','main','save');
		$f->label = 'Add new content';
		$f->appendElement(new Cgn_Form_ElementInput('title'),$values['title']);
		$f->appendElement(new Cgn_Form_ElementInput('caption','Sub-title'));
		$f->appendElement(new Cgn_Form_ElementText('content'));
		$f->appendElement(new Cgn_Form_ElementHidden('event'),'save');
		return $f;
	}

}

?>
