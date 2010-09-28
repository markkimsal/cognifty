<?php

class Cgn_Service {

	var $homeLinkName = 'List';
	var $representing = 'Item';
	var $presenter = 'default';
	var $requireLogin = false;
	var $templateStyle = '';
	var $usesConfig = false;
	var $_configs = array();
	var $usesPerms  = false;
	var $_perms = array();
	var $templateName = '';

	var $serviceName = '';
	var $moduleName = '';
	var $eventName = '';


	/**
	 * Any event preprocessing
	 */
	function eventBefore(&$req,&$t) {
	}

	/**
	 * Process internal events
	 */
	function processEvent($e,&$req,&$t) {
		$eventName = $e.'Event';
		if (method_exists($this, $eventName) ) {
			$this->$eventName($req,$t);
		} else {
			Cgn_ErrorStack::throwError('no such event: '.$e, 480);
		}
	}

	/**
	 * Handle authorization failures.
	 * This method is called if $this->authorize() returns false.
	 * By default, stack the login ticket.
	 *
	 * @return bool   true to process output from this service, false otherwise.
	 */
	public function onAuthFailure($e, $req, &$t) {
		$newTicket = new Cgn_SystemTicket('login', 'main', 'requireLogin');
		Cgn_SystemRunner::stackTicket($newTicket);
		$t['redir'] = base64_encode(
			cgn_appurl($this->moduleName, $this->serviceName, $this->eventName, $req->getvars)
		);
		/*
		Cgn_Template::assignArray('redir', base64_encode(
			cgn_appurl($this->moduleName, $this->serviceName, $this->eventName, $req->getvars)
		));
		 */
		return false;
	}


	/**
	 * Handle authorization failures.
	 * This method is called if $this->authorize() returns false and the user is logged in.
	 *
	 * @return bool  true to process output from this service, false otherwise.
	 */
	public function onAccessDenied($e, $req, &$t) {
		Cgn_ErrorStack::throwError('Unable to process request: You do not have permisision to access this service.', '601', 'sec');
		$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
		$myTemplate->parseTemplate($this->templateStyle);
		return false;
	}


	/**
	 * Any event post-processing
	 */
	function eventAfter(&$req,&$t) {
	}


