<?php

Cgn::loadModLibrary('Account::Account_Base');

/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Account_Agent extends Cgn_Service {

	var $requireLogin = true;

	function Cgn_Service_Account_Agent() {
	}

	function getBreadCrumbs() {
		return array( cgn_applink('Account Home', 'account'), 'Setup your API agent');
	}

	/**
	 * Show password change form.
	 */
	function mainEvent(&$req, &$t) {
		$u = $req->getUser();
		$account = Account_Base::loadByUserId($u->userId);

		//db errors are "trigger_errors" in case the Cgn_ErrorStack is not used
		// as a handler.
		// an upgrade to the cgn_account_attrib table may result in an
		// error as tables are only dynamically rebuilt on insert/update
		$e = Cgn_ErrorStack::pullError('php');

		//load the user again as a data item because 
		//we don't store the agent_key normally with this user's object
		$user = new Cgn_DataItem('cgn_user');
		$user->load($u->getUserId());

		$t['contact'] = $account->_dataItem->valuesAsArray();
		$t['contact']['email'] = $user->email;


		$t['form'] = $this->_loadAgentForm(($user->enable_agent != '0'));

		if ($user->agent_key != '') {
			$t['agentKey'] = $user->agent_key;
		}

		if ($user->enable_agent != '') {
			$t['agentEnabled'] = $user->enable_agent;
		}

	}


	function _loadAgentForm($checked= FALSE) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('agent_form');
		$f->width="auto";
		$f->action = cgn_appurl('account','agent','change');
		$f->label = '';
		$check = new Cgn_Form_ElementCheck('enable', 'Enable API Agent?');
		$check->addChoice(' Check to enable the API Agent feature.', '01',  $checked);
		$f->appendElement($check);

		return $f;
	}

	/**
	 * Process password change form.
	 */
	function changeEvent(&$req, &$t) {

		$user = $req->getUser();

		if ($req->cleanString('enable') === '01') {
			$ret = $user->enableApi(TRUE);
			if (!$ret) {
				$user->addSessionMessage('Enabling agent failed.', 'msg_warn');
			} else {
				$user->addSessionMessage('Agent enabled.');
			}
		} else {
			$ret = $user->disableApi();
		}
		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('account', 'agent');
	}
}
?>
