<?php

class Account_Address extends Cgn_Data_Model {

	var $dataItem     = NULL;
	var $tableName    = 'cgn_account_address';

	var $_isPreferred = false;

	public function initDataItem() {
		parent::initDataItem();
	}

	/**
	 * Return an Account_Address from the given user id
	 *
	 * @static
	 */
	public static function loadByAccountId($id, $type='primary') {
		$id = (int)$id;
		$address  = new Account_Address();
		if ($id < 1) {
			return $address;
		}

		$address->dataItem->orderBy('created_on DESC');
		$address->dataItem->andWhere('cgn_account_id', $id);
		$address->dataItem->andWhere('address_type', "primary");

		unset($address->dataItem->created_on);
		unset($address->dataItem->edited_on);
		$address->dataItem->loadExisting();

		$address->dataItem->cgn_account_id = $id;
		$address->dataItem->address_type   = $type;
		return $address;
	}


	public function save() {
		if ($this->dataItem->created_on == 0) {
			$this->dataItem->created_on = time(); 
		}
		$this->dataItem->edited_on = time(); 
		return parent::save();
	}
}
