<?php

class Account_Base {

	var $addressBook;
	var $firstname;
	var $lastname;
	var $title;
	var $birthDate;
	var $_dataItem;

	var $attributes   = array();
	var $_attribItem;

	function Account_Base() {
	}

	/**
	 * Return an Account_Base from the given user id
	 *
	 * @static
	 */
	public static function loadByUserId($id) {
		$account = new Account_Base();
		$dataItem = new Cgn_DataItem('cgn_account');
//		$account->andWhere('user_id',$id);
		$dataItem->load( array('cgn_user_id = '.$id) );
		$dataItem->cgn_user_id = $id;
		$account->setDataItem($dataItem);

		$accountId = (int)$account->_dataItem->getPrimaryKey();
		$attribItem = new Cgn_DataItem('cgn_account_attrib');
		$attribItem->load(  array('cgn_account_id = '.$accountId) );
		$attribItem->_typeMap['cgn_account_id'] = 'int';
		$attribItem->_typeMap['value']          = 'text';
		$account->setAttribItem($attribItem);

		return $account;
	}

	/**
	 * Return an Account_Base from the given ACCOUNT id
	 *
	 * @static
	 */
	public static function load($id) {
		$account = new Account_Base();
		$dataItem = new Cgn_DataItem('cgn_account');
		$dataItem->load( $id );
		$account->setDataItem($dataItem);

		$accountId = (int)$account->_dataItem->getPrimaryKey();
		$attribItem = new Cgn_DataItem('cgn_account_attrib');
		$attribItem->load(  array('cgn_account_id = '.$accountId) );
		$attribItem->_typeMap['cgn_account_id'] = 'int';
		$attribItem->_typeMap['value']          = 'text';
		$account->setAttribItem($attribItem);

		return $account;
	}


	function save() {
		$this->updateDataItem();
		$this->updateAttribItem();
		$x = $this->_dataItem->save();
		$this->_attribItem->set('cgn_account_id', $this->_dataItem->get('cgn_account_id'));
		$this->_attribItem->save();
		return $x;
	}

	function setAttribItem($dataItem) {
		$this->_attribItem = $dataItem;
		if (!function_exists('json_decode'))
			return FALSE;
		$attribs = json_decode($dataItem->get('value'), TRUE);
		if (!is_array($attribs)) {
			$attribs = array();
		}
		$this->attributes = array_merge($this->attributes, $attribs);
		return TRUE;
	}

	function setDataItem($dataItem) {
		$this->firstname = @$dataItem->firstname;
		$this->lastname  = @$dataItem->lastname;
		$this->title     = @$dataItem->title;
		$this->birthDate = @$dataItem->birth_date;
		$this->_dataItem = $dataItem;
	}

	function updateDataItem() {
		$this->_dataItem->firstname = $this->firstname;
		$this->_dataItem->lastname  = $this->lastname;
		$this->_dataItem->title     = $this->title;
		$this->_dataItem->birth_date = (int)$this->birthDate;
	}

	function updateAttribItem() {
		if (!function_exists('json_encode'))
			return FALSE;
		$this->_attribItem->set('value', json_encode($this->attributes));
	}


	/**
	 * Set the user id of this account data item
	 */
	function setUserId($id) {
		$this->_dataItem->cgn_user_id = $id;
	}
}
