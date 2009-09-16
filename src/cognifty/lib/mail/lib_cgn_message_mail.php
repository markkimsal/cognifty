<?php

class Cgn_Message_Mail {

	var $body;
	var $subject;
	var $toList         = array();
	var $from           = '';
	var $reply           = '';
	var $ccList         = array();
	var $bccList        = array();

	//behavioral settings
	var $sendCombinedTo = TRUE;
	var $useSmtp        = FALSE;


	/**
	 * Wrap php's mail function.
	 *
	 * If sendCombinedTo is set to false, this will send 1 mail for each
	 * entry in $this->toList, else it will combine all entries into 1 line.
	 */
	function sendMail() {
		$errno = 0;
		$errstr = '';

		$headers = $this->createExtraHeaders();
		if ($this->sendCombinedTo == TRUE) {
			$toList = $this->combineToList();
		} else {
			foreach ($this->toList as $to) {
//				if ($this->useSmtp)
				mail ('mark@metrofindings.com', $this->subject, 'Would have sent to: '.$to."\n".$this->body, $headers);
			}
			return TRUE;
		}
		return mail('mark@metrofindings.com', $this->subject, 'Would have sent to: '.$toList."\n".$this->body, $headers);
	}


	/**
	 * return a comma separated list of to addresses
	 */
	function combineToList() {
		return implode(', ', $this->toList);
	}

	function createExtraHeaders() {
		$headers = array();

		$this->makeFromHeader($headers);

		$this->makeReplyHeader($headers);

		$headers2 = array();


		foreach ($headers as $h => $v) {
			$v = str_replace("\n", '', $v);
			$v = str_replace("\r", '', $v);

			$headers2[]  = $h.': '.trim($v);
		}

		return implode("\n", $headers2);
	}

	function makeFromHeader(&$h) {
		if ($this->from != '')
		$h['From'] = $this->from;
	}

	function makeReplyHeader(&$h) {
		if ($this->reply != '')
		$h['Reply-to'] = $this->reply;
	}
}

?>
