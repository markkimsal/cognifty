<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');



class Cgn_Service_Mxq_Channel extends Cgn_Service_Admin {

	function Cgn_Service_Mxq_Channel () {
	}


	/**
	 * Show all messages in the queue
	 */
	function viewEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$loader = new Cgn_DataItem('cgn_mxq');
		$loader->andWhere('cgn_mxq_channel_id',$id);
		$loader->_cols=array('msg_name','received_on', 'format_type', 'return_address', 'expiry_date', 'BIT_LENGTH(msg) AS msg_len');
		$loader->_exclude('msg');
		$messages = $loader->find();

		$list = new Cgn_Mvc_TableModel();
		//cut up the data into table data
		foreach ($messages as $record) {
			$list->data[] = array(
				$record->msg_name,
				$record->msg_len,
				date('M jS Y',$record->received_on),
				$record->format_type,
				$record->return_address,
				$record->expiry_date
			);
		}
		$list->headers = array('Name','Size','Date','Type','Return Addr','Expires');
		$t['form'] = new Cgn_Mvc_AdminTableView($list);
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
