<?php

class Cgn_Service {

	var $presenter = 'default';
	var $requireLogin = false;
	var $templateStyle = '';
	var $usesConfig = false;
	var $_configs = array();
	var $templateName = '';

	var $serviceName = '';
	var $moduleName = '';
	var $eventName = '';


	function eventBefore(&$req,&$t) {
	}

	function processEvent($e,&$req,&$t) {
		$eventName = $e.'Event';
		if (method_exists($this, $eventName) ) {
			$this->$eventName($req,$t);
		} else {
			Cgn_ErrorStack::throwError('no such event: '.$e, 480);
		}
	}


	function eventAfter(&$req,&$t) {
	}


	/**
	 * Called before any events.
	 *
	 * If this call fails, no more processing will continue;
	 */
	function init($req, $mod, $srv, $evt) { 
		$this->moduleName =  $mod;
		$this->serviceName = $srv;
		$this->eventName   = $evt;
		return true;
	}

	/**
	 * Called if any service needs to init module config values.
	 *
	 * Called from default init() method
	 */
	function initConfig($serviceConfig) {
		foreach ($serviceConfig->getModuleKeys($this->moduleName) as $k) {
			$this->_configs[$k] = $serviceConfig->getModuleVal($this->moduleName,$k);
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

	function getHomeUrl($params = array()) {
		list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));
		return cgn_appurl($module,$service, '', $params);
	}

	function redirectHome(&$t, $params = array()) {
		$this->presenter = 'redirect';
		$t['url'] = $this->getHomeUrl($params);
	}

	/**
	 * Do not supply any breadcrumbs by default
	 *
	 * @abstract
	 */
	function getBreadCrumbs() {
		return false;
//		return array('Module','Service','Event');
	}

	function getConfig($key, $defaultValue=NULL) {
		if (isset($this->_configs[$key]) ) {
			return $this->_configs[$key];
		} else {
			return $defaultValue;
		}
	}

	/**
	 * Trigger a signal and send it to the defaultSignalHandler if one is installed
	 *
	 * @return mixed  NULL if there is no such slot, TRUE or FALSE depending on the slot's code
	 */
	function emit($signal) {
		if (Cgn_ObjectStore::hasConfig('object://signal/signal/handler')) {

			//initialize the class if it has not been loaded yet (lazy loading)
			Cgn_ObjectStore::getObject('object://defaultSignalHandler');
			//$sigHandler = Cgn_ObjectStore::getObject('object://defaultSignalHandler');
			return Cgn_Signal_Mgr::emit($signal, $this);
		}
	}

	/**
	 * Self presentation handler.
	 *
	 * @abstract
	 */
	function output() { }
}


class Cgn_Service_Trusted extends Cgn_Service {

	var $trustManager   = NULL;
	var $untrustLimit   = 1;
	var $untrustScore   = 0;
	var $untrustReasons = '';
	var $dieOnFailure   = TRUE;
	var $trustFailure   = FALSE;

