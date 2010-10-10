<?php

class Account_Base {

	var $addressBook;
	var $firstname;
	var $lastname;
	var $title;
	var $birthDate;
	var $_dataItem;

	function Account_Base() {
	}

	/**
	 * Return an Account_Base from the given user id
	 *
	 * @static
	 */
	function loadByUserId($id) {
		$account = new Account_Base();
		$dataItem = new Cgn_DataItem('cgn_account');
//		$account->andWhere('user_id',$id);
		$dataItem->load( array('cgn_user_id = '.$id) );
		$dataItem->cgn_user_id = $id;
		$account->setDataItem($dataItem);
		return $account;
	}

	function save() {
		$this->updateDataItem();
		return $this->_dataItem->save();
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

	/**
	 * Set the user id of this account data item
	 */
	function setUserId($id) {
		$this->_dataItem->cgn_user_id = $id;
	}
}