	/**
	 * Called before any events.  Initialize service variables, configs, and permissions
	 *
	 * If this call fails, no more processing will continue;
	 */
	function init($req, $mod, $srv, $evt) { 
		$this->moduleName =  $mod;
		$this->serviceName = $srv;
		$this->eventName   = $evt;

		if ($this->usesConfig === true || $this->usesPerms === true) {
			$serviceConfig =& Cgn_ObjectStore::getObject('object://defaultConfigHandler');
			$area = 'modules';
			if ($this instanceof Cgn_Service_Admin) { $area = 'admin'; }
			$serviceConfig->initModule($this->moduleName, $area);
		}

		/**
		 * handle module configuration
		 */
		if ($this->usesConfig === true) {
			$this->initConfig($serviceConfig);
		}

		/**
		 * handle module configuration
		 */
		if ($this->usesPerms === true) {
			$this->initPerms($serviceConfig);
		}
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
	 * Called if any service needs to init module permissions.
	 *
	 * Called from default init() method
	 */
	function initPerms($serviceConfig) {
		foreach ($serviceConfig->getPermissionKeys($this->moduleName) as $k) {
			$this->_perms[$k] = $serviceConfig->getPermissionVal($this->moduleName,$k);
		}
	}

	/**
	 * Signal whether or not the user can access the event $e of this service
	 *
	 * @return boolean  True if user has permission or service doesn't "usePerms"
	 */
	function authorize($e, $u) {
		if ($this->requireLogin && $u->isAnonymous() ) {
			return FALSE;
		}
		//if we don't specify permissions, then allow access
		if (!$this->usesPerms) {
			return TRUE;
		}

		return $this->hasAccess($u, $this->eventName);
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
	 * Return true of the user has access to the permission
	 *
	 * Returns false if no permission or domain has been defined
	 * @return Boolean  true if the user has permission
	 */
	public function hasPermission($u, $domain, $perm) {
		if ($perm == '' || $domain == '') {
			return FALSE;
		}

		if (isset($this->_perms[$domain][$perm]) ) {
			$groups = explode(',', $this->_perms[$domain][$perm]);
			foreach ($groups as $_g) {
				if ($u->belongsToGroup($_g) ) {
					return TRUE;
				}
			}
			return FALSE;
		}
		return FALSE;
	}

	/**
	 * Return true of the user has access to the permission
	 *
	 * Returns true if no event is defined.
	 *
	 * @return Boolean  true if the user has permission
	 */
	public function hasAccess($u, $event=NULL) {
		if ($event == NULL) 
			$event = $this->eventName;

		if (isset($this->_perms[$this->serviceName][$event]) ) {
			$groups = explode(',', $this->_perms[$this->serviceName][$event]);
			foreach ($groups as $_g) {
				if ($u->belongsToGroup($_g) ) {
					return TRUE;
				}
			}
			return FALSE;
		}
		return TRUE;
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

	/**
	 * Handle authorization failures.
	 * This method is called if $this->authorize() returns false and the user is not logged in.
	 * By default, stack the login ticket.
	 *
	 * @return bool   true to process output from this service, false otherwise.
	 */
	public function onAuthFailure($e, $req, &$t) {
		$newTicket = new Cgn_SystemTicket('login', 'main', 'requireLogin');
		Cgn_SystemRunner_Admin::stackTicket($newTicket);
		Cgn_Template::assignArray('redir', base64_encode(
			cgn_appurl($tk->module, $tk->service, $tk->event, $req->getvars)
		));
		return false;
	}

	function getHomeUrl($params = array()) {
		list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));
		return cgn_adminurl($module,$service, '', $params);
	}

	/**
	 * Return the $displayName of the currently running admin service
	 *
	 * @return Mixed  string if there is not a problem, false otherwise
	 */
	function getDisplayName() {
		$myHandler =& Cgn_ObjectStore::getObject("object://adminSystemHandler");
		if (!is_object($myHandler->ticketDoneList[0]))
			return FALSE;
		return $myHandler->ticketDoneList[0]->instance->displayName;
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
	protected $dataModel    = NULL;


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

	protected function _makeTableRow($d) {
		if (!is_object($d)) {
			return array_values($d);
		}
		$vals = $d->valuesAsArray();
		$row = array();
		foreach ($vals as $_k => $_v) {
			$row[] = $_v;
		}
		return $row;
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
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl($this->moduleName, $this->serviceName), $this->homeLinkName);
		$t['toolbar']->addButton($btn2);

		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl($this->moduleName, $this->serviceName, 'create'), "Add New ".ucfirst(strtolower($this->representing)));
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
			$this->dataModel = new $c();
		} else if ($this->tableName != '') {
			$this->dataModel = new Cgn_DataItem($this->tableName);
		} else {
			$this->dataModel = new Cgn_DataItem('');
		}
		//make the form
		$f = $this->_makeCreateForm($t, $this->dataModel);
		$this->_makeFormFields($f, $this->dataModel);
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
			$this->dataModel = new $c();
			$this->dataModel->load($req->cleanInt('id'));
		} else if ($this->tableName != '') {
			$this->dataModel = new Cgn_DataItem($this->tableName);
			$this->dataModel->load($req->cleanInt('id'));
		} else {
			$this->dataModel = new Cgn_DataItem('');
		}
		//make the form
		$f = $this->_makeEditForm($t, $this->dataModel);
		$this->_makeFormFields($f, $this->dataModel, TRUE);
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
			$this->dataModel = new $c();
		} else {
			$this->dataModel = new Cgn_DataItem($this->tableName);
		}
		$this->dataModel->load($req->cleanInt('id'));
		$this->_makePropTable($this->dataModel, $t);

		if ($this->eventName == 'view') {
			//Edit button
			$editParams = array('id'=>$req->cleanInt('id'));
			$btn4 = new Cgn_HtmlWidget_Button(
				cgn_adminurl($this->moduleName, $this->serviceName, 'edit', $editParams),
				"Edit This ".ucfirst(strtolower($this->representing)));
				
			$t['toolbar']->addButton($btn4);

			//Delete button
			$delParams = array('id'=>$req->cleanInt('id'), 
				'table'=>$this->dataModel->get('_table'));
			$btn3 = new Cgn_HtmlWidget_Button(
				cgn_adminurl($this->moduleName, $this->serviceName, 'del', $delParams),
				"Delete This ".ucfirst(strtolower($this->representing)));
				
			$t['toolbar']->addButton($btn3);
		}
	}

	/**
	 * @DEPRECATED at Cgn 18
	 */
	protected function _makeViewTable($model, &$t) {
		return $this->_makePropTable($model, $t);
	}

	protected function _makePropTable($model, &$t) {
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

		if (!$table = $req->cleanString('table')) {
			$table = $this->tableName;
		}

		if (!$key = $req->cleanString('key') ) {
			$key = $table;
		}
		if(!$id  = $req->cleanInt($key.'_id')) {
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
			if ($_key == $item->get('_pkey')) {continue;}
			if ($req->hasParam($_key)) {
				$cleaned = $req->cleanString($_key);
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
	protected $dataModel    = NULL;

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
		if (!is_object($d)) {
			return array_values($d);
		}
		$vals = $d->valuesAsArray();
		$row = array();
		foreach ($vals as $_k => $_v) {
			$row[] = $_v;
		}
		return $row;
	}

	/**
	 * Function to create a default toolbar
	 */
	protected function _makeToolbar(&$t) {
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn2 = new Cgn_HtmlWidget_Button(cgn_appurl($this->moduleName, $this->serviceName), $this->homeLinkName);
		$t['toolbar']->addButton($btn2);

		$btn1 = new Cgn_HtmlWidget_Button(cgn_appurl($this->moduleName, $this->serviceName, 'create'), "Add New ".ucfirst(strtolower($this->representing)));
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
	public function createEvent($req, &$t) {
		//make page title 
		$this->_makePageTitle($t);

		//make toolbar
		$this->_makeToolbar($t);

		$this->_makeDataModel($req);

		//make the form
		$f = $this->_makeCreateForm($t, $this->dataModel);
		$this->_makeFormFields($f, $this->dataModel, FALSE);
	}


	protected function _makeDataModel($req, $id=0) {
		//load a default data model if one is set
		if ($this->dataModelName != '') {
			$c = $this->dataModelName;
			$this->dataModel = new $c();
		} else if ($this->tableName != '') {
			$this->dataModel = new Cgn_DataItem($this->tableName);
		} else {
			$this->dataModel = new Cgn_DataItem('');
		}

		if ($id > 0 ) {
			$this->dataModel->initBlank();
			$this->dataModel->load($id);
		} else {
			$this->dataModel->initBlank();
		}
	}

	/**
	 * Show a form to make a new data item
	 */
	public function editEvent($req, &$t) {
		//make page title 
		$this->_makePageTitle($t);

		//make toolbar
		$this->_makeToolbar($t);

		$this->_makeDataModel($req, $req->cleanInt('id'));

		//make the form
		$f = $this->_makeEditForm($t, $this->dataModel);
		$this->_makeFormFields($f, $this->dataModel, TRUE);
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

	/**
	 * Attach form fields to the $f parmaeter
	 *
	 * @void
	 */
	protected function _makeFormFields($f, $dataModel, $editMode=FALSE) {
		$values = $dataModel->valuesAsArray();

		foreach ($values as $k=>$v) {
			//don't add the primary key if we're in edit mode
			if ($editMode == TRUE) {
				if ($k == 'id' || $k == $dataModel->get('_table').'_id') continue;
			}

			//skip common meta-data
			if ($k == 'created_on' || $k == 'edited_on') {
				continue;
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
	 * @DEPRECATED at Cgn 18
	 */
	protected function _makeViewTable($model, &$t) {
		return $this->_makePropTable($model, $t);
	}

	protected function _makePropTable($model, &$t) {
		$data = $model->valuesAsArray();

		$list =  new Cgn_Mvc_TableModel();
		//cut up the data into table data
		foreach ($data as $k => $v) {
			$list->data[] = array($k, $v);
		}
		$list->headers = array('key', 'value');

		$t['viewTable'] = new Cgn_Mvc_TableView($list);
		$t['viewTable']->attribs['width'] ='550';
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
			$this->dataModel = new $c();
		} else {
			$this->dataModel = new Cgn_DataItem($this->dataItemName);
		}
		$this->dataModel->load($req->cleanInt('id'));

		if ($this->eventName == 'view') {

			//Edit button
			$editParams = array('id'=>$req->cleanInt('id'));
			$btn4 = new Cgn_HtmlWidget_Button(
				cgn_appurl($this->moduleName, $this->serviceName, 'edit', $editParams),
				"Edit This ".ucfirst(strtolower($this->representing)));
				
			$t['toolbar']->addButton($btn4);

			//Delete button
			$delParams = array('id'=>$req->cleanInt('id'), 
				'table'=>$this->dataModel->get('_table'));
			$btn3 = new Cgn_HtmlWidget_Button(
				cgn_appurl($this->moduleName, $this->serviceName, 'del', $delParams),
				"Delete This ".ucfirst(strtolower($this->representing)));
				
			$t['toolbar']->addButton($btn3);
		}

		$this->_makePropTable($this->dataModel, $t);
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
	 * Saves $this->dataModel
	 *
	 * @return Int  primary key of saved item or false on error
	 */
	protected function _saveDataModel() {
		return $this->dataModel->save();
	}

	protected function _applyDataModelValues($req) {
		$vals = $this->dataModel->valuesAsArray();

		foreach ($vals as $_key => $_val) {
			if ($_key == $this->dataModel->get('_pkey')) {continue;}
			if ($req->hasParam($_key)) {
				$cleaned = $req->cleanString($_key);
				$this->dataModel->set($_key, $cleaned);
			}
		}
	}

	/**
	 * Save an object.
	 *
	 * This method calls
	 *
	 * _makeDataModel($req, $id)
	 * _applyDataModelValues($req)
	 * _saveDataModel()
	 * and
	 * redirectHome
	 *
	 * in that order
	 */
	public function saveEvent(&$req, &$t) {
		$id = $req->cleanInt('id');


		$this->_makeDataModel($req, $id);

		$this->_applyDataModelValues($req);
		$this->_saveDataModel();

		$this->redirectHome($t);
		$this->item = $this->dataModel;
	}


	public function undoEvent($req, &$t) {
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
