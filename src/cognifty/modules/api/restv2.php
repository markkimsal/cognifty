<?php

/**
 * Supports JSON or regular POST
 */
class Cgn_Service_Api_Restv2 extends Cgn_Service {

	public $crumbs = NULL;
	public $hasKey = FALSE;
	public $usesJson = FALSE;

	function Cgn_Service_Api_Restv2 () {
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


		$post = file_get_contents('php://input');
		$jsonPost = FALSE;
		if (function_exists('json_decode')) 
			$jsonPost = json_decode($post, TRUE);
		if ($jsonPost) {
			$this->usesJson = true;
			foreach ($jsonPost as $_k => $_v) {
				$req->postvars[$_k] = $_v;
			}
		}


		$u = $req->getUser();
		$req->isAjax = TRUE;
		//don't accept logged in users, that means
		//they have a cookie, or this session is already 
		//bound to someone else
		if (!$u->isAnonymous()) {
		//	return FALSE;
		}

		$key = $req->cleanString('key');
		if ($key == '') {
			$this->hasKey = FALSE;
			return TRUE;
		}
		$item = new Cgn_DataItem('cgn_user');
		$item->andWhere('enable_agent', 1);
		$item->andWhere('agent_key', $key);
		$item->load();

		if ($item->_isNew) { //load failed
			$this->hasKey = FALSE;
			return TRUE;
			//return FALSE;
		}
		$this->hasKey = TRUE;

		$u = Cgn_User::load($item->cgn_user_id);
		$u->loadGroups();
		$cgnUser = $u;
		return TRUE;
	}

	/**
	 * Change the current user based on the API KEY, then emit  
	 * a signal for the current event.
	 */
	function processEvent($e, $req, &$t) {
		/*
		$newTicket = new Cgn_SystemTicket('rss');
		Cgn_SystemRunner::stackTicket($newTicket);
		 */

		/*
		$u = $req->getUser();
		$t['message'] = 'Emitting signal api_'.$e;
		$t['message2'] = 'You username is  '.$u->username;
		*/
		//look for "action" or "method" in the POST
		if ($e == "") {
			$e = $req->cleanString('action');
			if ($e == "") {
				$e = $req->cleanString('method');
			}
		}

		$t['envelope'] = array('time'=>time());
		$this->presenter = 'self';
		$this->req = $req;
		$this->t   = $t;
		ob_start();

		//if we don't have a key, try specific anonymous api calls
		if (!$this->hasKey ) {
			$result = $this->emit('api_anon_'.$e);
			if (!$result) {
				$t['error'] = 'No API key provided or invalid API key.';
			}
		} else {
			$result = $this->emit('api_'.$e);
			if (!$result) {
				$t['error'] = 'There was a problem executing the API call.';
			}
		}

		if ($result) {
			$t['result'] = $this->t['result'];
		}
		ob_end_clean();
	}

	function output($req, &$t) {
		if ($this->usesJson) {
			echo json_encode($t)."\n";
		} else {
			foreach ($t['result'] as $_k => $_v) {
				echo $_k.'='.$_v."\n";
			}
		}
	}
}
