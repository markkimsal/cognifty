<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');



class Cgn_Service_Mxq_Main extends Cgn_Service_Admin {

	function Cgn_Service_Mxq_Main () {

	}


	function mainEvent(&$req, &$t) {
		$db = Cgn_Db_Connector::getHandle();

		$channelLoader = new Cgn_DataItem('cgn_mxq_channel');
		$channels = $channelLoader->find();

		$db->query(' SELECT count(cgn_mxq_id) as total, cgn_mxq_channel_id
			FROM cgn_mxq
			GROUP BY cgn_mxq_channel_id');

		while ($db->nextRecord()) {
			$cid = $db->record['cgn_mxq_channel_id'];
			$channels[$cid]->total = $db->record['total'];
		}

		$model = new Cgn_Mvc_TableModel();

		$model->headers = array('Channel Name','Type', 'Message Count');
		foreach ($channels as $chan) {
			$model->data[] = array(
				cgn_adminlink($chan->name, 'mxq','channel','view', array('id'=>$chan->cgn_mxq_channel_id)),
			   	$chan->channel_type, $chan->total);
		}



		//toolbar
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('mxq','channel','edit'),"New Channel");
		$t['toolbar']->addButton($btn1);


		$t['dataGrid'] = new Cgn_Mvc_AdminTableView($model);
	}


	function editEvent(&$req, &$t) {

	}
}

?>
