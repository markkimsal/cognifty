<?php

Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Main_Userform extends Cgn_Service_Trusted {

	function __construct () {
		$this->dieOnFailure = FALSE;
		$this->screenPosts();
		$this->trustPlugin('requireCookie');
		$this->trustPlugin('throttle',3);
		$this->trustPlugin('html',10);
//		$this->trustPlugin('secureForm');
	}

	/**
	 * Show a form
	 */
	function mainEvent(&$req, &$t) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('contactus_01');
		$f->width="auto";
		$f->action = cgn_appurl('main','userform','save');
		$f->label = '';
		$values = array();
		$values['name'] = '';
		$values['email'] = '';
		$values['phone'] = '';
		$values['content'] = '';
		$name = new Cgn_Form_ElementInput('name');
		$name->size = 55;
		$f->appendElement($name,$values['name']);

		$email = new Cgn_Form_ElementInput('email','E-mail');
		$email->size = 55;
		$f->appendElement($email,$values['email']);

		$phone = new Cgn_Form_ElementInput('phone','Phone<br/>(optional)');
		$phone->size = 55;
		$f->appendElement($phone,$values['phone']);

		//randomize the name of the content field
		$commentField = substr( md5( rand(0,1000)), 0, 18);
		$comment = new Cgn_Form_ElementLabel('comment','Comment');
		$f->appendElement($comment);

		$textarea = new Cgn_Form_ElementText($commentField,'', 15, 62);
		$f->appendElement($textarea,$values['content']);

		$t['formtitle'] = '<h2>Contact Us</h2>';
		$t['form'] = $f;
	}

	/**
	 * Save the form submission.
	 */
	function saveEvent(&$req, &$t) {
		if ( $this->isTrustFailure() ) {
			Cgn_ErrorStack::throwError('Your message was not sent because it was not trusted by the server.', '601', 'sec');
			$this->mainEvent($req, $t);
			$myTemplate = Cgn_ObjectStore::getObject("object://defaultOutputHandler");
			$myTemplate->contentTpl = 'userform_main';
			$this->eventName = 'main';
			return;
		}

		$commentField = '';
		foreach ($req->postvars as $k=>$v) {
			if (strlen($k) == 18) {$commentField = $k;}continue;
		}

		if ( $commentField == '' || !($content = $req->cleanString($commentField))) {
			Cgn_ErrorStack::throwError('Your message was not sent because you did not enter a comment.', '601', 'sec');
			$this->mainEvent($req, $t);
			$myTemplate = Cgn_ObjectStore::getObject("object://defaultOutputHandler");
			$myTemplate->contentTpl = 'userform_main';
			$this->eventName = 'main';
			return;
		}

		include_once(CGN_LIB_PATH.'/mxq/lib_cgn_mxq.php');
		//Cgn_ObjectStore::debug('config://email/default/contactus');
		$from = Cgn_ObjectStore::getConfig('config://default/email/contactus');
		$to = Cgn_ObjectStore::getConfig('config://default/email/contactus');
		$mail = new Cgn_Mxq_Message_Email();
		$mail->envelopeFrom = $from;
		$mail->envelopeTo   = $to;

		$siteName = Cgn_ObjectStore::getString("config://template/site/name");
		$name = trim($req->cleanString('name'));
		if ($name == '') {
			$name = trim($req->cleanString('contact_name'));
		}
		//save other random postvars
		$postVars = '';
		$skipVars = array('name', 'contact_name', 'email', 'phone', 'contactus_01_submit');
		foreach ($req->postvars as $k=>$v) {
			if (in_array($k, $skipVars)) continue;
			if (strlen($k) == 18) {$commentField = $k;}continue;
			$postVars .= $k.': '.trim($v)."\n";
		}

		$mail->dataItem->msg_name = 'Message from contact us form from '.$siteName;
		$mail->dataItem->body  = 'Message from contact us form from '.$siteName."\n\n";
		$mail->dataItem->body .= 'Name: '.$name."\n";
		$mail->dataItem->body .= 'Email: '.trim($req->cleanString('email'))."\n";
		$mail->dataItem->body .= 'Phone: '.trim($req->cleanString('phone'))."\n";
		$mail->dataItem->body .= $postVars."\n";
		if ($content = $req->cleanMultiLine($commentField)) {
			$mail->dataItem->body .= trim($content)."\n";
		}
		$mail->sendEmail();
	}
}

?>
