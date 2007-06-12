<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');

class Cgn_Service_Content_Edit extends Cgn_Service_Admin {

	function Cgn_Service_Content_Edit () {

	}


	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_content 
			WHERE cgn_content_id = '.$id);

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$t['data'] = $db->record;
		}
		$t['form'] = $this->_loadPublishForm(
			array('id'=>$t['data']['cgn_content_id']));
	}


	function publishEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$cont = new Cgn_DataItem('cgn_content');
		$cont->_pkey = 'cgn_content_id';
		$cont->load($id);
		//save to publish table
		$cont->_table = 'cgn_content_publish';
		$cont->_pkey = 'cgn_content_publish_id';
		unset($cont->cgn_content_id);
		$newId = $cont->save();
		//update main table with the id of the published content
		/* finish this later
		$pubId = $cont->cgn_content_publish_id;
		$db = Cgn_Db_Connector::getHandle();
		$db->query("UPDATE cgn_content SET cgn_content_publish_id = ".$pubId." WHERE cgn_content_id = ".$id);
		 */

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','main');
	}


	function _loadPublishForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('publish');
		$f->action = cgn_adminurl('content','edit','publish');
		$f->label = 'Publish Content';
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['id']);
		$f->appendElement(new Cgn_Form_ElementHidden('event'),'save');
		return $f;
	}
}
?>