	/**
	 * Called before any events.
	 * Run the trust manager
	 *
	 * @abstract
	 */
	function init($req, $mod, $srv, $evt) { 
		parent::init($req, $mod, $srv, $evt);
		$this->untrustScore = $this->trustManager->scoreRequest($req);
		$this->untrustReasons = implode(',',$this->trustManager->hitRules);
		if( $this->untrustScore > $this->untrustLimit ) {
			$this->trustFailure = TRUE;
		}
		if( $this->untrustScore > $this->untrustLimit && $this->dieOnFailure ) {
			return FALSE;
		} else {
			return TRUE;
		}
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

	/**
	 * Did the request fail the trust test?
	 *
	 * @return boolean true if request failed trust
	 */
	function isTrustFailure() {
		return $this->trustFailure;
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

	function getHomeUrl($params = array()) {
		list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));
		return cgn_adminurl($module,$service, '', $params);
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

	public $pageTitle = '';

	public $dataModelName = '';
	public $tableName     = '';

	public $tableHeaderList = array();
	public $tablePaged      = FALSE;

	protected $tableModel   = NULL;
	protected $tableView    = NULL;


	/**
	 * Show a list of items
	 */
	function mainEvent($req, &$t) {
		//make page title 
		$this->_makePageTitle($t);

		//make toolbar
		$this->_makeToolbar($t);

		//find the current page
		if ($this->tablePaged) 
			$this->setupPageVars($req);

		$this->tableModel = $this->_makeTableModel();

		$data = $this->_loadListData();
		//cut up the data into table data
		foreach ($data as $_d) {
			$this->tableModel->data[] = $this->_makeTableRow($_d);
		}
		$this->tableModel->headers = $this->_getHeaderList();

		$this->tableView = $this->_makeTableView();
		$t['dataGrid']   = $this->tableView;
	}


	protected function _makeTableView() {
		if ($this->tablePaged)  {
			$view = new Cgn_Mvc_AdminTableView_Paged($this->tableModel);
			$view->setCurPage($this->tableCurPage);
			$url = cgn_appurl($this->moduleName, $this->serviceName, $this->eventName, array('p'=>'%d'));
			$view->setNextUrl( $url );
			$view->setPrevUrl( $url );
			$url = cgn_appurl($this->moduleName, $this->serviceName, $this->eventName);
			$view->setBaseUrl( $url );
		} else {
			$view = new Cgn_Mvc_AdminTableView($this->tableModel);
		}
		return $view;
	}


	protected function _makeTableModel() {
		return new Cgn_Mvc_TableModel();
	}

	protected function _loadListData() {
		return array();
	}

	protected function _getHeaderList() {
		return $this->tableHeaderList;
	}

	protected function _makeTableRow() {
		return array();
	}

	/**
	 * Set $this->tableCurPage to GET[p]
	 */
	protected function setupPageVars($req) {
		if ($p = $req->cleanInt('p'))
			$this->tableCurPage = $p;
	}


	/**
	 * Function to create a default toolbar
	 */
	protected function _makeToolbar(&$t) {
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl($this->moduleName, $this->serviceName), "Home");
		$t['toolbar']->addButton($btn2);

		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl($this->moduleName, $this->serviceName, 'create'), "Add New Item");
		$t['toolbar']->addButton($btn1);
	}

	/**
	 * Function to create a default page title
	 */
	protected function _makePageTitle(&$t) {
		if ($this->pageTitle != '') {
			$t['pageTitle'] = '<h2>'.$this->pageTitle.'</h2>';
		}
	}

	/**
	 * Show a form to make a new data item
	 */
	function createEvent($req, &$t) {
		//make page title 
		$this->_makePageTitle($t);

		//make toolbar
		$this->_makeToolbar($t);

		//load a default data model if one is set
		if ($this->dataModelName != '') {
			$c = $this->dataModelName;
			$model = new $c();
		} else if ($this->tableName != '') {
			$model = new Cgn_DataItem($this->tableName);
		} else {
			$model = new Cgn_DataItem();
		}
		//make the form
		$f = $this->_makeCreateForm($t, $model);
		$this->_makeFormFields($f, $model);
	}

	/**
	 * Show a form to make a new data item
	 */
	function editEvent($req, &$t) {
		//make page title 
		$this->_makePageTitle($t);

		//make toolbar
		$this->_makeToolbar($t);

		//load a default data model if one is set
		if ($this->dataModelName != '') {
			$c = $this->dataModelName;
			$model = new $c();
			$model->load($req->cleanInt('id'));
		} else if ($this->tableName != '') {
			$model = new Cgn_DataItem($this->tableName);
			$model->load($req->cleanInt('id'));
		} else {
			$model = new Cgn_DataItem();
		}
		//make the form
		$f = $this->_makeEditForm($t, $model);
		$this->_makeFormFields($f, $model, TRUE);
	}


	/**
	 * Function to create a default form
	 */
	protected function _makeCreateForm(&$t, $dataModel) {
		$f = new Cgn_Form('admincrud_01');
		$f->width="auto";
		$f->action = cgn_adminurl($this->moduleName, $this->serviceName, 'save');
		$t['form'] = $f;
		return $f;
	}

	/**
	 * Function to create a default form
	 */
	protected function _makeEditForm(&$t, $dataModel) {
		return $this->_makeCreateForm($t, $dataModel);
	}

	protected function _makeFormFields($f, $dataModel, $editMode=FALSE) {
		$values = $dataModel->valuesAsArray();

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

	/**
	 * Load 1 data item and place it in the template array.
	 */
	function viewEvent($req, &$t) {
		//make page title 
		$this->_makePageTitle($t);

		//make toolbar
		$this->_makeToolbar($t);

		//load a default data model if one is set
		if ($this->dataModelName != '') {
			$c = $this->dataModelName;
			$model = new $c();
		} else {
			$model = new Cgn_DataItem($this->tableName);
		}
		$model->load($req->cleanInt('id'));
		$this->_makeViewTable($model, $t);
	}

	public function _makeViewTable($model, &$t) {
		$data = $model->valuesAsArray();

		$list =  new Cgn_Mvc_TableModel();
		//cut up the data into table data
		foreach ($data as $k => $v) {
			$list->data[] = array($k, $v);
		}
		$list->headers = array('key', 'value');

		$t['dataGrid'] = new Cgn_Mvc_AdminTableView($list);
		$t['dataGrid']->attribs['width'] ='400';
	}

	function delEvent($req, &$t) {

		//make toolbar
		$this->_makeToolbar($t);

		if (!$table = $req->cleanString('table')) {
			$table = $this->tableName;
		}

		if (!$key = $req->cleanString('key') ) {
			$key = $table;
		}
		$id    = $req->cleanInt($key.'_id');

		if ( strlen($table) < 1 || $id < 1) {
			$req->getUser()->addMessage("Object not Found", 'msg_warn');
			//ERRCODE 581 missing input
//			Cgn_ErrorStack::throwError("No ID specified", 581);
			return FALSE;
		}
		$obj   = new Cgn_DataItem($table, $key.'_id');
		$obj->{$key.'_id'} = $id;
		$obj->load($id);
		if ($obj->_isNew) {

			//ERRCODE 581 missing input
			$req->getUser()->addMessage("Object not Found", 'msg_warn');
//			Cgn_ErrorStack::throwError("Object not found", 582);
			return FALSE;
		}

		$trash = new Cgn_DataItem('cgn_obj_trash');
		$trash->table   = $table;
		$trash->content = serialize($obj);
		if ($obj->title) {
			$trash->title = $obj->title;
		} else if ($obj->display_name) {
			$trash->title = $obj->display_name;
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
		//clean out vars specifically for this request
		$extraVars = $req->getvars;
		unset($extraVars['id']);
		unset($extraVars['table']);
		unset($extraVars['key']);
		unset($extraVars[$key.'_id']);
		$this->redirectHome($t, $extraVars);
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
			if ($cleaned != NULL) {
				$item->{$_key} = $cleaned;
			}
		}
		$item->save();
		$this->redirectHome($t);
		$this->item = $item;
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
		$extraVars = $req->getvars;
		unset($extraVars['undo_id']);
		$this->redirectHome($t, $extraVars);
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


class Cgn_Service_Crud extends Cgn_Service {

	public $pageTitle = '';

	public $dataModelName    = '';
	public $dataItemName     = '';

	public $tableHeaderList = array();
	public $tableColList    = array();
	public $tableCurPage    = 1;
	public $tableTotalRows  = 0;
	public $tablePaged      = FALSE;


	protected $tableModel   = NULL;
	protected $tableView    = NULL;

	/**
	 * Show a list of items
	 */
	function mainEvent($req, &$t) {
		//make page title 
		$this->_makePageTitle($t);

		//make toolbar
		$this->_makeToolbar($t);

		//find the current page
		if ($this->tablePaged) 
			$this->setupPageVars($req);

		$this->tableModel = $this->_makeTableModel();

		$data = $this->_loadListData();

		//cut up the data into table data
		foreach ($data as $_d) {
			$this->tableModel->data[] = $this->_makeTableRow($_d);
		}
		$this->tableModel->headers = $this->_getHeaderList();
		$this->tableModel->setColKeys($this->_getColList());

		$this->tableView = $this->_makeTableView();
		$t['dataGrid']   = $this->tableView;
	}

	/**
	 * Set $this->tableCurPage to GET[p]
	 */
	protected function setupPageVars($req) {
		if ($p = $req->cleanInt('p'))
			$this->tableCurPage = $p;

	}

	protected function _makeTableView() {
		if ($this->tablePaged) {
			$view = new Cgn_Mvc_TableView_Paged($this->tableModel);
			$view->setCurPage($this->tableCurPage);
			$url = cgn_appurl($this->moduleName, $this->serviceName, $this->eventName, array('p'=>'%d'));
			$view->setNextUrl( $url );
			$view->setPrevUrl( $url );
			$url = cgn_appurl($this->moduleName, $this->serviceName, $this->eventName);
			$view->setBaseUrl( $url );
			return $view;
		}
		return new Cgn_Mvc_TableView($this->tableModel);
	}

	protected function _makeTableModel() {
		return new Cgn_Mvc_TableModel();
	}

	protected function _loadListData() {
		return array();
	}

	protected function _getHeaderList() {
		return $this->tableHeaderList;
	}

	protected function _getColList() {
		return $this->tableColList;
	}

	protected function _makeTableRow($d) {
		return $d;
	}

	/**
	 * Function to create a default toolbar
	 */
	protected function _makeToolbar(&$t) {
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn2 = new Cgn_HtmlWidget_Button(cgn_appurl($this->moduleName, $this->serviceName), "Home");
		$t['toolbar']->addButton($btn2);

		$btn1 = new Cgn_HtmlWidget_Button(cgn_appurl($this->moduleName, $this->serviceName, 'create'), "Add New Item");
		$t['toolbar']->addButton($btn1);

	}

	/**
	 * Function to create a default page title
	 */
	protected function _makePageTitle(&$t) {
		if ($this->pageTitle != '') {
			$t['pageTitle'] = '<h2>'.$this->pageTitle.'</h2>';
		}
	}

	/**
	 * Show a form to make a new data item
	 */
	function createEvent($req, &$t) {
		//make page title 
		$this->_makePageTitle($t);

		//make toolbar
		$this->_makeToolbar($t);

		//load a default data model if one is set
		if ($this->dataModelName != '') {
			$c = $this->dataModelName;
			$model = new $c();
		} else if ($this->tableName != '') {
			$model = new Cgn_DataItem($this->tableName);
		} else {
			$model = new Cgn_DataItem('');
		}
		//make the form
		$f = $this->_makeCreateForm($t, $model);
		$this->_makeFormFields($f, $model, TRUE);
	}

	/**
	 * Show a form to make a new data item
	 */
	function editEvent($req, &$t) {
		//make page title 
		$this->_makePageTitle($t);

		//make toolbar
		$this->_makeToolbar($t);

		//load a default data model if one is set
		if ($this->dataModelName != '') {
			$c = $this->dataModelName;
			$model = new $c();
			$model->load($req->cleanInt('id'));
		} else if ($this->tableName != '') {
			$model = new Cgn_DataItem($this->tableName);
			$model->load($req->cleanInt('id'));
		} else {
			$model = new Cgn_DataItem('');
		}
		//make the form
		$f = $this->_makeEditForm($t, $model);
		$this->_makeFormFields($f, $model, TRUE);
	}

	/**
	 * Function to create a default form
	 */
	protected function _makeCreateForm(&$t, $dataModel) {
		$f = new Cgn_Form('datacrud_01');
		$f->width="auto";
		$f->action = cgn_appurl($this->moduleName, $this->serviceName, 'save');
		$t['form'] = $f;
		return $f;
	}

	/**
	 * Function to create a default form
	 */
	protected function _makeEditForm(&$t, $dataModel) {
		return $this->_makeCreateForm($t, $dataModel);
	}

	protected function _makeFormFields($f, $dataModel, $editMode=FALSE) {
		$values = $dataModel->valuesAsArray();

		foreach ($values as $k=>$v) {
			//don't add the primary key if we're in edit mode
			if ($editMode == TRUE) {
				if ($k == 'id' || $k == $dataModel->get('_tableName').'_id') continue;
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

	/**
	 * Load 1 data item and place it in the template array.
	 */
	function viewEvent($req, &$t) {
		//make page title 
		$this->_makePageTitle($t);

		//make toolbar
		$this->_makeToolbar($t);

		//load a default data model if one is set
		if ($this->dataModelName != '') {
			$c = $this->dataModelName;
			$t['model'] = new $c();
		} else {
			$t['model'] = new Cgn_DataItem($this->dataItemName);
		}
		$t['model']->load($req->cleanInt('id'));

		if ($this->eventName == 'view') {

			//Edit button
			$editParams = array('id'=>$req->cleanInt('id'));
			$btn4 = new Cgn_HtmlWidget_Button(
				cgn_appurl($this->moduleName, $this->serviceName, 'edit', $editParams),
				"Edit This Item");
				
			$t['toolbar']->addButton($btn4);

			//Delete button
			$delParams = array('id'=>$req->cleanInt('id'), 
				'table'=>$t['model']->get('_table'));
			$btn3 = new Cgn_HtmlWidget_Button(
				cgn_appurl($this->moduleName, $this->serviceName, 'del', $delParams),
				"Delete This Item");
				
			$t['toolbar']->addButton($btn3);
		}
	}

	function delEvent($req, &$t) {

		//make toolbar
		$this->_makeToolbar($t);

		if (!$table = $req->cleanString('table')) {
			$table = $this->dataItemName;
		}

		if (!$key = $req->cleanString('key') ) {
			$key = $table;
		}
		if(!$id = $req->cleanInt($key.'_id')) {
			$id = $req->cleanInt('id');
		}

		if ( strlen($table) < 1 || $id < 1) {
			$req->getUser()->addMessage("Object not Found", 'msg_warn');
			//ERRCODE 581 missing input
//			Cgn_ErrorStack::throwError("No ID specified", 581);
			return FALSE;
		}
		$obj   = new Cgn_DataItem($table, $key.'_id');
		$obj->{$key.'_id'} = $id;
		$obj->load($id);
		if ($obj->_isNew) {

			//ERRCODE 581 missing input
			$req->getUser()->addMessage("Object not Found", 'msg_warn');
//			Cgn_ErrorStack::throwError("Object not found", 582);
			return FALSE;
		}

		$trash = new Cgn_DataItem('cgn_obj_trash');
		$trash->table   = $table;
		$trash->content = serialize($obj);
		if ($obj->title) {
			$trash->title = $obj->title;
		} else if ($obj->display_name) {
			$trash->title = $obj->display_name;
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
			$undoLink = cgn_applink('Undo?',$module,$service,'undo', $req->getvars);

			Cgn_ErrorStack::throwSessionMessage("Object deleted.  ".$undoLink);
		}
		//clean out vars specifically for this request
		$extraVars = $req->getvars;
		unset($extraVars['id']);
		unset($extraVars['table']);
		unset($extraVars['key']);
		unset($extraVars[$key.'_id']);
		$this->redirectHome($t, $extraVars);
	}

	/**
	 * Save an object
	 */
	function saveEvent(&$req, &$t) {
		$id = $req->cleanInt('id');

		//load a default data model if one is set
		if ($this->dataModelName != '') {
			$c = $this->dataModelName;
			$item = new $c();
		} else if ($this->tableName != '') {
			$item = new Cgn_DataItem($this->tableName);
		} else {
			$item = new Cgn_DataItem('');
		}

		if ($id > 0 ) {
			$item->load($id);
		} else {
			$item->initBlank();
		}

		$vals = $item->valuesAsArray();

		foreach ($vals as $_key => $_val) {
			$cleaned = $req->cleanString($_key);
			if ($cleaned != NULL) {
				$item->set($_key, $cleaned);
			}
		}
		$item->save();
		$this->redirectHome($t);
		$this->item = $item;
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
		$extraVars = $req->getvars;
		unset($extraVars['undo_id']);
		$this->redirectHome($t, $extraVars);
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
