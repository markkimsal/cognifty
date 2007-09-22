<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');



class Cgn_Service_Mxq_Main extends Cgn_Service_Admin {

	function Cgn_Service_Mxq_Main () {

	}


	function mainEvent(&$sys, &$t) {
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

		$model->headers = array('Channel Name','Message Count');
		foreach ($channels as $chan) {
			$model->data[] = array($chan->name, $chan->total);
		}

		$t['dataGrid'] = new Cgn_Mvc_TableView($model);
	}
}

?>
