<?php

class Cgn_Service_Api_Restv1 extends Cgn_Service {

	var $crumbs = NULL;

	function Cgn_Service_Api_Restv1 () {
	}


	/**
	 * Called before any events.
	 *
	 * If this call fails, no more processing will continue;
	 *
	 * Overridden to change the default user for one specified by the API KEY
	 */
	function init($req, $mod, $srv, $evt) { 
		global $cgnUser;
		parent::init($req, $mod, $srv, $evt);

		$u = $req->getUser();
		$req->isAjax = TRUE;
		//don't accept logged in users, that means
		//they have a cookie, or this session is already 
		//bound to someone else
		if (!$u->isAnonymous()) {
			return FALSE;
		}

		$key = $req->cleanString('key');
		if ($key == '') {
			return FALSE;
		}
		$item = new Cgn_DataItem('cgn_user');
		$item->andWhere('enable_agent', 1);
		$item->andWhere('agent_key', $key);
		$item->load();

		if ($item->_isNew) { //load failed
			return FALSE;
		}

		$u = Cgn_User::load($item->cgn_user_id);
		$u->loadGroups();
		$cgnUser = $u;
		return TRUE;
	}

	/**
	 * Change the current user based on the API KEY, then emit  
	 * a signal for the current event.
	 */
	function processEvent($e,&$req,&$t) {
		/*
		$newTicket = new Cgn_SystemTicket('rss');
		Cgn_SystemRunner::stackTicket($newTicket);
		 */

		/*
		$u = $req->getUser();
		$t['message'] = 'Emitting signal api_'.$e;
		$t['message2'] = 'You username is  '.$u->username;
		*/
		$this->presenter = 'self';
		$this->req = $req;
		$this->t   = $t;
		ob_start();
		$this->emit('api_'.$e);
		$t['echo'] = ob_get_contents();
		ob_end_clean();
	}

	function output($req, &$t) {
		echo json_encode($t);
	}
}
?>
