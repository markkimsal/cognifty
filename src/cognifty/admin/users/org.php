<?php
Cgn::loadLibrary('Html_Widgets::lib_cgn_widget');
Cgn::loadLibrary('Html_Widgets::lib_cgn_toolbar');

Cgn::loadLibrary('lib_cgn_mvc');
Cgn::loadLibrary('lib_cgn_mvc_table');
Cgn::loadLibrary('Form::lib_cgn_form');

class Cgn_Service_Users_Org extends Cgn_Service_AdminCrud {

	public $tableName = 'cgn_account';
	public $tableHeaderList = array('ID', 'Display Name','Members', 'Actions');

	function Cgn_Service_Users_Org () {
		$this->pageTitle = 'Organization Maintenance';
	}


	/*
	function mainEvent(&$sys, &$t) {

		$list = new Cgn_Mvc_TableModel();
		//cut up the data into table data
		foreach($groups as $_g) {
			$list->data[] = array(
				$_g->display_name,
				$_g->get('ref_id')
			);
		}
		$list->headers = array('Display Name','Reference');

	}
	 */

	protected function _loadListData() {
		$finder = new Cgn_DataItem('cgn_account');
		$finder->_cols[] = 'cgn_account.*';
		$finder->_cols[] = 'COUNT(Torg.cgn_org_id) as member_count';
		$finder->andWhere('cgn_account.cgn_user_id', 0);
		$finder->hasOne('cgn_user_org_link', 'cgn_org_id', 'Torg');
		$finder->groupBy('Torg.cgn_org_id');
		return $finder->find();
	}

	protected function _makeTableRow($d) {
		if (!is_object($d)) {
			return array_values($d);
		}
		$row = array();

		$row[] = $d->get('cgn_account_id');
		$row[] = $d->get('org_name');
//		$row[] = $d->get('ref_id');
		$row[] = $d->get('member_count');
		$row[] = 
			cgn_adminlink('edit', 'users', 'org', 'edit', array('id'=>$d->get('cgn_account_id'))). ' | '.
			cgn_adminlink('delete', 'users', 'org', 'del', array('id'=>$d->get('cgn_account_id')));
		return $row;
	}


	/**
	 * CRUD sub-classed methods
	 */
	protected function _makeFormFields($f, $dataModel, $editMode=FALSE) {
		$values = $dataModel->valuesAsArray();

		if (!isset($values['display_name'])) {
			$values['display_name'] = '';
		}
		if (!isset($values['ref_id'])) {
			$values['ref_id'] = '';
		}
		if (!isset($values['ref_no'])) {
			$values['ref_no'] = '';
		}

		foreach ($values as $k=>$v) {
			//don't add the primary key if we're in edit mode
			if ($editMode == TRUE) {
				if ($k == 'id' || $k == $dataModel->get('_table').'_id') continue;
			}
			$widget = new Cgn_Form_ElementInput($k);
			$widget->size = 55;
			$f->appendElement($widget, $v);
			unset($widget);
		}
		if ($editMode == TRUE) {
			$f->appendElement(new Cgn_Form_ElementHidden('id'), $dataModel->getPrimaryKey());
		}
	}

	function saveEvent($req, &$t) {
		$org = new Cgn_DataItem('cgn_account');
		$org->set('org_name', $req->cleanString('display_name'));
		$org->set('ref_id', $req->cleanString('code') );
		$org->set('ref_no', $req->cleanString('ref_no') );
		$org->set('cgn_user_id', 0);
		$org->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'users','org');
	}


	/**
	 * Delete membership relations and addresses too
	 */
	function delEvent($req, &$t) {
		parent::delEvent($req, $t);
		$id = $req->cleanInt('id');
		$eraser = new Cgn_DataItem('cgn_user_org_link');
		$eraser->andWhere('cgn_org_id', $id);
		$eraser->delete();

		$eraser = new Cgn_DataItem('cgn_account_address');
		$eraser->andWhere('cgn_account_id', $id);
		$eraser->delete();
	}

	function _loadGroupForm($values=array()) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('reg');
		$f->action = cgn_adminurl('users','groups','save');
		$f->label = 'Add new Group';
		$f->appendElement(new Cgn_Form_ElementInput('display_name', 'Display Name'));
		$f->appendElement(new Cgn_Form_ElementInput('code', 'Group Code'));
		return $f;
	}
}

?>
