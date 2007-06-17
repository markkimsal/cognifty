<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');

class Cgn_Service_Content_Preview extends Cgn_Service_Admin {

	var $templateStyle = 'blank';

	function Cgn_Service_Content_Preview () {

	}


	function imagesEvent(&$req, &$t) {

		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_image_publish');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {

//<a onclick="insertTags('[[',']]','Article Title');return false" href="#">link to article</a>
$t['data'][] = '<div onclick="parent.insertTags(\'{{img:'.$db->record['link_text'].'\',\'}}\',\'\');" style="float:left;text-align:center;margin-right:13px;"><img height="60" src="'.cgn_adminurl('content','preview','showImage',array('id'=>$db->record['cgn_image_publish_id'])).'"/><br/>'.$db->record['title'].'</div>';
		}
	}

	function showImageEvent(&$req, &$t) {

		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_image_publish where cgn_image_publish_id = '.$req->cleanInt('id'));
		$db->nextRecord();
		echo $db->record['binary'];
		exit();
	}
}

?>
