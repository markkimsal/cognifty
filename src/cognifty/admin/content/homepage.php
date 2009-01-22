<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Content_Homepage extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Homepage() {
		$this->displayName = 'Set a Home Page';
	}

	function mainEvent(&$req, &$t) {


		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','',''), "New Content");
		$t['toolbar']->addButton($btn1);
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','web',''), "Pages");
		$t['toolbar']->addButton($btn2);

	
		$db = Cgn_Db_Connector::getHandle();

		$db->query('SELECT A.title, A.cgn_content_id, A.version, A.published_on, B.cgn_web_publish_id, B.cgn_content_version, B.is_home
				FROM cgn_content AS A
				LEFT JOIN cgn_web_publish AS B
					ON A.cgn_content_id = B.cgn_content_id
				WHERE sub_type = "web" 
			   	ORDER BY title');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			if ($db->record['is_home']=='1') {
				
				// is the is_home field true (1)  ??
				$status = '<img src="'.cgn_url().
				'/media/icons/default/bool_yes_24.png">';
				
				// if it's true,(1), provide a link to unset this as a home page ??
				$unsetLink = cgn_adminlink('unset','content','homepage','unset',array('cgn_web_publish_id'=>$db->record['cgn_web_publish_id'], 'table'=>'cgn_web_publish'));
			
				// if it's tru, (1), leave blank, it is already set ??
				$setLink = '';
			
			} else {
			
				// if it's false, anything other than (1), leave blank ??
				$status = '';
				
				// if it's false, anything other than (1), leave blank, nothing to unset ??
				$unsetLink = '';

				// if it's false, anything other than (1), provide a link to set this as a home page ??
				$setLink = cgn_adminlink('set','content','homepage','set',array('cgn_web_publish_id'=>$db->record['cgn_web_publish_id'], 'table'=>'cgn_web_publish'));			
			}
			
			$list->data[] = array(
				cgn_adminlink($db->record['title'],'content','view','',array('id'=>$db->record['cgn_content_id'])),
				$status,
				$unsetLink,
				$setLink
			);
		}
		
		$list->headers = array('Title','Status','Unset','Set');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}

	/**
	 * Set the is_home data to '1'
	 * in the cgn_web_publish table.
	 */
	function setEvent(&$req, &$t) {
		$table = $req->cleanString('table');
		$id    = $req->cleanInt($table.'_id');
		if ($table != 'cgn_web_publish') {
			return parent::setEvent($req,$t);
		}

		$db = Cgn_Db_Connector::getHandle();

		$sqlQuery01 = 'UPDATE '.$table.' SET is_home=1 WHERE '.$table.'.cgn_web_publish_id='.$id; 
		$db->query($sqlQuery01);

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl('content','homepage','');
	}

	/**
	 * Set the is_home data to '0'
	 * in the cgn_web_publish table.
	 */
	function unsetEvent(&$req, &$t) {
		$table = $req->cleanString('table');
		$id    = $req->cleanInt($table.'_id');
		if ($table != 'cgn_web_publish') {
			return parent::setEvent($req,$t);
		}

		$db = Cgn_Db_Connector::getHandle();

		$sqlQuery01 = 'UPDATE '.$table.' SET is_home=0 WHERE '.$table.'.cgn_web_publish_id='.$id; 
		$db->query($sqlQuery01);

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl('content','homepage','');
	}

	}

?>
