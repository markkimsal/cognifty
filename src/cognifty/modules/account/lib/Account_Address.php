<?php

class Account_Address extends Cgn_Data_Model {

	var $dataItem     = NULL;
	var $tableName    = 'cgn_account_address';

	var $addressType;
	var $street;
	var $street2;
	var $city;
	var $region;
	var $countryCode;
	var $postalCode;
	var $_isPreferred = false;

	public function initDataItem() {
		parent::initDataItem();
		$this->dataItem->created_on = time(); 
		$this->dataItem->edited_on = time(); 
	}

	/**
	 * Return an Account_Address from the given user id
	 *
	 * @static
	 */
	public static function loadByAccountId($id, $type='primary') {
		$address  = new Account_Address();
		$address->dataItem->orderBy('created_on DESC');
		$address->dataItem->load( array('cgn_account_id = '.$id, 'address_type="primary"') );

		$address->dataItem->cgn_account_id = $id;
		$address->dataItem->address_type   = $type;
		return $address;
	}


	public function save() {
		$this->dataItem->edited_on = time(); 
		return parent::save();
	}
}
