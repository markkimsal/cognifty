<?php

class Cgn_Service {

	var $presenter = 'default';
	var $requireLogin = false;
	var $templateStyle = '';

	function processEvent($e,&$req,&$t) {
		$eventName = $e.'Event';
		if (method_exists($this, $eventName) ) {
			$this->$eventName($req,$t);
		} else {
			Cgn_ErrorStack::throwError('no such event', 580);
		}
	}

	/**
	 * Signal whether or not the user can access
	 * this service given event $e
	 */
	function authorize($e, $u) {
		if ($this->requireLogin && $u->isAnonymous() ) {
			return false;
		}
		return true;
	}

	/**
	 * Signal whether or not the user can perform the
	 *  specified action on the specified data item.
	 */
	function authorizeAction($e, $a, $d, $u) {
		return true;
	}
}



class Cgn_Service_Admin extends Cgn_Service {

	var $requireLogin = true;

	/**
	 * Signal whether or not the user can access
	 * this service given event $e
	 */
	function authorize($e, $u) {
		if (!$this->requireLogin ) {
			return true;
		}

		if (!$u->belongsToGroup('admin') ) {
			return false;
		}
		return true;
	}
}


class Cgn_Service_AdminCrud extends Cgn_Service_Admin {

	function delEvent($req, &$t) {
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		include_once('../cognifty/lib/lib_cgn_mvc.php');
		include_once('../cognifty/app-lib/lib_cgn_content.php');
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');

		$table = $req->cleanString('table');
		$id    = $req->cleanInt($table.'_id');
		if ( strlen($table) < 1 || $id < 1) {
			//ERRCODE 581 missing input
			Cgn_ErrorStack::throwError("No ID specified", 581);
			return false;
		}
		$obj   = new Cgn_DataItem($table);
		$obj->{$table.'_id'} = $id;
		$obj->load();
		if ($obj->_isNew) {
			//ERRCODE 581 missing input
			Cgn_ErrorStack::throwError("Object not found", 582);
			return false;
		}


		$trash = new Cgn_DataItem('cgn_obj_trash');
		$trash->table   = $table;
		$trash->content = serialize($obj);
		$u = $req->getUser();
		$trash->user_id = $u->userId;
		$trash->deleted_on = time();
		$trashId = $trash->save();
		$t['trashId'] = $trashId;

		list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));
		if ($trashId > 0 ) {
			$obj->delete();
			$t['message'] = "Object deleted.";
			//get the current MSE 
			$undoLink = cgn_adminlink('Undo?',$module,$service,'undo', array('undo_id'=>$trashId));

			Cgn_ErrorStack::throwSessionMessage("Object deleted.  ".$undoLink);
		}
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			$module,$service);
	}


	function undoEvent($req, &$t) {
		$trash = new Cgn_DataItem('cgn_obj_trash');
		$trash->load( $req->cleanInt('undo_id') );
		$u = $req->getUser();
		if ($trash->user_id != $u->userId) {
			//ERRCODE 583 No access
			Cgn_ErrorStack::throwError("No access to this object", 583);
			return false;
		}

		$obj = unserialize($trash->content);
		$obj->_isNew = true;
		$obj->save();

		list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));
		if (!$obj->_isNew) {
			$trash->delete();
//			$t['message'] = "Object restored.";
//			$t['returnLink'] = cgn_adminlink('Click here to return.',$module,$service);
			Cgn_ErrorStack::throwSessionMessage("Object restored.");
		}
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			$module,$service);
	}


	/**
	 * Use the ID and current rank to move an item up in listing
	 */
	function rankUpEvent($req, &$t) { 
//		print_r($req);exit();
	}


	/**
	 * Use the ID and current rank to move an item down in listing
	 */
	function rankDownEvent($req, &$t) {
//		print_r($req);exit();
       	}

}
?>
