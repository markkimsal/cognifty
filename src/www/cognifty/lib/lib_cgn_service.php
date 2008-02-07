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
	 * Called before any events.
	 *
	 * If this call fails, no more processing will continue;
	 *
	 * @abstract
	 */
	function init($req) { 
		return true;
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

	function getHomeUrl() {
		list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));
		return cgn_appurl($module,$service);
	}

	function redirectHome(&$t) {
		$this->presenter = 'redirect';
		$t['url'] = $this->getHomeUrl();
	}

	function getBreadCrumbs() {
		return array('Module','Service','Event');
	}
}


class Cgn_Service_Trusted extends Cgn_Service {

	var $trustManager = null;
	var $untrustLimit  = 1;
	var $untrustScore = 0;
	var $untrustReasons = '';

	/**
	 * Called before any events.
	 * Run the trust manager
	 *
	 * @abstract
	 */
	function init($req) { 
		$this->untrustScore = $this->trustManager->scoreRequest($req);
		$this->untrustReasons = implode(',',$this->trustManager->hitRules);
		return $this->untrustScore < $this->untrustLimit;
	}

	function screenPosts() {
		Cgn::loadLibrary('Trust::Lib_Cgn_Trust_Manager');
		$this->initTrustManager();
		$this->trustManager->screenPosts();
	}

	function initTrustManager() {
		$this->trustManager = new Cgn_Trust_Manager();
	}

	function trustPlugin($name, $args=array()) {
		if (!is_array($args)) {
			$args = array($args);
		}
		$this->trustManager->initPlugin($name,$args);
	}

	function getSpamScore() {
		return $this->untrustScore;
	}
}


class Cgn_Service_Admin extends Cgn_Service {

	var $requireLogin = true;
	var $displayName = '';

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

	function getHomeUrl() {
		list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));
		return cgn_adminurl($module,$service);
	}

	/**
	 * Return the $displayName of the currently running admin service
	 */
	function getDisplayName() {
		$myHandler =& Cgn_ObjectStore::getObject("object://adminSystemHandler");
		return $myHandler->serviceList[0]->displayName;
	}
}


class Cgn_Service_AdminCrud extends Cgn_Service_Admin {

	function delEvent($req, &$t) {
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
		include_once(CGN_SYS_PATH.'/app-lib/lib_cgn_content.php');
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');

		$table = $req->cleanString('table');
		if (!$key = $req->cleanString('key') ) {
			$key = $table;
		}
		$id    = $req->cleanInt($key.'_id');

		if ( strlen($table) < 1 || $id < 1) {
			//ERRCODE 581 missing input
			Cgn_ErrorStack::throwError("No ID specified", 581);
			return false;
		}
		$obj   = new Cgn_DataItem($table);
		$obj->{$key.'_id'} = $id;
		$obj->load(array($key=>$id));
		if ($obj->_isNew) {
			//ERRCODE 581 missing input
			Cgn_ErrorStack::throwError("Object not found", 582);
			return false;
		}

		$trash = new Cgn_DataItem('cgn_obj_trash');
		$trash->table   = $table;
		$trash->content = serialize($obj);
		if ($obj->title) {
			$trash->title = $obj->title;
		} else if ($obj->display_name) {
			$trash->display_name = $obj->display_name;
		}

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
			$req->getvars['undo_id'] = $trashId;
			$undoLink = cgn_adminlink('Undo?',$module,$service,'undo', $req->getvars);

			Cgn_ErrorStack::throwSessionMessage("Object deleted.  ".$undoLink);
		}
		$this->redirectHome($t);
	}

	/**
	 * Save an object
	 */
	function saveEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$item = new Cgn_DataItem($this->tableName);

		if ($id > 0 ) {
			$item->load($id);
		} else {
			$item->initBlank();
		}

		$vals = $item->valuesAsArray();

		foreach ($vals as $_key => $_val) {
			$cleaned = $req->cleanString($_key);
			if ($cleaned != null) {
				$item->{$_key} = $cleaned;
			}
		}
		$item->save();
		$this->redirectHome($t);
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
		$this->redirectHome($t);
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
