<?php

Cgn::loadModLibrary('Account::Account_Base');
Cgn::loadModLibrary('Account::Account_Address');

/**
 * Account
 *
 * Handle user accounts and profiles
 */
class Cgn_Service_Account_Main extends Cgn_Service {

	public $usesPerm = true;

	public function Cgn_Service_Account_Main() {
	}

	public function hasAccess($u, $eventName) {
		if ($eventName == 'main') {
			return TRUE;
		}
		//for all other events, require a login
		return !$u->isAnonymous();
	}

	public function getBreadCrumbs() {
		return array('Account Home');
	}

	/**
	 * Show account settings.
	 */
	public function mainEvent($req, &$t) {
		$u           = $req->getUser();
		$theId       = $u->userId;
		$otherUser   = FALSE;
		$otherId     = $req->cleanInt(0);
		if ($otherId) {
			$theId       = $otherId;
			$otherUser   = TRUE;
		} else { 
			$otherId     = $req->cleanString(0);
		}

		if ($otherId) {
			//try to find username based off of this
			$user    = new Cgn_DataItem('cgn_user');
			$account = new Cgn_DataItem('cgn_account');
			if ($user->load ( array('username = "' .$otherId.'"'))) {
				if ($account->load ( array('cgn_user_id = "' .$user->get('cgn_user_id').'"'))) {
				$otherUser = TRUE;
				$theId     = $account->get('cgn_account_id');
				}
			}
		}

		//anonymous person not viewing other user
		if (!$otherUser && $u->isAnonymous()) {
			$newTicket = new Cgn_SystemTicket('login', 'main', 'requireLogin');
			Cgn_SystemRunner::stackTicket($newTicket);
			return TRUE;
		}

		if ($otherUser) {
			$account = Account_Base::load($theId);
		} else {
			$account = Account_Base::loadByUserId($theId);
		}

		//unknown account number
		if ($otherUser) {
			if ($account->_dataItem->_isNew) {
				Cgn_ErrorStack::throwError('No such profile.', 509);
				$this->templateName = 'account_notfound';
				return false;
			}
		}

		$t['acctObj'] = $account;
		$t['addrObj'] = Account_Address::loadByAccountId($account->_dataItem->getPrimaryKey());
		$t['profile'] = $account->_dataItem->valuesAsArray();
		$t['profile'] = array_merge($t['addrObj']->valuesAsArray(), $t['profile']);
		$t['profile'] = array_merge($t['profile'], $account->attributes);

		//db errors are "trigger_errors" in case the Cgn_ErrorStack is not used
		// as a handler.
		// an upgrade to the cgn_account_attrib table may result in an
		// error as tables are only dynamically rebuilt on insert/update
		$e = Cgn_ErrorStack::pullError('php');


		$t['otherUser'] = $otherUser;


		//set the display name
		if (!$otherUser) {
			$t['displayName'] = $u->getDisplayName();
		} else {
			if ($account->firstname != '' ||
				$account->lastname != '') {
					$t['displayName'] = $account->firstname. ' '.$account->lastname;
			}
		}
	}
}
