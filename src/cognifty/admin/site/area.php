<?php
include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/html_widgets/lib_cgn_toolbar.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');


class Cgn_Service_Site_Area extends Cgn_Service_AdminCrud {

	function Cgn_Service_Site_Area () {

	}


	function mainEvent(&$req, &$t) {

		//TOOLBAR
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('site','area','edit'),"New Area");
		$t['toolbar']->addButton($btn1);


		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_site_area');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				cgn_adminlink($db->record['title'],'site','area','view',array('id'=>$db->record['cgn_site_area_id'])),
				cgn_adminlink('edit','site','area','edit',array('id'=>$db->record['cgn_site_area_id'])),
				cgn_adminlink('delete','site','area','del',array('cgn_site_area_id'=>$db->record['cgn_site_area_id'], 'table'=>'cgn_site_area'))
			);
		}
		$list->headers = array('Title','Edit','Delete');

		$t['dataGrid'] = new Cgn_Mvc_AdminTableView($list);

	}

	/**
	 * Show the template and allow for changing of the style
	 */
	function viewEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$area = new Cgn_DataItem('cgn_site_area');
		if ($id > 0 ) {
			$area->load($id);
		}
		$t['area'] = $area;
		$_SESSION['_debug_frontend'] = true;
		$_SESSION['_debug_template'] = $area->site_template;

		$values = array();
		$values['id'] = $id;
		$values['template'] = $area->site_template;
		$t['form'] = $this->_loadAreaStyleForm($values);
	}

	function editEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$values = array();
		if ($id > 0 ) {
			$area = new Cgn_DataItem('cgn_site_area');
			$area->load($id);
			$values = $area->valuesAsArray();
			$values['id'] = $id;

		} else {
		}

		$t['form'] = $this->_loadAreaForm($values);
	}

	/** 
	 * Save edit or new file
	 */
	function saveEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$area = new Cgn_DataItem('cgn_site_area');
		if ($id > 0 ) {
			$area->load($id);
		} else {
			$area->created_on = time();
			$u = $req->getUser();
			$area->owner_id = $u->userId;
		}

		$area->title = $req->cleanString('title');
		$area->site_template = $req->cleanString('template');
		$area->template_style = 'index';
		$area->cgn_def_menu_id = $req->cleanString('menu_id');
		$area->description = $req->cleanString('description');
		$area->edited_on = time();
		$id = $area->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'site','area','view',array('id'=>$id));
	}

	function _loadAreaForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('site_area_01');
		$f->action = cgn_adminurl('site','area','save');
		$f->width="600px";
		if ( count($values) ) {
			$f->label = 'Edit Site Area';
		} else {
			$f->label = 'New Site Area';
		}
		$f->appendElement(new Cgn_Form_ElementInput('title'),$values['title']);
		$d = dir('templates/');
		$template = new Cgn_Form_ElementSelect('template','Site Template',4);

		while ($dir = $d->read()) {
			if ( substr($dir,0,1) == '.' ) { continue;}
			if ($vaules['site_template'] == $dir) {
				$template->addChoice($dir,$dir,1);
			} else {
				$template->addChoice($dir);
			}
		}
		$f->appendElement($template);
		$f->appendElement(new Cgn_Form_ElementInput('menu_id','Default Menu'),$values['cgn_menu_id']);
		$f->appendElement(new Cgn_Form_ElementText('description','Description',15,45),$values['description']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'), $values['id']);
		return $f;
	}


	function _loadAreaStyleForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('site_area_01');
		$f->action = cgn_adminurl('site','area','saveStyle');
		$f->width="600px";
		$f->label = 'Pick Template Style (variant)';
		$d = @dir('templates/'.$values['template']);

		$e = Cgn_ErrorStack::pullError('php');
		if (! is_object($d) ) { 
			Cgn_ErrorStack::throwError('No such template', 590);
			return false;
		}

		$template = new Cgn_Form_ElementSelect('template','Template Style',3);

		while ($dir = $d->read()) {
			if ( substr($dir,0,1) == '.' || substr($dir,-9) != '.html.php' ) { continue;}
			$template->addChoice($dir);
		}
		$f->appendElement($template);
		$f->appendElement(new Cgn_Form_ElementHidden('id'), $values['id']);
		return $f;
	}


}

?>
