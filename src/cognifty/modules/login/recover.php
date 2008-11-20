<?php

/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Login_Recover extends Cgn_Service {

	/**
	 * @See Cgn_Service_Login_Recover::init()
	 */
	var $_allowRegister = TRUE;
	var $redirectModule = 'account';


	/**
	 * Checks a global setting for allowing self registration.
	 *
	 * Change this value in your default.ini to show or hide the register 
	 * option on the login page: 
	 * [config]
	 * allow.selfregister=[ true | false ]
	 */
	function init($req, $mod, $srv, $evt) {
		$selfRegisterKey = 'config://default/allow/selfregister';
		if (Cgn_ObjectStore::hasConfig($selfRegisterKey)) {
			$this->_allowRegister = (bool)
				Cgn_ObjectStore::getConfig($selfRegisterKey);
		}
		return parent::init($req, $mod, $srv, $evt);
	}


	/**
	 * show login box
	 * use this cookie for long term session $_COOKIE['cgn_ltsession'];
	 */
	function mainEvent(&$req, &$t) {
		//permanent login cookie

		$t['canregister'] = $this->_allowRegister;

		if (@$req->getvars['loginredir'] != '') {
			$t['redir'] = $req->getvars['loginredir'];
		} else {
			$t['redir'] = $_SERVER['HTTP_REFERER'];
		}
		$t['redir'] = base64_encode($t['redir']);

	}

	/**
	 * Check that the ticket is still valid, if so present a password change form.
	 */
	function verifyEvent(&$req, &$t) {
		$tk = $req->cleanString('tk');
		$item = new Cgn_DataItem('cgn_user_lost_ticket');
		$item->andWhere('token', $tk);
		$item->hasOne('cgn_user', 'cgn_user_id', 'Tcgn_user', 'cgn_user_id');
		$item->_rsltByPkey = FALSE;
		$tickets = $item->find();
		if (count($tickets) !== 1) {
			Cgn_ErrorStack::throwError('Ticket not valid.', 502);
			return false;
		}
		$ticket = $tickets[0];
		unset($tickets);
		$t['username'] = $ticket->username;
		$t['tk'] = $tk;
	}

	function resetEvent(&$req, &$t) {
		$tk = $req->cleanString('tk');
		$pass  = $req->cleanString('password');
		$pass2 = $req->cleanString('password2');

		$item = new Cgn_DataItem('cgn_user_lost_ticket');
		$item->andWhere('token', $tk);
		$item->hasOne('cgn_user', 'cgn_user_id', 'Tcgn_user', 'cgn_user_id');
		$item->_rsltByPkey = FALSE;
		$tickets = $item->find();
		if (count($tickets) !== 1) {
			Cgn_ErrorStack::throwError('Ticket not valid.', 502);
			return false;
		}
		if ($pass == '') {
			Cgn_ErrorStack::throwError('Invalid password.', 502);
			return false;
		}
		if ($pass !== $pass2) {
			Cgn_ErrorStack::throwError('Passwords do not match.', 502);
			return false;
		}
		//collect all the ticket ids for deleting
		$ticketIds = array();
		foreach ($tickets as $ticketObj) {
			$ticketIds[] = $ticketObj->cgn_user_lost_ticket_id;
		}
		$deleter = new Cgn_DataItem('cgn_user_lost_ticket');
		$deleter->andWhere('cgn_user_lost_ticket_id', '('.implode(',', $ticketIds).')', 'IN');
		$deleter->delete();

		$ticket = $tickets[0];
		unset($tickets);

		$user = $req->getUser();
		$user->userId = $ticket->cgn_user_id;
		$user->setPassword($pass);
		$user->username = $ticket->username;
		$user->email = $ticket->email;
		$user->save();

		if ($user->login($ticket->username,
			$pass)) 
		{
			$user->bindSession();
			$user->addSessionMessage("Password changed.");
		} else {
			Cgn_ErrorStack::throwError('Cannot login.', 501);
			return false;
		}

		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl($this->redirectModule);//DEFAULT_URL;
	}

	/**
	 * Send an email to the user, record the request, generate a ticket.
	 */
	function sendEvent(&$req, &$t) {
		$user = Cgn_SystemRequest::getUser();
		$user = new Cgn_DataItem('cgn_user');
		$user->andWhere('email',$req->cleanString('email'));
		$user->_rsltByPkey = false;
		$possibles = $user->find();

		$target = $possibles[0];
		if (!is_object($target)) {
			Cgn_ErrorStack::throwError('No such user found', 501);
			return false;
		}

		$ticket = $this->generateLostTicket($target->cgn_user_id);

		$ret = $this->sendLostMail($target->email, $target->username, $ticket);
	}

	/**
	 * Generate a ticket object for this user ID
	 *
	 * @return object Cgn_DataItem
	 */
	public function generateLostTicket($userId) {
		$ticket = new Cgn_DataItem('cgn_user_lost_ticket');
		$ticket->cgn_user_id = $userId;
		$ticket->token = md5(rand());
		$ticket->created_on = time();
		$ticket->save();
		return $ticket;
	}

	/**
	 * Send an email to the user that invites them to 
	 * reset their password.
	 *
	 * @param string $email
	 * @param string $username
	 * @param object $ticketObj Cgn_DataItem
	 * @return boolean result of php's mail() function.
	 */
	public function sendLostMail($email, $username, $ticketObj) {

		$errno = 0;
		$errstr = '';
		$template = '
Someone, probably you, requested to change their password at:
'.Cgn_Template::siteName().' '.cgn_url().'

If you wish to change your password, click the following link:

'.cgn_appurl('login','recover','verify', array('tk'=>$ticketObj->token)).'
';
		return mail($email, 'Password Recovery: ('.Cgn_Template::siteName().')', $template);
	}
}
?>
