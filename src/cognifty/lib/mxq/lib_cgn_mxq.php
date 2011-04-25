<?php

require_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');

class Cgn_Mxq_Channel {

	var $dataItem    = null;
	var $_loaded     = false;
	var $_messages   = array();

	function Cgn_Mxq_Channel() {
	}

	function loadByName($name) {
			$loader = new Cgn_DataItem('cgn_mxq_channel');
			$loader->andWhere('name', $name);
			$loader->_rsltByPkey = false;
			$channelList = $loader->find();
			if (!isset($channelList[0])) {
				Cgn_ErrorStack::throwError('No Such MXQ Channel', 410);
				return FALSE;
			}
			$this->dataItem = $channelList[0];
	}

	function getId() {
		return $this->dataItem->cgn_mxq_channel_id;
	}

	function getName() {
		return $this->dataItem->name;
	}

	function clearQueue() {
		$this->_loaded = false;
		return Cgn_Mxq_Queue::clear($this->getId());
	}

	function hasMessages() {
		if (!$this->_loaded) {
			$this->fetchMessages();
		}
		return count($this->_messages) > 0;
	}

	function nextMessage() {
		if (!$this->_loaded) {
			$this->fetchMessages();
		}
		if (isset($this->_messages[0])) {
			$x = array_shift($this->_messages);
			return $x;
		} else {
			return false;
		}
	}

	/**
	 * Only works for point to point types right now
	 */
	function consumeMessage($msg) {
		if ($this->dataItem->channel_type == 'point') {
			Cgn_Mxq_Queue::delete($msg);
		}
	}

	function fetchMessages() {
		$this->_messages = Cgn_Mxq_Queue::fetch($this->getId());
		$this->_loaded = true;
	}

	/**
	 * returns true on successfull save
	 */
	function addMessage($msg) {
		if (Cgn_Mxq_Queue::queueMessage($msg, $this->getId())) {
			$this->_messages[] = $msg;
			return true;
		} else {
			return false;
		}

	}
}

class Cgn_Mxq_Queue {

	/**
	 * returns true if any messages where removed
	 */
	static function clear($channelId=0) {
		if ($channelId==0) { return false; }
		$db = Cgn_Db_Connector::getHandle();
		$db->query('DELETE FROM `cgn_mxq` WHERE cgn_mxq_channel_id = '.(int)$channelId);
		return true;
	}

	/**
	 * returns true if any messages where removed
	 */
	static function delete(&$msg) {
		$msg->delete();
	}

	/**
	 * load message envelopes from queue
	 */
	static function fetch($channelId=0) {
		if ($channelId==0) { return false; }
		$loader = new Cgn_DataItem('cgn_mxq');
		$loader->andWhere('cgn_mxq_channel_id', $channelId);
		$loader->_cols = array('cgn_mxq_id', 'received_on', 'viewed_on','msg_name','return_address','expiry_date','format_version','format_type');
		$loader->_rsltByPkey = false;
		$results = $loader->find();
		$messageList = array();
		foreach ($results as $dataItem) {
			$x = new Cgn_Mxq_Message();
			$x->dataItem = $dataItem;
			$messageList[] = $x;
		}
		return $messageList;
	}

	static function fetchBody($msgId=0) {
		if ($msgId==0) { return false; }

		$loader = new Cgn_DataItem('cgn_mxq');
		$loader->_cols = array('msg');
		$loader->load($msgId);
		return $loader->msg;
	}

	static function queueMessage($msg, $channelId) {
		$msg->setChannelId($channelId);
		$msg->setReceived();
		return $msg->save();
	}
}


class Cgn_Mxq_Message {

	var $dataItem        = NULL;
	var $envelopeFrom    = '';
	var $envelopeReplyTo = '';
	var $envelopeTo      = '';

	function Cgn_Mxq_Message() {
		$this->dataItem = new Cgn_DataItem('cgn_mxq');
		$this->setName('New Message');
	}

	function getId() {
		return $this->dataItem->cgn_mxq_id;
	}

	function setName($n) {
		$this->dataItem->msg_name = $n;
	}

	function setChannelId($id) {
		$this->dataItem->cgn_mxq_channel_id = $id;
	}

	function setReceived() {
		$this->dataItem->received_on = time();
	}

	function save() {
		return $this->dataItem->save();
	}

	function setBody($msg) {
		$this->dataItem->msg = $msg;
	}

	function getBody() {
		return $this->dataItem->msg;
	}

	function fetchBody() {
		$this->dataItem->msg = Cgn_Mxq_Queue::fetchBody($this->dataItem->cgn_mxq_id);
	}

	function delete() {
		$this->dataItem->delete();
	}
}

/**
 * MXQ Message which sends e-mail directly.
 * These messages are not stored in any queue.  If they are stored, they lose the
 * envelopeTo/From/ReplyTo fields.  Those fields would need to be set
 * after loading from the DB and before sending.
 *
 * Ex:
 * $mail = new Cgn_Mxq_Message_Email();
 * $mail->setName('Title of mail');
 * $mail->setBody('This is the text content of the message.');
 * $mail->envelopeTo      = 'singleaddr@example.com';
 * $mail->envelopeFrom    = 'noreply@example.com';
 * $mail->envelopeReplyTo = 'noreply@example.com';
 *
 *
 * dataItem->msg_name   is the subject
 * envelopeFrom         is the from line
 * envelopeTo           is the to line
 * envelopeReplyTo      is the reply to line
 * dataItem->msg        is the plain text
 */
class Cgn_Mxq_Message_Email extends Cgn_Mxq_Message {

	public $envelopeToList = array();

	/**
	 * Skip the queue, send directly with mail
	 *
	 * @return Boolean  the result of the mail command or the ANDed result
	 * of many mail commands if envelopeToList has any items.
	 */
	function sendEmail() {
		//TODO construct message encoding properly
		//TODO construct message attachments
		if (count($this->envelopeToList)) {
			$m = TRUE;
			foreach ($this->envelopeToList as $_to) {
				$m =& mail(
					$_to,
					$this->dataItem->msg_name,
					$this->getEmailBody(),
					$this->getEmailHeaders()
				);

			}
		} else {
			$m = mail(
				$this->envelopeTo,
				$this->dataItem->msg_name,
				$this->getEmailBody(),
				$this->getEmailHeaders()
			);
		}
		return $m;
	}

	function getEmailBody() {
		return $this->dataItem->msg;
	}

	function getEmailHeaders() {
		$headers = '';
		if (isset($this->envelopeFrom) &&
			$this->envelopeFrom != '') {
				$headers .= "From: ".trim($this->envelopeFrom)."\r\n";
			}
		if (isset($this->envelopeReplyTo) &&
			$this->envelopeReplyTo != '') {
				$headers .= "Reply-To: ".trim($this->envelopeReplyTo)."\r\n";
			}
		//if no reply-to, use noreply@fromdomain.com
		if (!isset($this->envelopeReplyTo) ||
			$this->envelopeReplyTo == '') {
				$from = trim($this->envelopeFrom);
				$headers .= "Reply-To: ".
					substr_replace($from, "noreply", 0, strpos($from, '@'))
					."\r\n";
			}
		return $headers;
	}
}
