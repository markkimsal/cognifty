<?php

Cgn::loadModLibrary('Account::Account_Base');
Cgn::loadModLibrary('Account::Account_Address');

/**
 * Account
 *
 * Handle user accounts and profiles
 */
class Cgn_Service_Account_Main extends Cgn_Service {

	var $requireLogin = true;

	function Cgn_Service_Account_Main() {
	}

	function getBreadCrumbs() {
		return array('Account Home');
	}

	/**
	 * Show account settings.
	 */
	function mainEvent($req, &$t) {
		$u           = $req->getUser();
		$theId       = $u->userId;
		$otherUser   = FALSE;
		$otherId     = $req->cleanInt(0);
		if ($otherId) {
			$theId       = $otherId;
			$otherUser   = TRUE;
		} else { 
			$otherId     = $req->cleanString(0);
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



		if ($otherUser) {
			$account = Account_Base::load($theId);
		} else {
			$account = Account_Base::loadByUserId($theId);
		}

		//unknown account number
		if ($account->_dataItem->_isNew) {
			Cgn_ErrorStack::throwError('No such profile.', 509);
			$this->templateName = 'account_notfound';
			return false;
		}

		$t['acctObj'] = $account;
		$t['addrObj'] = Account_Address::loadByAccountId($account->_dataItem->getPrimaryKey());
		$t['profile'] = $account->_dataItem->valuesAsArray();
		$t['profile'] = array_merge($t['addrObj']->valuesAsArray(), $t['profile']);
		$t['profile'] = array_merge($t['profile'], $account->attributes);

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
