<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');



class Cgn_Service_Mxq_Channel extends Cgn_Service_Admin {

	function Cgn_Service_Mxq_Channel () {

	}


	function editEvent(&$req, &$t) {
		$t['editForm'] = $this->_loadForm();
	}

	function saveEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$channel = new Cgn_DataItem('cgn_mxq_channel');
		if ($id) {
			$channel->load($id);
		} else {
			$channel->created_on = time();
		}

		$channel->name = $req->cleanString('name');
		$channel->channel_type = 'broadcast';

		$channel->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl('mxq');
	}


	function _loadForm($values = array()) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('mxqchannel_01');
		$f->action = cgn_adminurl('mxq','channel','save');
		$f->label = 'Message channel settings';
		$f->appendElement(new Cgn_Form_ElementInput('name'));
		$f->appendElement(new Cgn_Form_ElementInput('code'));
		return $f;
	}
}

?>
